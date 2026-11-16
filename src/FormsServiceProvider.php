<?php

declare(strict_types=1);

namespace Atrium\Forms;

use Atrium\Admin\Modules\Modules;
use Atrium\Forms\Http\Controllers\SubmitController;
use Atrium\Forms\Models\Form;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class FormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/atrium-forms.php', 'atrium-forms');
    }

    /**
     * The models and migrations come from the manifest in composer.json
     * (`extra.atrium`) once `atrium:module:install` has run; until then the
     * route is not there either.
     */
    public static function installed(): bool
    {
        return app(Modules::class)->available(Form::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'atrium-forms');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'atrium-forms');
        Blade::componentNamespace('Atrium\\Forms\\View\\Components', 'atrium-forms');

        RateLimiter::for('atrium-forms', function (Request $request): Limit {
            $code = (string) $request->route('code');

            return Limit::perMinute(max(1, (int) config('atrium-forms.per_minute', 5)))
                ->by($request->ip().'|'.$code)
                ->response(function (Request $request, array $headers) use ($code) {
                    $message = __('atrium-forms::forms.public.too_many');

                    if ($request->expectsJson()) {
                        return response()->json(['message' => $message], 429, $headers);
                    }

                    throw SubmitController::failed($code, ['form' => $message]);
                });
        });

        // Once the panel has booted: Route::localized() is its macro.
        $this->app->booted(function (): void {
            if ($this->app->routesAreCached() || ! self::installed()) {
                return;
            }

            Route::middleware(['web', 'throttle:atrium-forms'])->group(function (): void {
                Route::localized(function (): void {
                    Route::post(trim((string) config('atrium-forms.prefix', 'forms'), '/').'/{code}', SubmitController::class)
                        ->where('code', '[A-Za-z0-9_-]+')
                        ->name('atrium.forms.submit');
                });
            });
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/atrium-forms.php' => config_path('atrium-forms.php'),
            ], 'atrium-forms-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/atrium-forms'),
            ], 'atrium-forms-views');
        }
    }
}
