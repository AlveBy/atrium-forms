<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Forms\Models\FormSubmission;
use Atrium\Forms\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;

/**
 * Form submissions are records the panel keeps for itself: they say so, in a method
 * the panel asks from outside the model, so it has to be public.
 */
final class ServiceModulesTest extends TestCase
{
    /**
     * @return array<string,array{class-string}>
     */
    public static function models(): array
    {
        return [
            'submissions' => [FormSubmission::class],
        ];
    }

    /**
     * @param  class-string  $model
     */
    #[DataProvider('models')]
    public function test_it_is_not_a_content_module(string $model): void
    {
        $method = new ReflectionMethod($model, 'isContentModule');

        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $this->assertFalse($method->invoke(null));
        $this->assertFalse($model::isContentModule());
    }
}
