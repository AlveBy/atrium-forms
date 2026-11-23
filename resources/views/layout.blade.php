{{--
    This file is part of Atrium (https://github.com/AlveBy/atrium).
    Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
--}}
{{-- The page of a form on a site without alveby/atrium-theme. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    @stack('head')
    <style>
        body { margin: 0; font: 1rem/1.6 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color: #16181d; background: #fff; }
        main { max-width: 40rem; margin: 0 auto; padding: 2rem 1rem; }
    </style>
</head>
<body>
    <main id="content">
        @yield('content')
    </main>
</body>
</html>
