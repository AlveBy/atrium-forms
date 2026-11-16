<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Rules;

use Atrium\Forms\Models\FormField;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A field name is the key of the value in a submission: one per form.
 * Reads the form and the edited field from the admin request.
 */
final class UniqueFieldName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $request = request();
        $id = $request->route('id');
        // An API update may leave the form out: it stays the same.
        $formId = $request->input('form_id') ?? ($id ? FormField::query()->whereKey($id)->value('form_id') : null);

        $taken = FormField::query()
            ->where('form_id', $formId)
            ->where('name', $value)
            ->when($id, fn ($query) => $query->whereKeyNot($id))
            ->exists();

        if ($taken) {
            $fail(__('atrium-forms::forms.validation.field_name_taken'));
        }
    }
}
