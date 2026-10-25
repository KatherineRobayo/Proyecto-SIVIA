<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Empresa;

class AjustesController extends Controller
{
    /* ===============================
       VISTA PRINCIPAL
    =============================== */

    public function index()
    {
        $actor = auth()->user();
        $users = User::with('empresa')->when(
            ! $actor->isAdminGlobal(),
            fn ($query) => $query->where('empresa_id', $actor->empresa_id)
        )->latest()->get();
        $empresas = Empresa::when(
            ! $actor->isAdminGlobal(),
            fn ($query) => $query->whereKey($actor->empresa_id)
        )->latest()->get();

        return view('dashboard.ajustes.index', compact('users', 'empresas'));
    }

    /* ===============================
       CAMBIAR CONTRASEÑA
    =============================== */

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'La contraseña actual no es correcta'
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('password_success', 'Contraseña actualizada correctamente');
    }

    /* ===============================
       CAMBIAR CORREO
    =============================== */

    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . auth()->id()
        ]);

        $user = auth()->user();
        $user->email = $request->email;
        $user->email_verified_at = null;
        $user->save();

        return back()->with('success', 'Correo actualizado correctamente');
    }

    /* ===============================
       CRUD USUARIOS
    =============================== */

    public function storeUser(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor->isAdminGlobal() || $actor->role === User::ROLE_COMPANY_ADMIN, 403);
        $allowedRoles = $actor->isAdminGlobal()
            ? [User::ROLE_COMPANY_ADMIN]
            : [User::ROLE_EMPLOYEE];

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in($allowedRoles)],
            'empresa_id' => [$actor->isAdminGlobal() ? 'required' : 'prohibited', 'integer', 'exists:empresas,id'],
        ]);

        $empresaId = $actor->isAdminGlobal() ? (int) $request->input('empresa_id') : $actor->empresa_id;

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'empresa_id' => $empresaId,
            'activo' => true,
        ]);

        return back()->with('success', 'Usuario creado');
    }

    public function updateUser(Request $request, User $user)
    {
        $actor = $request->user();
        $canManageUser = $actor->isAdminGlobal()
            ? $user->role === User::ROLE_COMPANY_ADMIN
            : $actor->belongsToEmpresa((int) $user->empresa_id)
                && $user->role === User::ROLE_EMPLOYEE;
        abort_unless(
            $canManageUser && $user->id !== $actor->id,
            403
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Usuario actualizado');
    }

    public function destroyUser(User $user)
    {
        $actor = request()->user();
        $canManageUser = $actor->isAdminGlobal()
            ? $user->role === User::ROLE_COMPANY_ADMIN
            : $actor->belongsToEmpresa((int) $user->empresa_id)
                && $user->role === User::ROLE_EMPLOYEE;
        abort_unless(
            $canManageUser && $user->id !== $actor->id,
            403
        );

        $user->update(['activo' => false]);

        return back()->with('success', 'Usuario desactivado; se conservó su historial.');
    }

    /* ===============================
       CRUD EMPRESAS
    =============================== */

    public function storeEmpresa(Request $request)
    {
        abort_unless(request()->user()?->isAdminGlobal(), 403);
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);

        Empresa::create($validated);

        return back()->with('success', 'Empresa creada');
    }

    public function updateEmpresa(Request $request, Empresa $empresa)
    {
        abort_unless($request->user()?->isAdminGlobal(), 403);
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);

        $empresa->update($validated);

        return back()->with('success', 'Empresa actualizada');
    }

    public function destroyEmpresa(Empresa $empresa)
    {
        abort_unless(request()->user()?->isAdminGlobal(), 403);
        return back()->withErrors([
            'empresa' => 'No se elimina una empresa desde esta pantalla porque puede tener registros históricos.',
        ]);
    }
}
