<?php

declare(strict_types=1);

namespace Atrium\Forms\Models;

use Atrium\Admin\Columns\TextColumn;
use Atrium\Admin\Contracts\Administrable;
use Atrium\Admin\Fields\TextareaField;
use Atrium\Admin\Fields\TextField;
use Atrium\Admin\Traits\BelongsToSite;
use Atrium\Admin\Traits\InteractsWithAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * What a visitor sent. The values are kept with their labels as they were at
 * the time, so a submission reads the same after its form is changed or
 * deleted. The admin only reads and deletes them.
 *
 * @property int $id
 * @property int|null $site_id
 * @property int|null $form_id
 * @property string $form_name
 * @property array<int,array{name:string,type:string,label:string,value:mixed}> $data
 * @property string|null $locale
 * @property string|null $page_url
 * @property string|null $ip
 * @property string|null $user_agent
 * @property Carbon|null $created_at
 * @property-read string $content
 * @property-read string $preview
 * @property-read string $submitted_at
 */
class FormSubmission extends Model implements Administrable
{
    use BelongsToSite, InteractsWithAdmin;

    protected $table = 'form_submissions';

    /** Written by the public form only, never by the admin's form. */
    protected $guarded = ['*'];

    /** @var list<string> */
    protected $appends = ['content', 'preview', 'submitted_at'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    /**
     * @return BelongsTo<Form,$this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * "Label: value" lines, for the admin and the mail.
     */
    public function getContentAttribute(): string
    {
        return collect($this->data ?? [])
            ->map(fn (array $item): string => $item['label'].': '.self::text($item['value'] ?? null))
            ->implode("\n");
    }

    public function getPreviewAttribute(): string
    {
        $values = collect($this->data ?? [])
            ->map(fn (array $item): string => self::text($item['value'] ?? null))
            ->filter(fn (string $value): bool => $value !== '');

        return Str::limit($values->implode(' · '), 100);
    }

    public function getSubmittedAtAttribute(): string
    {
        return (string) $this->created_at?->format('Y-m-d H:i:s');
    }

    public static function text(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? __('atrium-forms::forms.public.yes') : __('atrium-forms::forms.public.no'),
            is_scalar($value) => (string) $value,
            default => '',
        };
    }

    public static function isCreatableInAdmin(): bool
    {
        return false;
    }

    public static function isEditableInAdmin(): bool
    {
        return false;
    }

    public static function getAdminLabel(): string
    {
        return __('atrium-forms::forms.modules.submissions');
    }

    public static function getAdministrationFields(): array
    {
        return [
            TextField::make('form_name')->label(__('atrium-forms::forms.fields.form'))->readonly()->hideOnCreate(),
            TextField::make('submitted_at')->label(__('atrium-forms::forms.fields.submitted_at'))->readonly()->hideOnCreate(),
            TextareaField::make('content')->label(__('atrium-forms::forms.fields.content'))->rows(10)->readonly()->hideOnCreate(),
            TextField::make('page_url')->label(__('atrium-forms::forms.fields.page_url'))->readonly()->hideOnCreate(),
            TextField::make('locale')->label(__('atrium-forms::forms.fields.locale'))->readonly()->hideOnCreate(),
            TextField::make('ip')->label(__('atrium-forms::forms.fields.ip'))->readonly()->hideOnCreate(),
            TextField::make('user_agent')->label(__('atrium-forms::forms.fields.user_agent'))->readonly()->hideOnCreate(),
        ];
    }

    public static function getAdministrationListColumns(): array
    {
        return [
            TextColumn::make('id')->label(__('atrium-forms::forms.fields.id'))->sortable(),
            TextColumn::make('submitted_at')->label(__('atrium-forms::forms.fields.submitted_at')),
            TextColumn::make('form_name')->label(__('atrium-forms::forms.fields.form'))->sortable()->searchable(),
            TextColumn::make('preview')->label(__('atrium-forms::forms.fields.content')),
        ];
    }
}
