<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Admin\Captcha\CaptchaManager;
use Atrium\Forms\FormsServiceProvider;
use Atrium\Forms\Http\Controllers\SubmitController;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormField;
use Atrium\Forms\Models\FormSubmission;
use Atrium\Forms\Tests\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

/**
 * The finer points of sending a form: what is kept of it, how often it may
 * be sent, the anti-spam clock, and what comes back when it is refused.
 */
final class SubmitDetailsTest extends TestCase
{
    public function test_a_mail_server_that_is_down_loses_nothing(): void
    {
        Exceptions::fake();
        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('Mail server is down'));
        $this->contactForm();

        $this->from(url('/contacts'))
            ->post('/forms/contact', $this->submission())
            ->assertRedirect(url('/contacts').'#form-contact')
            ->assertSessionHas('atrium-forms.sent', 'contact');

        $this->assertSame(1, FormSubmission::query()->count());
        Exceptions::assertReported(fn (RuntimeException $exception): bool => $exception->getMessage() === 'Mail server is down');
    }

    public function test_a_sent_form_reports_nothing(): void
    {
        Exceptions::fake();
        Mail::fake();
        $this->contactForm();

        $this->post('/forms/contact', $this->submission())->assertSessionHas('atrium-forms.sent', 'contact');

        Exceptions::assertNothingReported();
    }

    public function test_the_values_are_kept_trimmed_and_an_empty_one_as_nothing(): void
    {
        Mail::fake();
        $this->contactForm();

        // Straight to the controller: the web middleware would trim first.
        $response = $this->callController($this->submission(['name' => '  Ann  ', 'message' => null, 'consent' => 'yes']));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $data = FormSubmission::query()->sole()->data;
        $this->assertSame(['name' => 'name', 'type' => 'text', 'label' => 'Your name', 'value' => 'Ann'], $data[0]);
        $this->assertNull($data[3]['value']);
        $this->assertTrue($data[4]['value']);
    }

    public function test_an_error_names_the_field_by_its_label_in_lower_case(): void
    {
        $form = $this->contactForm();
        FormField::query()->where('form_id', $form->id)->where('name', 'name')->update(['label' => ['en' => 'Ваше ИМЯ']]);

        $this->postJson('/forms/contact', $this->submission(['name' => '']))
            ->assertUnprocessable()
            ->assertJsonPath('errors', ['fields.name' => ['The ваше имя field is required.']]);
    }

    /**
     * @return array<string,array{0:int,1:int}>
     */
    public static function pageAddresses(): array
    {
        return [
            'as long as allowed: kept whole' => [2000, 2000],
            'one too long: cut' => [2001, 2000],
            'short' => [30, 30],
        ];
    }

    #[DataProvider('pageAddresses')]
    public function test_the_page_address_is_kept_up_to_two_thousand_characters(int $length, int $kept): void
    {
        Mail::fake();
        $this->contactForm();
        $base = 'https://shop.test/';
        $address = $base.str_repeat('a', $length - strlen($base));

        $this->withHeaders(['referer' => $address])->post('/forms/contact', $this->submission())->assertSessionHasNoErrors();

        $this->assertSame(substr($address, 0, $kept), FormSubmission::query()->sole()->page_url);
    }

    /**
     * @return array<string,array{0:int,1:int}>
     */
    public static function browsers(): array
    {
        return [
            'as long as allowed: kept whole' => [500, 500],
            'one too long: cut' => [501, 500],
            'short' => [10, 10],
        ];
    }

    #[DataProvider('browsers')]
    public function test_the_browser_name_is_kept_up_to_five_hundred_characters(int $length, int $kept): void
    {
        Mail::fake();
        $this->contactForm();
        $agent = str_repeat('b', $length);

        $this->withHeaders(['User-Agent' => $agent])->post('/forms/contact', $this->submission())->assertSessionHasNoErrors();

        $submission = FormSubmission::query()->sole();
        $this->assertSame(substr($agent, 0, $kept), $submission->user_agent);
        $this->assertSame('127.0.0.1', $submission->ip);
    }

    public function test_a_refused_form_gets_back_what_was_typed_without_its_time_and_captcha(): void
    {
        $this->contactForm();
        $captcha = array_fill_keys(CaptchaManager::INPUTS, 'x');

        // An empty list in the trap is not "filled": the form is checked, and refused.
        $this->from(url('/contacts'))
            ->post('/forms/contact', [...$this->submission(['email' => 'nope']), 'website' => [], ...$captcha])
            ->assertRedirect(url('/contacts').'#form-contact')
            ->assertSessionHasErrorsIn('form-contact', ['fields.email'])
            ->assertSessionHasInput('fields.email', 'nope')
            ->assertSessionHasInput('_form', 'contact')
            ->assertSessionMissing('_old_input._t');

        // The trap comes back as it was sent: its value is no secret.
        $this->assertSame([], session('_old_input.website'));

        foreach (CaptchaManager::INPUTS as $input) {
            $this->assertNull(session('_old_input.'.$input), $input);
        }
    }

    public function test_what_was_typed_comes_back_to_the_form_that_was_sent_only(): void
    {
        $this->contactForm();

        $this->from(url('/contacts'))->post('/forms/contact', [...$this->submission(['email' => 'nope']), '_form' => 'other']);

        $this->get('/contacts')->assertSee('aria-invalid="true"', false)->assertDontSee('value="nope"', false);
    }

    public function test_the_form_is_drawn_outside_a_request_with_a_session(): void
    {
        $this->contactForm(['button_label' => ['en' => 'Go'], 'success_message' => ['en' => 'Thanks!']]);

        $html = Blade::render('<x-atrium-forms::form code="contact" />');

        $this->assertStringContainsString('id="form-contact"', $html);
        $this->assertStringContainsString('Go', $html);
        $this->assertStringNotContainsString('Thanks!', $html);
        $this->assertStringNotContainsString('aria-invalid="true"', $html);
    }

    public function test_a_missing_form_draws_nothing(): void
    {
        $this->contactForm();

        $this->assertSame('', trim(Blade::render('<x-atrium-forms::form code="nothing" />')));
    }

    /**
     * @return array<string,array{0:int,1:bool}>
     */
    public static function clock(): array
    {
        return [
            'drawn three seconds ago: a person' => [3, true],
            'drawn two seconds ago: a bot' => [2, false],
            'drawn a minute ago' => [60, true],
            'drawn now' => [0, false],
        ];
    }

    #[DataProvider('clock')]
    public function test_a_form_must_take_three_seconds_to_fill_in(int $secondsAgo, bool $taken): void
    {
        Mail::fake();
        $config = config('atrium-forms');
        unset($config['min_seconds']);
        config(['atrium-forms' => $config]);
        $this->contactForm();

        $this->atTheStartOfASecond();
        $response = $this->post('/forms/contact', [...$this->submission(), '_t' => Crypt::encryptString((string) (time() - $secondsAgo))]);

        $taken
            ? $response->assertSessionHas('atrium-forms.sent', 'contact')
            : $response->assertSessionHasErrorsIn('form-contact', ['form' => 'The form was sent too quickly. Please check it and send it again.']);
        $this->assertSame($taken ? 1 : 0, FormSubmission::query()->count());
    }

    public function test_a_site_may_ask_for_more_time(): void
    {
        Mail::fake();
        config(['atrium-forms.min_seconds' => 10]);
        $this->contactForm();

        $this->atTheStartOfASecond();
        $this->post('/forms/contact', [...$this->submission(), '_t' => Crypt::encryptString((string) (time() - 9))])
            ->assertSessionHasErrorsIn('form-contact', 'form');
        $this->atTheStartOfASecond();
        $this->post('/forms/contact', [...$this->submission(), '_t' => Crypt::encryptString((string) (time() - 10))])
            ->assertSessionHas('atrium-forms.sent', 'contact');
    }

    public function test_a_wait_the_config_does_not_make_a_number_of_is_no_wait(): void
    {
        Mail::fake();
        config(['atrium-forms.min_seconds' => 'off']);
        $this->contactForm();

        $this->post('/forms/contact', [...$this->submission(), '_t' => Crypt::encryptString((string) time())])
            ->assertSessionHas('atrium-forms.sent', 'contact');
    }

    public function test_a_signed_time_that_is_not_a_number_counts_as_long_ago(): void
    {
        Mail::fake();
        $this->contactForm();

        $this->post('/forms/contact', [...$this->submission(), '_t' => Crypt::encryptString('not a time')])
            ->assertSessionHas('atrium-forms.sent', 'contact');
    }

    /**
     * @return array<string,array{0:mixed,1:int}>
     */
    public static function limits(): array
    {
        return [
            'not set: five a minute' => [null, 5],
            'zero: still one' => [0, 1],
            'negative: still one' => [-3, 1],
            'one' => [1, 1],
            'three' => [3, 3],
        ];
    }

    #[DataProvider('limits')]
    public function test_a_form_is_taken_so_many_times_a_minute(mixed $perMinute, int $allowed): void
    {
        $config = config('atrium-forms');
        unset($config['per_minute']);
        config(['atrium-forms' => $perMinute === null ? $config : [...$config, 'per_minute' => $perMinute]]);
        $this->contactForm();

        for ($i = 0; $i < $allowed; $i++) {
            $this->postJson('/forms/contact', $this->submission())->assertOk();
        }

        $this->postJson('/forms/contact', $this->submission())
            ->assertTooManyRequests()
            ->assertExactJson(['message' => 'Too many attempts. Please try again in a minute.']);
        $this->assertSame($allowed, FormSubmission::query()->count());
    }

    public function test_the_limit_counts_one_form_from_one_address(): void
    {
        config(['atrium-forms.per_minute' => 1]);
        $this->contactForm();
        $other = Form::query()->create(['name' => 'Call me', 'code' => 'call']);

        $this->postJson('/forms/contact', $this->submission())->assertOk();
        $this->assertSame(1, RateLimiter::attempts(md5('atrium-forms127.0.0.1|contact')));

        // Another form, and the same form from another address, are counted apart.
        $this->postJson('/forms/call', ['_t' => Crypt::encryptString((string) (time() - 10))])->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])->postJson('/forms/contact', $this->submission())->assertOk();
        $this->assertSame(1, RateLimiter::attempts(md5('atrium-forms10.0.0.2|contact')));

        $this->postJson('/forms/contact', $this->submission())->assertTooManyRequests();
        $this->assertSame(3, FormSubmission::query()->count());
        $this->assertSame(1, $other->fresh()?->submissions_count);
    }

    public function test_a_browser_over_the_limit_goes_back_to_the_form(): void
    {
        config(['atrium-forms.per_minute' => 1]);
        $this->contactForm();
        $this->post('/forms/contact', $this->submission());

        $this->from(url('/contacts'))
            ->post('/forms/contact', $this->submission(['name' => 'Typed name']))
            ->assertRedirect(url('/contacts').'#form-contact')
            ->assertSessionHasErrorsIn('form-contact', ['form' => 'Too many attempts. Please try again in a minute.'])
            ->assertSessionHasInput('fields.name', 'Typed name');
        $this->assertSame(1, FormSubmission::query()->count());
    }

    #[DefineEnvironment('sendToAnotherPrefix')]
    public function test_the_address_forms_are_sent_to_is_configured(): void
    {
        Mail::fake();
        $this->contactForm();

        $this->get('/contacts')->assertSee('action="'.url('/send/contact').'"', false);
        $this->post('/send/contact', $this->submission())->assertSessionHas('atrium-forms.sent', 'contact');
        $this->post('/forms/contact', $this->submission())->assertNotFound();
        $this->assertSame(1, FormSubmission::query()->count());
    }

    public function test_a_code_with_other_characters_is_not_a_form(): void
    {
        $this->contactForm();

        $this->post('/forms/con.tact', $this->submission())->assertNotFound();
        $this->post('/forms/'.rawurlencode('контакт'), $this->submission())->assertNotFound();
    }

    public function test_the_defaults_of_the_module(): void
    {
        $this->assertSame(
            ['prefix' => 'forms', 'honeypot' => 'website', 'min_seconds' => 3, 'captcha' => true, 'per_minute' => 5],
            config('atrium-forms'),
        );
        $this->assertSame('Atrium\\Forms\\View\\Components', Blade::getClassComponentNamespaces()['atrium-forms'] ?? null);
    }

    public function test_the_config_and_views_can_be_published(): void
    {
        $config = ServiceProvider::pathsToPublish(FormsServiceProvider::class, 'atrium-forms-config');
        $views = ServiceProvider::pathsToPublish(FormsServiceProvider::class, 'atrium-forms-views');

        $this->assertCount(1, $config);
        $this->assertSame(realpath(__DIR__.'/../../config/atrium-forms.php'), realpath((string) array_key_first($config)));
        $this->assertSame(config_path('atrium-forms.php'), $config[array_key_first($config)]);

        $this->assertCount(1, $views);
        $this->assertSame(realpath(__DIR__.'/../../resources/views'), realpath((string) array_key_first($views)));
        $this->assertSame(resource_path('views/vendor/atrium-forms'), $views[array_key_first($views)]);
    }

    protected function sendToAnotherPrefix($app): void
    {
        $app['config']->set('atrium-forms.prefix', '/send/');
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private function callController(array $data): mixed
    {
        $request = Request::create('/forms/contact', 'POST', $data);
        $route = (new Route('POST', 'forms/{code}', []))->bind($request);
        $route->setParameter('code', 'contact');
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request', $request);

        return app()->call([app(SubmitController::class), '__invoke'], ['request' => $request]);
    }

    /**
     * The spam guard reads PHP's own clock, which the test clock does not
     * freeze; starting at a fresh second keeps a boundary case from ticking
     * over into the next one mid-request.
     */
    private function atTheStartOfASecond(): void
    {
        usleep((int) ((1 - fmod(microtime(true), 1)) * 1_000_000) + 1_000);
    }
}
