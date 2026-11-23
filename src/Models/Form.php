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
use Atrium\Admin\Fields\DisplayField;
use Atrium\Admin\Fields\FormBuilderField;
use Atrium\Admin\Fields\TextareaField;
use Atrium\Admin\Fields\TextField;
use Atrium\Admin\Support\ContentLanguages;
use Atrium\Admin\Traits\BelongsToSite;
use Atrium\Admin\Traits\InteractsWithAdmin;
use Atrium\Admin\Traits\Translatable;
use Atrium\Forms\Rules\EmailList;
use Atrium\Forms\Support\Snippets;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * A form of one site. Its fields (FormField) are built on its own page, as
 * the `builder` attribute; the API reads and writes the same attribute. The
 * site shows it with <x-atrium-forms::form code="…" />.
 *
 * @property int $id
 * @property int|null $site_id
 * @property string $name
 * @property string $code
 * @property bool $active
 * @property string|null $recipients
 * @property int $submissions_count
 * @property-read array<int,array<string,mixed>> $builder
 * @property-read Collection<int,FormField> $fields
 */
class Form extends Model implements Administrable
{
    use BelongsToSite, InteractsWithAdmin, Translatable;

    protected $table = 'forms';

    /** @var array<int,string> */
    protected array $translatable = ['button_label', 'success_message'];

    protected $fillable = ['site_id', 'name', 'code', 'active', 'recipients', 'button_label', 'success_message'];

    /** @var list<string> */
    protected $appends = ['builder'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
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
            FormBuilderField::make('builder')
                ->label(__('atrium-forms::forms.fields.builder'))
                ->hint(__('atrium-forms::forms.hints.builder'))
                ->types(self::typeLabels())
                ->choiceTypes(FormField::CHOICE_TYPES),
            DisplayField::make('snippets')
                ->label(__('atrium-forms::forms.snippets.label'))
                ->html(static fn (Model $form): string => $form instanceof self ? view('atrium-forms::admin.snippets', ['snippets' => new Snippets($form)])->render() : ''),
        ];
    }

    /**
     * @return array<string,string>
     */
    private static function typeLabels(): array
    {
        $types = [];

        foreach (FormField::TYPES as $type) {
            $types[$type] = __("atrium-forms::forms.types.{$type}");
        }

        return $types;
    }

    /**
     * The fields as the builder and the API see them.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function getBuilderAttribute(): array
    {
        return $this->fields->map(fn (FormField $field): array => [
            'id' => $field->id,
            'name' => $field->name,
            'type' => $field->type,
            'required' => $field->required,
            'width' => $field->width,
            'min' => $field->min,
            'max' => $field->max,
            'label' => $field->getTranslations('label'),
            'hint' => $field->getTranslations('hint'),
            'placeholder' => $field->getTranslations('placeholder'),
            'options' => $field->getTranslations('options'),
        ])->all();
    }

    /**
     * Writes the fields from the builder. A field keeps its id (and its
     * place in the submissions) while it is in the list; new ones are
     * created, the ones taken out are deleted. A save without the builder
     * (an API call that changes only the name) leaves the fields as they are.
     *
     * @param  array<string,mixed>  $data
     */
    public function afterAdminSave(array $data): void
    {
        if (! is_array($data['builder'] ?? null)) {
            return;
        }

        $codes = app(ContentLanguages::class)->codes();

        DB::transaction(function () use ($data, $codes): void {
            $existing = $this->fields()->pluck('id')->all();
            $kept = [];

            foreach (array_values($data['builder']) as $index => $item) {
                $attributes = [
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'required' => $item['required'] ?? false,
                    'width' => $item['width'],
                    'min' => self::bound($item, 'min'),
                    'max' => self::bound($item, 'max'),
                    'sort' => ($index + 1) * 10,
                ];

                foreach (['label', 'hint', 'placeholder', 'options'] as $text) {
                    $attributes[$text] = self::texts($item[$text] ?? [], $codes);
                }

                $field = in_array($item['id'] ?? null, $existing, false) ? $this->fields()->find($item['id']) : $this->fields()->make();
                $field->fill($attributes)->save();
                $kept[] = $field->id;
            }

            $this->fields()->whereKeyNot($kept)->delete();
        });

        $this->unsetRelation('fields');
    }

    /**
     * @param  array<string,mixed>  $item
     */
    private static function bound(array $item, string $name): ?int
    {
        return in_array($item['type'], FormField::BOUNDED_TYPES) && is_numeric($item[$name] ?? null) ? (int) $item[$name] : null;
    }

    /**
     * The texts of the site's languages, trimmed; empty ones are left out.
     *
     * @param  array<string,mixed>  $texts
     * @param  array<int,string>  $codes
     * @return array<string,string>
     */
    private static function texts(array $texts, array $codes): array
    {
        $kept = [];

        foreach ($codes as $code) {
            $text = trim($texts[$code] ?? '');

            if ($text !== '') {
                $kept[$code] = $text;
            }
        }

        return $kept;
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
