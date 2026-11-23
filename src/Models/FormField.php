<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Models;

use Atrium\Admin\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

/**
 * One field of a form: its type, label (per language), whether it is
 * required, for a list its options, and how it is laid out. The fields are
 * edited on the form's own page (Form::builder), not as records of their own.
 *
 * @property int $id
 * @property int $form_id
 * @property string $name
 * @property string $type
 * @property bool $required
 * @property int $sort
 * @property int $width
 * @property int|null $min
 * @property int|null $max
 * @property-read Form|null $form
 */
class FormField extends Model
{
    use Translatable;

    /** Field types, as the site draws them. */
    public const TYPES = ['text', 'email', 'tel', 'number', 'date', 'textarea', 'select', 'radio', 'checkbox'];

    /** Types whose value is one of the options. */
    public const CHOICE_TYPES = ['select', 'radio'];

    /** Types the least and most of the builder apply to. */
    public const BOUNDED_TYPES = ['text', 'email', 'tel', 'number', 'textarea'];

    protected $table = 'form_fields';

    /** @var array<int,string> */
    protected array $translatable = ['label', 'options', 'hint', 'placeholder'];

    protected $fillable = ['form_id', 'name', 'type', 'label', 'required', 'options', 'sort', 'width', 'hint', 'placeholder', 'min', 'max'];

    protected function casts(): array
    {
        return ['required' => 'boolean', 'sort' => 'integer', 'width' => 'integer', 'min' => 'integer', 'max' => 'integer'];
    }

    /**
     * @return BelongsTo<Form,$this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * The options in the current language, or the one asked, one per line
     * in the admin.
     *
     * @return array<int,string>
     */
    public function optionList(?string $locale = null): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $this->getTranslation('options', $locale ?? app()->getLocale())) ?: [];

        return array_values(array_unique(array_filter(array_map('trim', $lines), static fn (string $line): bool => $line !== '')));
    }

    /**
     * Rules for the visitor's value. The least and most the builder set are
     * characters for text and the value itself for a number, on top of the
     * longest a type takes anyway.
     *
     * @return array<int,mixed>
     */
    public function publicRules(): array
    {
        $bounds = in_array($this->type, self::BOUNDED_TYPES) ? [
            ...($this->min !== null ? ["min:{$this->min}"] : []),
            ...($this->max !== null ? ["max:{$this->max}"] : []),
        ] : [];

        return [$this->required ? 'required' : 'nullable', ...match ($this->type) {
            'email' => ['string', 'email', 'max:255'],
            'tel' => ['string', 'max:50', 'regex:/^[0-9+()\-.\s]+$/'],
            'number' => ['numeric'],
            'date' => ['date'],
            'textarea' => ['string', 'max:10000'],
            'select', 'radio' => ['string', Rule::in($this->optionList())],
            'checkbox' => $this->required ? ['accepted'] : ['boolean'],
            default => ['string', 'max:1000'],
        }, ...$bounds];
    }
}
