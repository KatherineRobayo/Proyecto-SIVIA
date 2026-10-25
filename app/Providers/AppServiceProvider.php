<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use App\Models\Producto;
use App\Models\Movimiento;
use Illuminate\Support\Facades\URL; // <-- Nueva importación


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        RateLimiter::for('chatbot', function (Request $request) {
            return Limit::perMinute((int) config('chatbot.rate_limit_per_minute', 10))
                ->by($request->ip())
                ->response(fn (Request $request, array $headers) => response()->json([
                    'success' => false,
                    'reply' => config('chatbot.messages.too_many'),
                    'code' => 'too_many',
                ], 429, $headers));
        });

        View::composer('*', function ($view) {
            $notifAlertas = collect();
            $notifMovimientos = collect();
            $notifCount = 0;
            $user = auth()->user();

            if ($user?->activo && $user->empresa_id !== null) {
                $empresaId = (int) $user->empresa_id;
                $notifAlertas = Producto::forEmpresa($empresaId)
                    ->where('estado', 'activo')
                    ->whereColumn('stock', '<=', 'stock_minimo')
                    ->take(5)
                    ->get();
                $notifMovimientos = Movimiento::query()
                    ->whereHas('producto', fn ($query) => $query->where('empresa_id', $empresaId))
                    ->with('producto')
                    ->latest()
                    ->take(5)
                    ->get();
                $notifCount = $notifAlertas->count();
            }

            $view->with([
                'notifAlertas' => $notifAlertas,
                'notifMovimientos' => $notifMovimientos,
                'notifCount' => $notifCount,
            ]);
        });
    }
}
