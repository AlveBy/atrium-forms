<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The script that sends the forms without reloading the page and opens them
 * in a window: resources/js/atrium-forms.js, no dependencies. The component
 * asks for it with ?v= (the file's hash), and that address is kept by the
 * browser for good; without it, for an hour.
 */
final class ScriptController
{
    public static function path(): string
    {
        return dirname(__DIR__, 3).'/resources/js/atrium-forms.js';
    }

    public static function version(): string
    {
        return substr((string) hash_file('sha256', self::path()), 0, 12);
    }

    public function __invoke(Request $request): Response
    {
        return response((string) file_get_contents(self::path()), 200, [
            'Content-Type' => 'text/javascript; charset=utf-8',
            'Cache-Control' => $request->query('v') === self::version() ? 'public, max-age=31536000, immutable' : 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
