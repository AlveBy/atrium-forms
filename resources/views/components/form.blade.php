{{--
    This file is part of Atrium (https://github.com/AlveBy/atrium).
    Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
--}}
@once
<style>
    .atrium-form { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 1rem; max-width: 40rem; }
    .atrium-form > * { grid-column: 1 / -1; }
    .atrium-form > .atrium-form__field { grid-column: span var(--atrium-span, 12) / span var(--atrium-span, 12); display: grid; gap: .35rem; align-content: start; }
    @media (max-width: 40rem) { .atrium-form > .atrium-form__field { grid-column: 1 / -1; } }
    .atrium-form__hint { margin: 0; font-size: .875em; }
    .atrium-form__field input:not([type=checkbox]):not([type=radio]), .atrium-form__field select, .atrium-form__field textarea { font: inherit; padding: .5rem .6rem; border: 1px solid var(--control, #6b7280); border-radius: .375rem; background: var(--bg, #fff); color: var(--text, inherit); }
    .atrium-form__choice { display: flex; gap: .5rem; align-items: center; }
    .atrium-form__error { color: var(--error, #b91c1c); margin: 0; }
    .atrium-form__sent { margin: 0; font-weight: bold; }
    .atrium-form__sent:empty, .atrium-form__error:empty { display: none; }
    .atrium-form .atrium-captcha > .atrium-form__error { flex-basis: 100%; }
    .atrium-form-open { display: inline-block; font: inherit; padding: .5rem 1rem; border: 1px solid var(--control, #6b7280); border-radius: .375rem; background: var(--surface, #f3f4f6); color: var(--text, #16181d); text-decoration: none; cursor: pointer; }
    .atrium-form-dialog { width: min(40rem, calc(100vw - 2rem)); max-height: calc(100vh - 2rem); padding: 1.25rem; border: 1px solid var(--line, #e2e5ea); border-radius: .5rem; background: var(--bg, #fff); color: var(--text, #16181d); }
    .atrium-form-dialog::backdrop { background: rgb(0 0 0 / .6); }
    .atrium-form-dialog__head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; }
    .atrium-form-dialog__head h2 { margin: 0; font-size: 1.25rem; line-height: 1.3; }
    .atrium-form-dialog__close { flex: none; width: 2.75rem; height: 2.75rem; font: inherit; font-size: 1.5rem; line-height: 1; border: 1px solid var(--control, #6b7280); border-radius: .375rem; background: var(--surface, #f3f4f6); color: var(--text, #16181d); cursor: pointer; }
    .atrium-form-open:focus-visible, .atrium-form-dialog__close:focus-visible, .atrium-form-dialog :focus-visible { outline: 3px solid var(--focus, #1d4ed8); outline-offset: 2px; }
    .atrium-form button { justify-self: start; font: inherit; padding: .5rem 1rem; border: 1px solid var(--control, #6b7280); border-radius: .375rem; background: var(--surface, #f3f4f6); color: var(--text, inherit); cursor: pointer; }
    .atrium-form .atrium-captcha { display: flex; flex-wrap: wrap; gap: .5rem 1rem; align-items: center; }
    .atrium-form .atrium-captcha input[type=text] { width: 5rem; font: inherit; padding: .5rem .6rem; border: 1px solid var(--control, #6b7280); border-radius: .375rem; background: var(--bg, #fff); color: var(--text, inherit); }
    .atrium-form__trap { position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden; }
</style>
@endonce
@if ($script !== null)
    @once
        <script src="{{ $script }}" defer></script>
    @endonce
@endif
@if ($modal)
    <a href="{{ $page }}" class="atrium-form-open" data-atrium-form-open="{{ $form->code }}" aria-haspopup="dialog" aria-controls="form-{{ $form->code }}-dialog" aria-expanded="false">{{ $opener }}</a>
    <dialog id="form-{{ $form->code }}-dialog" class="atrium-form-dialog" data-atrium-dialog="{{ $form->code }}" aria-labelledby="form-{{ $form->code }}-title">
        <div class="atrium-form-dialog__head">
            <h2 id="form-{{ $form->code }}-title">{{ $heading }}</h2>
            <button type="button" class="atrium-form-dialog__close" data-atrium-form-close aria-label="{{ __('atrium-forms::forms.public.close') }}"><span aria-hidden="true">&times;</span></button>
        </div>
@endif
<form id="form-{{ $form->code }}" method="post" action="{{ $action }}" {{ $attributes->merge(['class' => 'atrium-form']) }} @if ($script !== null) data-atrium-form data-atrium-failed="{{ __('atrium-forms::forms.public.failed') }}" novalidate @endif>
    @csrf
    <input type="hidden" name="_form" value="{{ $form->code }}">
    <input type="hidden" name="{{ $timeField }}" value="{{ $token }}">

    <div class="atrium-form__trap" aria-hidden="true">
        <label>{{ __('atrium-forms::forms.public.honeypot') }}
            <input type="text" name="{{ $honeypot }}" value="" tabindex="-1" autocomplete="off">
        </label>
    </div>

    {{-- Kept on the page even when empty: a screen reader hears what the script puts in. --}}
    <p class="atrium-form__sent" role="status" tabindex="-1" data-atrium-sent>{{ $sent ? $message : '' }}</p>
    <p class="atrium-form__error" role="alert" tabindex="-1" data-atrium-alert>{{ $errors->first('form') }}</p>

    @foreach ($form->fields as $field)
        @php
            $id = 'form-'.$form->code.'-'.$field->name;
            $name = 'fields['.$field->name.']';
            $key = 'fields.'.$field->name;
            $value = $old[$field->name] ?? '';
            $invalid = $errors->has($key);
            $hint = (string) $field->hint;
            $described = trim(($hint !== '' ? $id.'-hint ' : '').($invalid ? $id.'-error' : ''));
            $bounds = in_array($field->type, \Atrium\Forms\Models\FormField::BOUNDED_TYPES, true)
                ? ($field->type === 'number' ? ['min' => $field->min, 'max' => $field->max] : ['minlength' => $field->min, 'maxlength' => $field->max])
                : [];
        @endphp
        <div class="atrium-form__field" style="--atrium-span: {{ $field->width }}" data-field="{{ $field->name }}">
            @if ($field->type === 'checkbox')
                <label class="atrium-form__choice">
                    <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked($value) @required($field->required) @if ($invalid) aria-invalid="true" @endif @if ($described !== '') aria-describedby="{{ $described }}" @endif>
                    {{ $field->label }}
                </label>
            @elseif ($field->type === 'radio')
                <fieldset @if ($described !== '') aria-describedby="{{ $described }}" @endif>
                    <legend>{{ $field->label }}</legend>
                    @foreach ($field->optionList() as $index => $option)
                        <label class="atrium-form__choice">
                            <input type="radio" name="{{ $name }}" value="{{ $option }}" @checked($value === $option) @required($field->required && $index === 0)>
                            {{ $option }}
                        </label>
                    @endforeach
                </fieldset>
            @else
                <label for="{{ $id }}">{{ $field->label }}</label>
                @if ($field->type === 'textarea')
                    <textarea id="{{ $id }}" name="{{ $name }}" rows="5" placeholder="{{ $field->placeholder }}" @foreach (array_filter($bounds, 'is_int') as $attribute => $bound) {{ $attribute }}="{{ $bound }}" @endforeach @required($field->required) @if ($invalid) aria-invalid="true" @endif @if ($described !== '') aria-describedby="{{ $described }}" @endif>{{ $value }}</textarea>
                @elseif ($field->type === 'select')
                    <select id="{{ $id }}" name="{{ $name }}" @required($field->required) @if ($invalid) aria-invalid="true" @endif @if ($described !== '') aria-describedby="{{ $described }}" @endif>
                        <option value="">{{ __('atrium-forms::forms.public.choose') }}</option>
                        @foreach ($field->optionList() as $option)
                            <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="{{ $field->type }}" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $field->placeholder }}" @foreach (array_filter($bounds, 'is_int') as $attribute => $bound) {{ $attribute }}="{{ $bound }}" @endforeach @required($field->required) @if ($invalid) aria-invalid="true" @endif @if ($described !== '') aria-describedby="{{ $described }}" @endif>
                @endif
            @endif
            @if ($hint !== '')
                <p class="atrium-form__hint" id="{{ $id }}-hint">{{ $hint }}</p>
            @endif
            @if ($invalid)
                <p class="atrium-form__error" id="{{ $id }}-error" data-atrium-error>{{ $errors->first($key) }}</p>
            @endif
        </div>
    @endforeach

    @if (config('atrium-forms.captcha', true))
        <x-atrium::captcha data-error-for="captcha" />
        @if ($errors->has('captcha'))
            <p class="atrium-form__error" id="form-{{ $form->code }}-captcha-error" role="alert" data-atrium-error>{{ $errors->first('captcha') }}</p>
        @endif
    @endif

    <button type="submit">{{ $button }}</button>
</form>
@if ($modal)
    </dialog>
@endif
