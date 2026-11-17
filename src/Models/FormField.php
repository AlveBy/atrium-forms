<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Models;

use Atrium\Admin\Columns\BooleanColumn;
use Atrium\Admin\Columns\TextColumn;
use Atrium\Admin\Contracts\Administrable;
use Atrium\Admin\Fields\BelongsToField;
use Atrium\Admin\Fields\CheckboxField;
use Atrium\Admin\Fields\NumberField;
use Atrium\Admin\Fields\SelectField;
use Atrium\Admin\Fields\TextareaField;
use Atrium\Admin\Fields\TextField;
use Atrium\Admin\Traits\InteractsWithAdmin;
use Atrium\Admin\Traits\Translatable;
use Atrium\Forms\Rules\UniqueFieldName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

/**
 * One field of a form: its type, label (per language), whether it is required
 * and, for a list, its options. The list in the admin shows the fields of the
 * current site's forms.
 *
 * @property int $id
 * @property int $form_id
 * @property string $name
 * @property string $type
 * @property bool $required
 * @property int $sort
 * @property-read Form|null $form
 * @property-read string $form_name
 */
class FormField extends Model implements Administrable
{
    use InteractsWithAdmin, Translatable;

    /** Field types, as the site draws them. */
    public const TYPES = ['text', 'email', 'tel', 'number', 'date', 'textarea', 'select', 'radio', 'checkbox'];

    /** Types whose value is one of the options. */
    public const CHOICE_TYPES = ['select', 'radio'];

    protected $table = 'form_fields';

    /** @var array<int,string> */
    protected array $translatable = ['label', 'options'];

    protected $fillable = ['form_id', 'name', 'type', 'label', 'required', 'options', 'sort'];

    /** @var list<string> */
    protected $appends = ['form_name'];

    protected function casts(): array
    {
        return ['required' => 'boolean', 'sort' => 'integer'];
    }

    /**
     * @return BelongsTo<Form,$this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function getFormNameAttribute(): string
    {
        return (string) $this->form?->name;
    }

    /**
     * The options in the current language, one per line in the admin.
     *
     * @return array<int,string>
     */
    public function optionList(): array
    {
        $lines = preg_split('/\R/', (string) $this->getTranslation('options', app()->getLocale())) ?: [];

        return array_values(array_unique(array_filter(array_map('trim', $lines), static fn (string $line): bool => $line !== '')));
    }

    /**
     * Rules for the visitor's value.
     *
     * @return array<int,mixed>
     */
    public function publicRules(): array
    {
        return [$this->required ? 'required' : 'nullable', ...match ($this->type) {
            'email' => ['string', 'email', 'max:255'],
            'tel' => ['string', 'max:50', 'regex:/^[0-9+()\-.\s]+$/'],
            'number' => ['numeric'],
            'date' => ['date'],
            'textarea' => ['string', 'max:10000'],
            'select', 'radio' => ['string', Rule::in($this->optionList())],
            'checkbox' => $this->required ? ['accepted'] : ['boolean'],
            default => ['string', 'max:1000'],
        }];
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function scopeAdminList(Builder $query): Builder
    {
        // Through the form, so only the current site's forms count.
        return $query->whereHas('form')->with('form');
    }

    public static function getAdminLabel(): string
    {
        return __('atrium-forms::forms.modules.form_fields');
    }

    public static function getDefaultSortColumn(): string
    {
        return 'sort';
    }

    public static function getDefaultSortDirection(): string
    {
        return 'asc';
    }

    public static function getAdministrationFields(): array
    {
        $types = [];

        foreach (self::TYPES as $type) {
            $types[$type] = __("atrium-forms::forms.types.{$type}");
        }

        return [
            BelongsToField::make('form_id')
                ->label(__('atrium-forms::forms.fields.form'))
                ->model(Form::class)
                ->required(),
            TextField::make('label')->label(__('atrium-forms::forms.fields.label'))->required(),
            TextField::make('name')
                ->label(__('atrium-forms::forms.fields.field_name'))
                ->hint(__('atrium-forms::forms.hints.field_name'))
                ->required()
                ->rules(['alpha_dash:ascii', 'max:100', new UniqueFieldName]),
            SelectField::make('type')
                ->label(__('atrium-forms::forms.fields.type'))
                ->options($types)
                ->default('text')
                ->required(),
            CheckboxField::make('required')->label(__('atrium-forms::forms.fields.required')),
            TextareaField::make('options')
                ->label(__('atrium-forms::forms.fields.options'))
                ->hint(__('atrium-forms::forms.hints.options')),
            NumberField::make('sort')->label(__('atrium-forms::forms.fields.sort'))->default(500),
        ];
    }

    public static function getAdministrationListColumns(): array
    {
        return [
            TextColumn::make('id')->label(__('atrium-forms::forms.fields.id'))->sortable(),
            TextColumn::make('form_name')->label(__('atrium-forms::forms.fields.form')),
            TextColumn::make('label')->label(__('atrium-forms::forms.fields.label'))->sortable()->searchable(),
            TextColumn::make('name')->label(__('atrium-forms::forms.fields.field_name'))->sortable()->searchable(),
            TextColumn::make('type')->label(__('atrium-forms::forms.fields.type'))->sortable(),
            BooleanColumn::make('required')->label(__('atrium-forms::forms.fields.required')),
            TextColumn::make('sort')->label(__('atrium-forms::forms.fields.sort'))->sortable(),
        ];
    }
}
