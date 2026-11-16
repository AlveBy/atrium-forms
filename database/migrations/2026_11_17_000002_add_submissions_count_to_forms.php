<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * How many submissions a form has, kept on the form so the list does not
 * count them on every page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table): void {
            $table->unsignedInteger('submissions_count')->default(0)->after('success_message');
        });

        $forms = DB::getTablePrefix().'forms';
        $submissions = DB::getTablePrefix().'form_submissions';

        DB::table('forms')->update([
            'submissions_count' => DB::raw("(select count(*) from {$submissions} where {$submissions}.form_id = {$forms}.id)"),
        ]);
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table): void {
            $table->dropColumn('submissions_count');
        });
    }
};
