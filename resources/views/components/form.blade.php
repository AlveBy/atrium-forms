{{--
    This file is part of Atrium (https://github.com/AlveBy/atrium).
    Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
--}}
@once
<style>
    .atrium-form { display: grid; gap: 1rem; max-width: 36rem; }
    .atrium-form__field { display: grid; gap: .35rem; }
    .atrium-form__field input:not([type=checkbox]):not([type=radio]), .atrium-form__field select, .atrium-form__field textarea { font: inherit; padding: .5rem .6rem; border: 1px solid var(--control, #6b7280); border-radius: .375rem; background: var(--bg, #fff); color: var(--text, inherit); }
    .atrium-form__choice { display: flex; gap: .5rem; align-items: center; }
    .atrium-form__error { color: #b91c1c; margin: 0; }
    .atrium-form__sent { margin: 0; font-weight: bold; }
    .atrium-form button { justify-self: start; font: inherit; padding: .5rem 1rem; border: 1px solid var(--control, #6b7280); border-radius: .375rem; background: var(--surface, #f3f4f6); color: var(--text, inherit); cursor: pointer; }
    .atrium-form .atrium-captcha { display: flex; flex-wrap: wrap; gap: .5rem 1rem; align-items: center; }
    .atrium-form .atrium-captcha input[type=text] { width: 5rem; font: inherit; padding: .5rem .6rem; border: 1px solid var(--control, #6b7280); border-radius: .375rem; background: var(--bg, #fff); color: var(--text, inherit); }
    .atrium-form__trap { position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden; }
</style>
@endonce
<form id="form-{{ $form->code }}" method="post" action="{{ $action }}" {{ $attributes->merge(['class' => 'atrium-form']) }}>
    @csrf
    <input type="hidden" name="_form" value="{{ $form->code }}">
    <input type="hidden" name="{{ $timeField }}" value="{{ $token }}">

    <div class="atrium-form__trap" aria-hidden="true">
        <label>{{ __('atrium-forms::forms.public.honeypot') }}
            <input type="text" name="{{ $honeypot }}" value="" tabindex="-1" autocomplete="off">
        </label>
    </div>

    @if ($sent)
        <p class="atrium-form__sent" role="status">{{ $message }}</p>
    @endif

    @if ($errors->has('form'))
        <p class="atrium-form__error" role="alert">{{ $errors->first('form') }}</p>
    @endif

    @foreach ($form->fields as $field)
        @php
            $id = 'form-'.$form->code.'-'.$field->name;
            $name = 'fields['.$field->name.']';
            $key = 'fields.'.$field->name;
            $value = $old[$field->name] ?? '';
            $invalid = $errors->has($key);
        @endphp
        <div class="atrium-form__field">
            @if ($field->type === 'checkbox')
                <label class="atrium-form__choice">
                    <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked($value) @required($field->required) @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
                    {{ $field->label }}
                </label>
            @elseif ($field->type === 'radio')
                <fieldset @if ($invalid) aria-describedby="{{ $id }}-error" @endif>
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
                    <textarea id="{{ $id }}" name="{{ $name }}" rows="5" @required($field->required) @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>{{ $value }}</textarea>
                @elseif ($field->type === 'select')
                    <select id="{{ $id }}" name="{{ $name }}" @required($field->required) @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
                        <option value="">{{ __('atrium-forms::forms.public.choose') }}</option>
                        @foreach ($field->optionList() as $option)
                            <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="{{ $field->type }}" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" @required($field->required) @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
                @endif
            @endif
            @if ($invalid)
                <p class="atrium-form__error" id="{{ $id }}-error">{{ $errors->first($key) }}</p>
            @endif
        </div>
    @endforeach

    @if (config('atrium-forms.captcha', true))
        <x-atrium::captcha />
        @if ($errors->has('captcha'))
            <p class="atrium-form__error" role="alert">{{ $errors->first('captcha') }}</p>
        @endif
    @endif

    <button type="submit">{{ $button }}</button>
</form>
