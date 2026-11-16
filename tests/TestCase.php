<?php

declare(strict_types=1);

namespace Atrium\Forms\Tests;

use Atrium\Admin\AtriumServiceProvider;
use Atrium\Admin\Models\AdminUser;
use Atrium\Forms\FormsServiceProvider;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Laravel\Mcp\Server\McpServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tighten\Ziggy\ZiggyServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            InertiaServiceProvider::class,
            ZiggyServiceProvider::class,
            McpServiceProvider::class,
            AtriumServiceProvider::class,
            FormsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $config = $app['config'];
        $config->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        // CI runs the suite against MySQL and PostgreSQL too.
        $target = env('DB_CONNECTION');
        $config->set('database.default', in_array($target, ['mysql', 'pgsql'], true) ? $target : 'testing');
        $config->set('atrium.demo', false);
        $config->set('atrium.modules', ['alveby/atrium-forms']);
        $config->set('atrium.auth.two_factor.enabled', false);
        $config->set('atrium.dashboard.timings.enabled', false);
    }

    protected function defineRoutes($router): void
    {
        // A page of the site with the form on it.
        Route::middleware('web')->group(function (): void {
            Route::localized(function (): void {
                Route::get('contacts', fn () => Blade::render('<x-atrium-forms::form code="contact" />'))->name('contacts');
            });
        });
    }

    /**
     * On MySQL and PostgreSQL the whole suite shares one database, and
     * RefreshDatabase migrates it once, for whichever package came first:
     * without the forms tables. Each side migrates afresh.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        RefreshDatabaseState::$migrated = false;
    }

    public static function tearDownAfterClass(): void
    {
        RefreshDatabaseState::$migrated = false;
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function admin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret-password',
            'is_active' => true,
        ]);

        $admin->forceFill(['is_superadmin' => true])->save();

        return $admin;
    }

    /**
     * "Contact us": name, e-mail, a topic from a list, a message, consent.
     */
    protected function contactForm(array $attributes = []): Form
    {
        $form = Form::query()->create([
            'name' => 'Contact us',
            'code' => 'contact',
            'recipients' => 'sales@example.com, help@example.com',
            ...$attributes,
        ]);

        $fields = [
            ['name' => 'name', 'type' => 'text', 'label' => 'Your name', 'required' => true],
            ['name' => 'email', 'type' => 'email', 'label' => 'E-mail', 'required' => true],
            ['name' => 'topic', 'type' => 'select', 'label' => 'Topic', 'options' => "Order\nDelivery"],
            ['name' => 'message', 'type' => 'textarea', 'label' => 'Message'],
            ['name' => 'consent', 'type' => 'checkbox', 'label' => 'I agree', 'required' => true],
        ];

        foreach ($fields as $index => $field) {
            FormField::query()->create(['form_id' => $form->id, 'sort' => $index * 10, ...$field]);
        }

        return $form;
    }

    /**
     * What a person sends a few seconds after the page opened.
     *
     * @param  array<string,mixed>  $fields
     * @return array<string,mixed>
     */
    protected function submission(array $fields = []): array
    {
        return [
            '_form' => 'contact',
            '_t' => Crypt::encryptString((string) (time() - 10)),
            'website' => '',
            'fields' => [
                'name' => 'Ann',
                'email' => 'ann@example.com',
                'topic' => 'Delivery',
                'message' => 'Where is my parcel?',
                'consent' => '1',
                ...$fields,
            ],
        ];
    }
}
