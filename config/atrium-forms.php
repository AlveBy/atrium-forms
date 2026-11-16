<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Where the forms are sent
    |--------------------------------------------------------------------------
    |
    | POST /{prefix}/{code}, and /{language}/{prefix}/{code} for the other
    | content languages. The Blade component builds the address by itself.
    |
    */
    'prefix' => 'forms',

    /*
    |--------------------------------------------------------------------------
    | Anti-spam
    |--------------------------------------------------------------------------
    |
    | No outside service. Every form carries a hidden field that people do not
    | see and bots fill in (the honeypot), and the time it was drawn: a form
    | sent sooner than `min_seconds` after that is a bot's.
    |
    */
    'honeypot' => 'website',

    'min_seconds' => (int) env('ATRIUM_FORMS_MIN_SECONDS', 3),

    /*
    |--------------------------------------------------------------------------
    | Rate limit
    |--------------------------------------------------------------------------
    |
    | Submissions of one form from one address per minute.
    |
    */
    'per_minute' => (int) env('ATRIUM_FORMS_PER_MINUTE', 5),
];
