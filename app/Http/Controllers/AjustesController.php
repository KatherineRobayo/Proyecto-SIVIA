<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Empresa;

class AjustesController extends Controller
{
    /* ===============================
       VISTA PRINCIPAL
    =============================== */

    public function index()
    {
        $users = User::with('empresa')->latest()->get();
        $empresas = Empresa::latest()->get();

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
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4',
            'role' => 'required',
            'empresa_id' => 'nullable'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'empresa_id' => $request->empresa_id
        ]);

        return back()->with('success', 'Usuario creado');
    }

    public function updateUser(Request $request, User $user)
    {
        $user->update([
            'name' => $request->name,
            'role' => $request->role,
            'empresa_id' => $request->empresa_id
        ]);

        return back()->with('success', 'Usuario actualizado');
    }

    public function destroyUser(User $user)
    {
        $user->delete();

        return back()->with('success', 'Usuario eliminado');
    }

    /* ===============================
       CRUD EMPRESAS
    =============================== */

    public function storeEmpresa(Request $request)
    {
        Empresa::create($request->all());

        return back()->with('success', 'Empresa creada');
    }

    public function updateEmpresa(Request $request, Empresa $empresa)
    {
        $empresa->update($request->all());

        return back()->with('success', 'Empresa actualizada');
    }

    public function destroyEmpresa(Empresa $empresa)
    {
        $empresa->delete();

        return back()->with('success', 'Empresa eliminada');
    }
}
