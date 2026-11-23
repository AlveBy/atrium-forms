<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Admin\Fields\Field;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormField;
use Atrium\Forms\Models\FormSubmission;
use Atrium\Forms\Tests\TestCase;

/**
 * Which texts of the module the AI helps with: the ones the editors write for
 * the site, and not the ones visitors write or the ones that are code.
 */
final class AiFieldsTest extends TestCase
{
    public function test_what_a_visitor_sent_is_not_for_the_ai_to_touch(): void
    {
        foreach (['content'] as $name) {
            $this->assertNull($this->field(FormSubmission::class, $name)->toArray()['ai'] ?? null, $name);
        }
    }

    public function test_the_recipients_of_a_form_are_left_alone(): void
    {
        foreach (['recipients', 'success_message'] as $name) {
            $this->assertNull($this->field(Form::class, $name)->toArray()['ai'] ?? null, $name);
        }
    }

    public function test_the_options_of_a_field_are_left_alone(): void
    {
        // The fields, their options among them, are built on the form's own page.
        $this->assertNull($this->field(Form::class, 'builder')->toArray()['ai'] ?? null);
        $this->assertFalse(method_exists(FormField::class, 'getAdministrationFields'), 'A field of a form has no admin form of its own.');
    }

    /**
     * @param  class-string  $model
     */
    private function field(string $model, string $name): Field
    {
        $fields = method_exists($model, 'getAdminFieldsWithTabs') ? $model::getAdminFieldsWithTabs() : $model::getAdministrationFields();

        return collect($fields)->first(fn (Field $field): bool => $field->getName() === $name) ?? $this->fail("No field {$name} in {$model}.");
    }
}
