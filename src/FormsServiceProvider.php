<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms;

use Atrium\Admin\Builder\BlockTypes;
use Atrium\Admin\Embeds\Embeds;
use Atrium\Admin\Hooks\Hooks;
use Atrium\Admin\Modules\Modules;
use Atrium\Forms\Builder\FormBlock;
use Atrium\Forms\Embeds\FormEmbed;
use Atrium\Forms\Http\Controllers\ScriptController;
use Atrium\Forms\Http\Controllers\ShowController;
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
    /** What a form's code may be in an address. */
    private const CODE = '[A-Za-z0-9_-]+';

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
        // @atriumForm('contact', ['modal' => true]): the component in one line.
        // "Insert → Form" in the editors; the type is off while the module is not installed.
        $this->app->make(Hooks::class)->register(Embeds::REGISTRY, 'form', new FormEmbed);
        $this->app->make(Hooks::class)->register(BlockTypes::REGISTRY, 'form', new FormBlock, 70);
        Blade::directive('atriumForm', static fn (string $expression): string => "<?php echo \\Atrium\\Forms\\View\\Components\\Form::inline({$expression}); ?>");

        RateLimiter::for('atrium-forms', function (Request $request): Limit {
            $code = $request->route('code');

            return Limit::perMinute((int) config('atrium-forms.per_minute', 5))
                ->by($request->ip().'|'.$code)
                ->response(function (Request $request, array $headers) use ($code) {
                    $message = __('atrium-forms::forms.public.too_many');

                    if ($request->expectsJson()) {
                        return response()->json(['message' => $message], 429, $headers);
                    }

                    throw SubmitController::failed($request, $code, ['form' => $message]);
                });
        });

        // Once the panel has booted: Route::localized() is its macro.
        $this->app->booted(function (): void {
            if ($this->app->routesAreCached() || ! self::installed()) {
                return;
            }

            $prefix = trim((string) config('atrium-forms.prefix', 'forms'), '/');

            // One file for every language and site, without a session.
            Route::get("{$prefix}/script.js", ScriptController::class)->name('atrium.forms.script');

            Route::middleware('web')->group(function () use ($prefix): void {
                Route::localized(function () use ($prefix): void {
                    Route::get("{$prefix}/{code}", ShowController::class)
                        ->where('code', self::CODE)
                        ->name('atrium.forms.show');

                    Route::post("{$prefix}/{code}", SubmitController::class)
                        ->where('code', self::CODE)
                        ->middleware('throttle:atrium-forms')
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
