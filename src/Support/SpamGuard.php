<?php

declare(strict_types=1);

namespace Atrium\Forms\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Anti-spam without an outside service: a hidden field people leave empty
 * (the honeypot) and the time the form was drawn, signed so it cannot be
 * forged. A form sent faster than a person can fill it in is a bot's.
 */
final class SpamGuard
{
    public const TIME_FIELD = '_t';

    public const HONEYPOT = 'honeypot';

    public const TOO_FAST = 'too_fast';

    public static function honeypotName(): string
    {
        return (string) config('atrium-forms.honeypot', 'website');
    }

    public static function token(): string
    {
        return Crypt::encryptString((string) time());
    }

    /**
     * Why the request looks like spam, or null when it does not.
     *
     * @return self::HONEYPOT|self::TOO_FAST|null
     */
    public static function check(Request $request): ?string
    {
        if (filled($request->input(self::honeypotName()))) {
            return self::HONEYPOT;
        }

        try {
            $drawnAt = (int) Crypt::decryptString((string) $request->input(self::TIME_FIELD));
        } catch (DecryptException) {
            return self::TOO_FAST;
        }

        if (time() - $drawnAt < (int) config('atrium-forms.min_seconds', 3)) {
            return self::TOO_FAST;
        }

        return null;
    }
}
