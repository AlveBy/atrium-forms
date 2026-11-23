<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Support;

use Atrium\Admin\Captcha\CaptchaManager;
use Atrium\Admin\Support\ContentLanguages;
use Atrium\Forms\Http\Controllers\ScriptController;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormField;

/**
 * What a developer copies from the form's page in the admin to put the form
 * on a site: the Blade tags, markup of their own with this form's fields, a
 * fetch call that sends it, and the answers it gets.
 */
final class Snippets
{
    /** The language of the snippets: the site's default, the one the staff read. */
    private readonly string $locale;

    public function __construct(private readonly Form $form)
    {
        $this->locale = app(ContentLanguages::class)->default();
    }

    public function blade(): string
    {
        $code = $this->form->code;

        return implode("\n", [
            "<x-atrium-forms::form code=\"{$code}\" />",
            "<x-atrium-forms::form code=\"{$code}\" ajax />",
            "<x-atrium-forms::form code=\"{$code}\" modal />",
            "@atriumForm('{$code}', ['modal' => true])",
        ]);
    }

    /**
     * A form in a Blade template of this site, its look left to the site:
     * the hidden fields carry the token and the anti-spam check.
     */
    public function markup(): string
    {
        $code = $this->form->code;
        $lines = [
            "<form id=\"form-{$code}\" method=\"post\" action=\"{{ route('atrium.forms.submit', '{$code}') }}\">",
            '    @csrf',
            '    <input type="hidden" name="'.SpamGuard::TIME_FIELD.'" value="{{ \\'.SpamGuard::class.'::token() }}">',
            '    <div hidden><input type="text" name="'.SpamGuard::honeypotName().'" tabindex="-1" autocomplete="off"></div>',
            '    <p role="status" data-atrium-sent></p>',
            '    <p role="alert" data-atrium-alert></p>',
        ];

        foreach ($this->form->fields as $field) {
            $lines = [...$lines, '', ...$this->field($field)];
        }

        if ($this->captcha()) {
            $lines = [...$lines, '', '    <x-atrium::captcha data-error-for="captcha" />'];
        }

        $button = e((string) ($this->form->getTranslation('button_label', $this->locale) ?: __('atrium-forms::forms.public.submit', [], $this->locale)));

        return implode("\n", [...$lines, '', "    <button type=\"submit\">{$button}</button>", '</form>']);
    }

    /**
     * The script of the module on a page: the form above with data-atrium-form.
     */
    public function script(): string
    {
        return '<script src="{{ route(\'atrium.forms.script\', [\'v\' => \\'.ScriptController::class.'::version()]) }}" defer></script>';
    }

    /**
     * Sends the form above from a script of the site's own.
     */
    public function fetch(): string
    {
        $captcha = $this->captcha()
            ? "\n\n    // Every answer uses the captcha up and brings a new one.\n    form.querySelector('.atrium-captcha').innerHTML = data.captcha;"
            : '';

        return <<<JS
            const form = document.getElementById('form-{$this->form->code}');

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const body = new FormData(form);
                const xsrf = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

                // A page kept in a cache has an old token: the cookie has the fresh one.
                if (xsrf) {
                    body.delete('_token');
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { Accept: 'application/json', ...(xsrf && { 'X-XSRF-TOKEN': decodeURIComponent(xsrf[1]) }) },
                    body,
                });
                const data = await response.json();
                const errors = Object.values(data.errors ?? {}).flat();

                form.querySelector('[data-atrium-sent]').textContent = response.ok ? data.message : '';
                form.querySelector('[data-atrium-alert]').textContent = response.ok ? '' : errors.join(' ') || data.message;

                if (response.ok) {
                    form.reset();
                }{$captcha}
            });
            JS;
    }

    /**
     * What the server answers, with this form's messages.
     */
    public function json(): string
    {
        $field = $this->form->fields->first();
        $message = $this->form->getTranslation('success_message', $this->locale) ?: __('atrium-forms::forms.public.sent', [], $this->locale);
        $error = $field === null
            ? __('atrium-forms::forms.public.too_fast', [], $this->locale)
            : __('validation.required', ['attribute' => mb_strtolower((string) $field->getTranslation('label', $this->locale))], $this->locale);
        $captcha = $this->captcha() ? ['captcha' => '…'] : [];

        return implode("\n\n", [
            "200 OK\n".self::encode(['message' => $message, ...$captcha]),
            "422 Unprocessable Content\n".self::encode([
                'message' => $error,
                'errors' => [$field === null ? 'form' : "fields.{$field->name}" => [$error]],
                ...$captcha,
            ]),
            "429 Too Many Requests\n".self::encode(['message' => __('atrium-forms::forms.public.too_many', [], $this->locale)]),
        ]);
    }

    /**
     * @return list<string>
     */
    private function field(FormField $field): array
    {
        $id = "form-{$this->form->code}-{$field->name}";
        $name = "fields[{$field->name}]";
        $label = e((string) $field->getTranslation('label', $this->locale));
        $options = $field->optionList($this->locale);
        $required = $field->required ? ' required' : '';

        $control = match ($field->type) {
            'checkbox' => ["        <label><input type=\"checkbox\" id=\"{$id}\" name=\"{$name}\" value=\"1\"{$required}> {$label}</label>"],
            'radio' => [
                "        <fieldset><legend>{$label}</legend>",
                ...array_map(static fn (string $option): string => '            <label><input type="radio" name="'.$name.'" value="'.e($option).'"'.$required.'> '.e($option).'</label>', $options),
                '        </fieldset>',
            ],
            'select' => [
                "        <label for=\"{$id}\">{$label}</label>",
                "        <select id=\"{$id}\" name=\"{$name}\"{$required}>",
                '            <option value="">'.e(__('atrium-forms::forms.public.choose', [], $this->locale)).'</option>',
                ...array_map(static fn (string $option): string => '            <option>'.e($option).'</option>', $options),
                '        </select>',
            ],
            'textarea' => ["        <label for=\"{$id}\">{$label}</label>", "        <textarea id=\"{$id}\" name=\"{$name}\"{$required}></textarea>"],
            default => ["        <label for=\"{$id}\">{$label}</label>", "        <input type=\"{$field->type}\" id=\"{$id}\" name=\"{$name}\"{$required}>"],
        };

        return ["    <div data-field=\"{$field->name}\">", ...$control, '    </div>'];
    }

    private function captcha(): bool
    {
        return config('atrium-forms.captcha', true) && app(CaptchaManager::class)->enabled();
    }

    /**
     * @param  array<string,mixed>  $answer
     */
    private static function encode(array $answer): string
    {
        return (string) json_encode($answer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
