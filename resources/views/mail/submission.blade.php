{{--
    This file is part of Atrium (https://github.com/AlveBy/atrium).
    Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $submission->form_name }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,Helvetica,sans-serif;color:#333;">
    <div style="max-width:600px;margin:0 auto;padding:20px;">
        <div style="background:#2563eb;color:#fff;padding:18px 24px;border-radius:4px 4px 0 0;">
            <h1 style="margin:0;font-size:18px;">{{ __('atrium-forms::forms.mail.heading', ['form' => $submission->form_name]) }}</h1>
            <div style="margin-top:4px;font-size:14px;opacity:.9;">{{ $submission->submitted_at }}</div>
        </div>
        <div style="background:#fff;padding:24px;border:1px solid #e5e5e5;border-top:none;border-radius:0 0 4px 4px;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                @foreach ($submission->data as $item)
                    <tr>
                        <th style="text-align:left;vertical-align:top;padding:6px 12px 6px 0;color:#555;font-weight:normal;width:35%;">{{ $item['label'] }}</th>
                        <td style="padding:6px 0;white-space:pre-wrap;">{{ \Atrium\Forms\Models\FormSubmission::text($item['value'] ?? null) }}</td>
                    </tr>
                @endforeach
            </table>

            @if ($submission->page_url)
                <p style="margin:18px 0 0;font-size:13px;color:#757575;">
                    {{ __('atrium-forms::forms.mail.page') }} {{ $submission->page_url }}
                </p>
            @endif
        </div>
    </div>
</body>
</html>
