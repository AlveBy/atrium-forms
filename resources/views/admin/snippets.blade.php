{{--
    This file is part of Atrium (https://github.com/AlveBy/atrium).
    Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
--}}
{{--
    The form's page in the admin: how to put the form on a site. Drawn by a
    DisplayField, so plain HTML with the panel's colours, no Tailwind classes.
--}}
@php
    $code = 'display:block;margin:.375rem 0 0;padding:.75rem;overflow-x:auto;border:1px solid var(--atrium-line);border-radius:.375rem;background:var(--atrium-surface-alt);color:var(--atrium-fg);font:.8125rem/1.5 ui-monospace,SFMono-Regular,Menlo,monospace;white-space:pre';
    $note = 'margin:.375rem 0 0;color:var(--atrium-muted)';
@endphp
<div data-form-snippets style="display:grid;grid-template-columns:minmax(0,1fr);gap:1.25rem">
    <section>
        <h3 style="font-weight:600">{{ __('atrium-forms::forms.snippets.blade') }}</h3>
        <pre tabindex="0" style="{{ $code }}" data-snippet="blade"><code>{{ $snippets->blade() }}</code></pre>
    </section>

    <section>
        <h3 style="font-weight:600">{{ __('atrium-forms::forms.snippets.markup') }}</h3>
        <p style="{{ $note }}">{{ __('atrium-forms::forms.snippets.markup_note') }}</p>
        <pre tabindex="0" style="{{ $code }}" data-snippet="markup"><code>{{ $snippets->markup() }}</code></pre>
        <p style="{{ $note }}">{{ __('atrium-forms::forms.snippets.script_note') }}</p>
        <pre tabindex="0" style="{{ $code }}" data-snippet="script"><code>{{ $snippets->script() }}</code></pre>
    </section>

    <section>
        <h3 style="font-weight:600">{{ __('atrium-forms::forms.snippets.fetch') }}</h3>
        <p style="{{ $note }}">{{ __('atrium-forms::forms.snippets.fetch_note') }}</p>
        <pre tabindex="0" style="{{ $code }}" data-snippet="fetch"><code>{{ $snippets->fetch() }}</code></pre>
    </section>

    <section>
        <h3 style="font-weight:600">{{ __('atrium-forms::forms.snippets.json') }}</h3>
        <p style="{{ $note }}">{{ __('atrium-forms::forms.snippets.json_note') }}</p>
        <pre tabindex="0" style="{{ $code }}" data-snippet="json"><code>{{ $snippets->json() }}</code></pre>
    </section>
</div>
