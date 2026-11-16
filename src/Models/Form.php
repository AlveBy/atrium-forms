<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Models;

use Atrium\Admin\Columns\BooleanColumn;
use Atrium\Admin\Columns\DateColumn;
use Atrium\Admin\Columns\TextColumn;
use Atrium\Admin\Contracts\Administrable;
use Atrium\Admin\Fields\CheckboxField;
use Atrium\Admin\Fields\TextareaField;
use Atrium\Admin\Fields\TextField;
use Atrium\Admin\Traits\BelongsToSite;
use Atrium\Admin\Traits\InteractsWithAdmin;
use Atrium\Admin\Traits\Translatable;
use Atrium\Forms\Rules\EmailList;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A form of one site. Its fields are records of their own (FormField); the
 * site shows it with <x-atrium-forms::form code="…" />.
 *
 * @property int $id
 * @property int|null $site_id
 * @property string $name
 * @property string $code
 * @property bool $active
 * @property string|null $recipients
 * @property int $submissions_count
 * @property-read Collection<int,FormField> $fields
 */
class Form extends Model implements Administrable
{
    use BelongsToSite, InteractsWithAdmin, Translatable;

    protected $table = 'forms';

    /** @var array<int,string> */
    protected array $translatable = ['button_label', 'success_message'];

    protected $fillable = ['site_id', 'name', 'code', 'active', 'recipients', 'button_label', 'success_message'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'submissions_count' => 'integer'];
    }

    /**
     * The fields go with the form; the submissions stay, with its name.
     * The foreign keys say the same, where the database enforces them.
     */
    protected static function booted(): void
    {
        static::deleting(function (Form $form): void {
            $form->fields()->delete();
            $form->submissions()->withoutGlobalScopes()->update(['form_id' => null]);
        });
    }

    /**
     * @return HasMany<FormField,$this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort')->orderBy('id');
    }

    /**
     * @return HasMany<FormSubmission,$this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    /**
     * The addresses a submission is mailed to.
     *
     * @return array<int,string>
     */
    public function recipientList(): array
    {
        return EmailList::split((string) $this->recipients);
    }

    public static function getAdminLabel(): string
    {
        return __('atrium-forms::forms.modules.forms');
    }

    public static function getDefaultSortColumn(): string
    {
        return 'name';
    }

    public static function getDefaultSortDirection(): string
    {
        return 'asc';
    }

    public static function getAdministrationFields(): array
    {
        return [
            CheckboxField::make('active')->label(__('atrium-forms::forms.fields.active'))->default(true),
            TextField::make('name')->label(__('atrium-forms::forms.fields.name'))->required(),
            TextField::make('code')
                ->label(__('atrium-forms::forms.fields.code'))
                ->hint(__('atrium-forms::forms.hints.code'))
                ->required()
                ->rules(['alpha_dash:ascii', 'max:100'])
                ->unique('forms', 'code'),
            TextareaField::make('recipients')
                ->label(__('atrium-forms::forms.fields.recipients'))
                ->hint(__('atrium-forms::forms.hints.recipients'))
                ->rules([new EmailList]),
            TextField::make('button_label')
                ->label(__('atrium-forms::forms.fields.button_label'))
                ->placeholder(__('atrium-forms::forms.public.submit')),
            TextareaField::make('success_message')
                ->label(__('atrium-forms::forms.fields.success_message'))
                ->placeholder(__('atrium-forms::forms.public.sent')),
        ];
    }

    public static function getAdministrationListColumns(): array
    {
        return [
            TextColumn::make('id')->label(__('atrium-forms::forms.fields.id'))->sortable(),
            TextColumn::make('name')->label(__('atrium-forms::forms.fields.name'))->sortable()->searchable(),
            TextColumn::make('code')->label(__('atrium-forms::forms.fields.code'))->sortable()->searchable(),
            BooleanColumn::make('active')->label(__('atrium-forms::forms.fields.active')),
            TextColumn::make('submissions_count')->label(__('atrium-forms::forms.fields.submissions_count'))->sortable(),
            DateColumn::make('updated_at')->label(__('atrium-forms::forms.fields.updated_at'))->sortable(),
        ];
    }
}
