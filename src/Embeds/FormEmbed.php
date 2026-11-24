<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Embeds;

use Atrium\Admin\Embeds\ModelEmbed;
use Atrium\Forms\Models\Form;
use Atrium\Forms\View\Components\Form as FormComponent;
use Illuminate\Database\Eloquent\Model;

/**
 * "Insert → Form" in the editor: one of the site's forms, drawn by the same
 * component as <x-atrium-forms::form> and sent without reloading the page.
 * A form that is switched off or deleted leaves nothing.
 */
final class FormEmbed extends ModelEmbed
{
    public function label(): string
    {
        return __('atrium-forms::forms.fields.form');
    }

    protected function model(): string
    {
        return Form::class;
    }

    protected function renderRecord(Model $record): string
    {
        return FormComponent::inline((string) $record->getAttribute('code'), ['ajax' => true]);
    }
}
