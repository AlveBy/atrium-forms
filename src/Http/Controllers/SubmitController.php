<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Http\Controllers;

use Atrium\Admin\Captcha\CaptchaManager;
use Atrium\Admin\Support\ContentLanguages;
use Atrium\Forms\Mail\SubmissionMail;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormSubmission;
use Atrium\Forms\Support\SpamGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * A visitor sends a form: anti-spam, the form's own rules, the submission is
 * stored and mailed. A browser goes back to the page, to the form; a request
 * that wants JSON gets JSON.
 */
final class SubmitController
{
    public function __invoke(Request $request, ContentLanguages $languages): RedirectResponse|JsonResponse
    {
        $code = $request->route('code');

        // The site's own form only: Form belongs to sites.
        $form = Form::query()->where('code', $code)->where('active', true)->with('fields')->firstOrFail();

        $spam = SpamGuard::check($request);

        // A bot that filled the trap is told it worked and learns nothing.
        if ($spam === SpamGuard::HONEYPOT) {
            return self::sent($request, $form);
        }

        if ($spam === SpamGuard::TOO_FAST) {
            throw self::failed($request, $form, ['form' => __('atrium-forms::forms.public.too_fast')]);
        }

        $rules = [];
        $attributes = [];

        foreach ($form->fields as $field) {
            $rules["fields.{$field->name}"] = $field->publicRules();
            $attributes["fields.{$field->name}"] = mb_strtolower((string) $field->label);
        }

        // The site's captcha (atrium.captcha.driver), when one is on.
        if (config('atrium-forms.captcha', true)) {
            $rules = [...$rules, ...app(CaptchaManager::class)->rules()];
        }

        $validator = Validator::make($request->all(), $rules, [], $attributes);

        if ($validator->fails()) {
            throw self::failed($request, $form, $validator->errors()->toArray());
        }

        $default = $languages->default();
        $data = [];

        foreach ($form->fields as $field) {
            $value = $request->input("fields.{$field->name}");

            $data[] = [
                'name' => $field->name,
                'type' => $field->type,
                // In the default language: the one the site's staff read.
                'label' => $field->getTranslation('label', $default),
                'value' => $field->type === 'checkbox' ? (bool) $value : (is_scalar($value) ? trim((string) $value) : null),
            ];
        }

        $submission = new FormSubmission;
        $submission->forceFill([
            'form_id' => $form->id,
            'form_name' => $form->name,
            'data' => $data,
            'locale' => app()->getLocale(),
            'page_url' => Str::limit((string) $request->headers->get('referer'), 2000, ''),
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ])->save();

        self::mail($form, $submission);

        return self::sent($request, $form);
    }

    /**
     * Where the errors of one form go: forms on the same page keep their own.
     */
    public static function errorBag(string $code): string
    {
        return "form-{$code}";
    }

    private static function mail(Form $form, FormSubmission $submission): void
    {
        $recipients = $form->recipientList();

        if ($recipients === []) {
            return;
        }

        // A mail server that is down must not lose the submission: it is
        // stored already and stays in the admin.
        try {
            Mail::to($recipients)->locale(config('app.locale'))->send(new SubmissionMail($submission));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private static function sent(Request $request, Form $form): RedirectResponse|JsonResponse
    {
        $message = filled($form->success_message) ? $form->success_message : __('atrium-forms::forms.public.sent');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('atrium-forms.sent', $form->code)->withFragment('form-'.$form->code);
    }

    /**
     * Back to the form with its errors and what the visitor typed, less the
     * form's time and the captcha (a used captcha is not asked again); JSON
     * gets 422.
     *
     * @param  array<string,mixed>  $errors
     */
    public static function failed(Request $request, Form|string $form, array $errors): ValidationException
    {
        $code = $form instanceof Form ? $form->code : $form;
        $exception = ValidationException::withMessages($errors)->errorBag(self::errorBag($code));

        if (! $request->expectsJson()) {
            $exception->response = redirect()->to(url()->previous().'#form-'.$code)
                ->withErrors($exception->errors(), self::errorBag($code))
                ->withInput($request->except([SpamGuard::TIME_FIELD, ...CaptchaManager::INPUTS]));
        }

        return $exception;
    }
}
