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
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            Schema::disableForeignKeyConstraints();
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

            try {
                if (class_exists(\App\Models\Producto::class)) {
                    $notifAlertas = Producto::whereColumn('stock', '<=', 'stock_minimo')
                        ->take(5)
                        ->get();
                }

                if (class_exists(\App\Models\Movimiento::class)) {
                    $notifMovimientos = Movimiento::latest()
                        ->take(5)
                        ->get();
                }

                $notifCount = $notifAlertas->count();

            } catch (\Exception $e) {
                // silencioso
            }

            $view->with([
                'notifAlertas' => $notifAlertas,
                'notifMovimientos' => $notifMovimientos,
                'notifCount' => $notifCount,
            ]);
        });
    }
}

