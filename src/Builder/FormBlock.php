<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Builder;

use Atrium\Admin\Builder\BlockType;
use Atrium\Admin\Fields\CheckboxField;
use Atrium\Admin\Fields\SelectField;
use Atrium\Forms\FormsServiceProvider;
use Atrium\Forms\Models\Form;

/**
 * The block of the site builder that shows one of the site's forms, drawn by
 * the same component as <x-atrium-forms::form> and sent without reloading
 * the page.
 */
final class FormBlock extends BlockType
{
    public function label(): string
    {
        return __('atrium-forms::forms.fields.form');
    }

    public function fields(): array
    {
        return [
            SelectField::make('form')
                ->label(__('atrium-forms::forms.fields.form'))
                ->options(Form::query()->orderBy('name')->pluck('name', 'id')->all())
                ->required()
                ->rules('integer'),
            CheckboxField::make('modal')->label(__('atrium-forms::forms.builder.modal'))->default(false),
        ];
    }

    public function view(): string
    {
        return 'atrium-forms::builder';
    }

    public function data(array $settings): ?array
    {
        $id = $settings['form'] ?? null;
        $code = is_numeric($id) ? Form::query()->whereKey((int) $id)->where('active', true)->value('code') : null;

        return $code === null ? null : ['code' => $code, 'modal' => (bool) ($settings['modal'] ?? false)];
    }

    public function available(): bool
    {
        return FormsServiceProvider::installed();
    }
}
