<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmartOrderController extends Controller
{
    private const BUSINESS_SESSION_KEY = 'smart_order.empresa_id';

    public function index(Request $request): View
    {
        $selectedBusinessId = $request->session()->get(self::BUSINESS_SESSION_KEY);

        if ($selectedBusinessId && ! Empresa::whereKey($selectedBusinessId)->exists()) {
            $request->session()->forget(self::BUSINESS_SESSION_KEY);
        }

        $businesses = Empresa::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'direccion', 'logo']);

        return view('smart-order', compact('businesses'));
    }

    public function selectBusiness(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
        ]);

        $request->session()->put(self::BUSINESS_SESSION_KEY, (int) $validated['empresa_id']);

        return redirect()->route('smart.order.catalog');
    }

    public function catalog(Request $request): View|RedirectResponse
    {
        $businessId = $request->session()->get(self::BUSINESS_SESSION_KEY);

        if (! $businessId) {
            return redirect()->route('smart.order');
        }

        $business = Empresa::find($businessId);

        if (! $business) {
            $request->session()->forget(self::BUSINESS_SESSION_KEY);

            return redirect()->route('smart.order');
        }

        return view('smart-order', [
            'businesses' => collect(),
            'selectedBusiness' => $business,
        ]);
    }
}
