<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->index();
            $table->string('name');
            $table->string('code');
            $table->boolean('active')->default(true);
            $table->text('recipients')->nullable();
            $table->json('button_label')->nullable();
            $table->json('success_message')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'code']);
        });

        Schema::create('form_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 20)->default('text');
            $table->json('label');
            $table->boolean('required')->default(false);
            $table->json('options')->nullable();
            $table->integer('sort')->default(500);
            $table->timestamps();

            $table->unique(['form_id', 'name']);
        });

        // A submission outlives its form: the name and the labels are copied.
        Schema::create('form_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->index();
            $table->foreignId('form_id')->nullable()->constrained('forms')->nullOnDelete();
            $table->string('form_name');
            $table->json('data');
            $table->string('locale', 10)->nullable();
            $table->string('page_url', 2048)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
