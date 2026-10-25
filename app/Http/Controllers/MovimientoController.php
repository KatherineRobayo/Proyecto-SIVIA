<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovimientoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless(
            $user?->activo && $user->empresa_id
                && in_array($user->role, [User::ROLE_COMPANY_ADMIN, User::ROLE_EMPLOYEE], true),
            403
        );
        $movimientos = Movimiento::query()
            ->whereHas('producto', fn ($query) => $query->where('empresa_id', $user->empresa_id))
            ->with(['producto', 'user'])
            ->latest()
            ->paginate(30);

        return view('dashboard.movimientos', compact('movimientos'));
    }
}
