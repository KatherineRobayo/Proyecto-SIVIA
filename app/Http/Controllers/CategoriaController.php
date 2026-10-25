<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $empresaId = $this->empresaId();
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias', 'nombre')->where('empresa_id', $empresaId),
            ],
        ]);

        Categoria::create($validated + ['empresa_id' => $empresaId]);

        return back()->with('success', 'Categoría creada.');
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $empresaId = $this->empresaId();
        abort_unless((int) $categoria->empresa_id === $empresaId, 404);
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias', 'nombre')
                    ->where('empresa_id', $empresaId)
                    ->ignore($categoria->id),
            ],
        ]);

        $categoria->update($validated);

        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $empresaId = $this->empresaId();
        abort_unless((int) $categoria->empresa_id === $empresaId, 404);

        if (Producto::forEmpresa($empresaId)->where('categoria_id', $categoria->id)->exists()) {
            return back()->withErrors([
                'categoria' => 'La categoría tiene productos asociados; reasígnelos antes de eliminarla.',
            ]);
        }

        $categoria->delete();

        return back()->with('success', 'Categoría eliminada.');
    }

    private function empresaId(): int
    {
        $user = request()->user();
        abort_unless($user?->role === \App\Models\User::ROLE_COMPANY_ADMIN && $user->empresa_id, 403);

        return (int) $user->empresa_id;
    }
}
