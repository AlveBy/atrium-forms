{{--
    This file is part of Atrium (https://github.com/AlveBy/atrium).
    Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
--}}
@extends($layout, ['metaTitle' => $form->name])

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
    <h1>{{ $form->name }}</h1>

    <x-atrium-forms::form :code="$form->code" ajax />
@endsection
