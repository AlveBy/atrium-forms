<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the form builder sets on a field besides its type and label: how many
 * of the twelve columns it takes, a hint under it, a placeholder inside it,
 * and the least and most it takes (characters, or the number itself). The
 * fields there are keep the whole row, as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_fields', function (Blueprint $table): void {
            $table->unsignedTinyInteger('width')->default(12)->after('options');
            $table->json('hint')->nullable()->after('width');
            $table->json('placeholder')->nullable()->after('hint');
            $table->integer('min')->nullable()->after('placeholder');
            $table->integer('max')->nullable()->after('min');
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table): void {
            $table->dropColumn(['width', 'hint', 'placeholder', 'min', 'max']);
        });
    }
};
