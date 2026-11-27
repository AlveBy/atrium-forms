<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Admin\Builder\BlockTypes;
use Atrium\Admin\Embeds\Embeds;
use Atrium\Forms\Tests\TestCase;

/**
 * Required with Composer but not installed with atrium:module:install:
 * no tables, so nothing of the module may touch them.
 */
final class NotInstalledTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('atrium.modules', []);
    }

    public function test_the_module_stays_out_of_the_panel_and_the_site(): void
    {
        $this->actingAs($this->admin(), 'admin')->get('/admin/forms')->assertNotFound();

        $this->get('/contacts')->assertOk()->assertDontSee('<form', false);
        $this->post('/forms/contact', $this->submission())->assertNotFound();
    }

    public function test_the_form_is_not_offered_to_editors_and_its_placeholders_show_nothing(): void
    {
        $embeds = $this->app->make(Embeds::class);
        $admin = $this->admin();

        $this->assertNull($embeds->get('form'));
        $this->assertSame([], $embeds->types($admin));
        $this->assertSame('<p>a</p>', $embeds->render('<p>a</p><div data-atrium-embed="form" data-id="1"></div>'));
        $this->actingAs($admin, 'admin')->getJson('/admin/embeds/form')->assertNotFound();
    }

    public function test_the_site_builder_does_not_offer_its_block(): void
    {
        $types = $this->app->make(BlockTypes::class);

        $this->assertNull($types->get('form'));
        $this->assertArrayNotHasKey('form', $types->all());
    }
}
