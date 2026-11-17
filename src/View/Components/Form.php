<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\View\Components;

use Atrium\Admin\Support\ContentLanguages;
use Atrium\Forms\FormsServiceProvider;
use Atrium\Forms\Http\Controllers\SubmitController;
use Atrium\Forms\Models\Form as FormModel;
use Atrium\Forms\Support\SpamGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Component;

/**
 * <x-atrium-forms::form code="contact" /> — a form of the current site, in
 * the page's language. Nothing is drawn for a form that is missing or off.
 */
final class Form extends Component
{
    public function __construct(public string $code) {}

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

        return view('atrium-forms::components.form', [
            'form' => $form,
            'action' => app(ContentLanguages::class)->route('atrium.forms.submit', ['code' => $form->code]),
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
