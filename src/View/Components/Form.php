<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\View\Components;

use Atrium\Admin\Support\ContentLanguages;
use Atrium\Forms\FormsServiceProvider;
use Atrium\Forms\Http\Controllers\ScriptController;
use Atrium\Forms\Http\Controllers\SubmitController;
use Atrium\Forms\Models\Form as FormModel;
use Atrium\Forms\Support\SpamGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Component;

/**
 * <x-atrium-forms::form code="contact" /> — a form of the current site, in
 * the page's language. Nothing is drawn for a form that is missing or off.
 *
 *   ajax                      sent without reloading the page, errors at the fields
 *   modal                     a button that opens the form in a window (sent the same way)
 *   trigger="Write to us"     the button's text; the form's name when left out
 *   title="Write to us"       the window's heading; the form's name when left out
 */
final class Form extends Component
{
    public function __construct(
        public string $code,
        public bool $ajax = false,
        public bool $modal = false,
        public ?string $trigger = null,
        public ?string $title = null,
    ) {}

    /**
     * The component for the directive, `@atriumForm('contact', ['modal' => true, 'class' => 'mine'])`:
     * ajax, modal, trigger and title are its own, the rest go to the <form> as attributes.
     *
     * @param  array<string,mixed>  $options
     */
    public static function inline(string $code, array $options = []): string
    {
        $text = static fn (string $key): ?string => is_string($options[$key] ?? null) ? $options[$key] : null;
        $component = new self($code, (bool) ($options['ajax'] ?? false), (bool) ($options['modal'] ?? false), $text('trigger'), $text('title'));

        return Blade::renderComponent($component->withAttributes(Arr::except($options, ['ajax', 'modal', 'trigger', 'title'])));
    }

    public function render(): View|string
    {
        if (! FormsServiceProvider::installed()) {
            return '';
        }

        $form = FormModel::query()->where('code', $this->code)->where('active', true)->with('fields')->first();

        if ($form === null) {
            return '';
        }

        $session = request()->hasSession() ? request()->session() : null;
        $errors = $session?->get('errors');

        $languages = app(ContentLanguages::class);

        return view('atrium-forms::components.form', [
            'form' => $form,
            'action' => $languages->route('atrium.forms.submit', ['code' => $form->code]),
            // The script that sends the form; a window is sent that way too:
            // reloading the page would close it.
            'script' => $this->ajax || $this->modal ? route('atrium.forms.script', ['v' => ScriptController::version()]) : null,
            'page' => $languages->route('atrium.forms.show', ['code' => $form->code]),
            'opener' => $this->trigger ?? $form->name,
            'heading' => $this->title ?? $form->name,
            'errors' => $errors instanceof ViewErrorBag ? $errors->getBag(SubmitController::errorBag($form->code)) : new MessageBag,
            // Old input belongs to the form that was sent, not to every form on the page.
            'old' => $session?->getOldInput('_form') === $form->code ? Arr::wrap($session->getOldInput('fields', [])) : [],
            'sent' => $session?->get('atrium-forms.sent') === $form->code,
            'message' => filled($form->success_message) ? $form->success_message : __('atrium-forms::forms.public.sent'),
            'button' => filled($form->button_label) ? $form->button_label : __('atrium-forms::forms.public.submit'),
            'honeypot' => SpamGuard::honeypotName(),
            'timeField' => SpamGuard::TIME_FIELD,
            'token' => SpamGuard::token(),
        ]);
    }
}
