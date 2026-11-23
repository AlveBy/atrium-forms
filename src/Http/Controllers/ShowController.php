<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Http\Controllers;

use Atrium\Forms\Models\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as Views;

/**
 * A form on a page of its own, GET /{prefix}/{code}: where the button of a
 * form in a window leads when the script cannot open the window. In the
 * site's theme when there is one; not for search engines.
 */
final class ShowController
{
    public function __invoke(Request $request): View
    {
        $form = Form::query()->where('code', $request->route('code'))->where('active', true)->firstOrFail();

        return view('atrium-forms::page', [
            'form' => $form,
            'layout' => Views::exists('atrium-theme::layout') ? 'atrium-theme::layout' : 'atrium-forms::layout',
        ]);
    }
}
