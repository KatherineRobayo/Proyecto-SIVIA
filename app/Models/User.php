<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_GLOBAL_ADMIN = 'admin_global';
    public const ROLE_COMPANY_ADMIN = 'admin_empresa';
    public const ROLE_EMPLOYEE = 'empleado';
    public const ROLE_CUSTOMER = 'cliente';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'empresa_id',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            if ($user->exists && ! $user->isDirty(['role', 'empresa_id'])) {
                return;
            }

            $role = $user->canonicalRole($user->getAttributes()['role'] ?? self::ROLE_CUSTOMER);
            $companyId = $user->empresa_id;

            if (! in_array($role, [
                self::ROLE_GLOBAL_ADMIN,
                self::ROLE_COMPANY_ADMIN,
                self::ROLE_EMPLOYEE,
                self::ROLE_CUSTOMER,
            ], true)) {
                throw new \InvalidArgumentException("Rol no permitido: {$role}.");
            }

            if (in_array($role, [self::ROLE_GLOBAL_ADMIN, self::ROLE_CUSTOMER], true) && $companyId !== null) {
                throw new \InvalidArgumentException("El rol {$role} no puede pertenecer a una empresa.");
            }

            if (in_array($role, [self::ROLE_COMPANY_ADMIN, self::ROLE_EMPLOYEE], true) && $companyId === null) {
                throw new \InvalidArgumentException("El rol {$role} requiere una empresa.");
            }

            $user->setAttribute('role', $role);
        });
    }

    public function getRoleAttribute(?string $value): ?string
    {
        return $value === null ? null : $this->canonicalRole($value);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function pedidosComoCliente(): HasMany
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }

    public function ventasComoCliente(): HasMany
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_GLOBAL_ADMIN, self::ROLE_COMPANY_ADMIN], true);
    }

    public function isAdminGlobal(): bool
    {
        return $this->role === self::ROLE_GLOBAL_ADMIN;
    }

    public function belongsToEmpresa(int $empresaId): bool
    {
        return $this->empresa_id !== null && (int) $this->empresa_id === $empresaId;
    }

    private function canonicalRole(string $role): string
    {
        return match ($role) {
            'admin' => $this->empresa_id === null ? self::ROLE_GLOBAL_ADMIN : self::ROLE_COMPANY_ADMIN,
            'almacenista' => self::ROLE_EMPLOYEE,
            default => $role,
        };
    }
}