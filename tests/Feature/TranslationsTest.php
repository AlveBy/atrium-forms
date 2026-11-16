<?php

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TranslationsTest extends TestCase
{
    /**
     * @return iterable<string,array{string}>
     */
    public static function languages(): iterable
    {
        foreach (glob(__DIR__.'/../../lang/*/forms.php') ?: [] as $file) {
            yield basename(dirname($file)) => [$file];
        }
    }

    #[DataProvider('languages')]
    public function test_every_language_has_the_english_keys_and_placeholders(string $file): void
    {
        $english = self::flatten(require __DIR__.'/../../lang/en/forms.php');
        $lines = self::flatten(require $file);

        $this->assertSame(array_keys($english), array_keys($lines));

        foreach ($english as $key => $line) {
            preg_match_all('/:[a-z_]+/', $line, $expected);
            preg_match_all('/:[a-z_]+/', $lines[$key], $actual);
            $this->assertSame($expected[0], $actual[0], $key);
        }
    }

    /**
     * @param  array<string,mixed>  $lines
     * @return array<string,string>
     */
    private static function flatten(array $lines, string $prefix = ''): array
    {
        $flat = [];

        foreach ($lines as $key => $value) {
            if (is_array($value)) {
                $flat = [...$flat, ...self::flatten($value, $prefix.$key.'.')];
            } else {
                $flat[$prefix.$key] = (string) $value;
            }
        }

        return $flat;
    }
}
