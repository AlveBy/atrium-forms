<?php

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

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
}
