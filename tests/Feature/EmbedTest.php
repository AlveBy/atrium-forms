<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Admin\Models\Language;
use Atrium\Forms\Http\Controllers\ScriptController;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormSubmission;
use Atrium\Forms\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A form put on a page: sent by the script without reloading, opened in a
 * window, on a page of its own, in one line of Blade.
 */
final class EmbedTest extends TestCase
{
    private const SCRIPT = __DIR__.'/../../resources/js/atrium-forms.js';

    public function test_the_script_is_served_as_javascript_and_kept_for_good_at_its_version(): void
    {
        $version = substr((string) hash_file('sha256', self::SCRIPT), 0, 12);
        $this->assertSame($version, ScriptController::version());
        $this->assertSame(realpath(self::SCRIPT), realpath(ScriptController::path()));

        $response = $this->get('/forms/script.js?v='.$version)->assertOk();

        $this->assertSame(file_get_contents(self::SCRIPT), $response->getContent());
        $this->assertSame('text/javascript; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('immutable, max-age=31536000, public', $response->headers->get('Cache-Control'));
        // One file for every visitor: no session, no cookies.
        $this->assertSame([], $response->headers->getCookies());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function otherVersions(): array
    {
        return [
            'no version' => ['/forms/script.js'],
            'an old version' => ['/forms/script.js?v=0123456789ab'],
            'a version as a list' => ['/forms/script.js?v[]=x'],
        ];
    }

    #[DataProvider('otherVersions')]
    public function test_the_script_at_another_address_is_kept_for_an_hour(string $url): void
    {
        $this->get($url)->assertOk()->assertHeader('Cache-Control', 'max-age=3600, public');
    }

    public function test_the_script_follows_the_prefix_and_is_not_localized(): void
    {
        $this->assertSame(url('/forms/script.js?v=1'), route('atrium.forms.script', ['v' => 1]));
        Language::query()->create(['code' => 'de', 'name' => 'Deutsch']);

        $this->get('/de/forms/script.js')->assertNotFound();
    }

    public function test_a_form_has_a_page_of_its_own_outside_the_search_engines(): void
    {
        $this->contactForm();

        $html = (string) $this->get('/forms/contact')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="en">', $html);
        $this->assertStringContainsString('<meta name="viewport" content="width=device-width, initial-scale=1">', $html);
        $this->assertStringContainsString('<title>Contact us</title>', $html);
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $html);
        $this->assertStringContainsString('<h1>Contact us</h1>', $html);
        $this->assertStringContainsString('data-atrium-form', $html);
        $this->assertStringContainsString('action="'.url('/forms/contact').'"', $html);
    }

    public function test_the_page_of_a_form_is_drawn_in_the_site_theme_when_there_is_one(): void
    {
        $this->contactForm();
        $theme = sys_get_temp_dir().'/atrium-forms-theme-'.getmypid();
        File::ensureDirectoryExists($theme);
        File::put("{$theme}/layout.blade.php", '<theme title="{{ $metaTitle }}">@stack(\'head\')@yield(\'content\')</theme>');
        View::addNamespace('atrium-theme', $theme);

        try {
            $html = (string) $this->get('/forms/contact')->assertOk()->getContent();
        } finally {
            File::deleteDirectory($theme);
        }

        $this->assertMatchesRegularExpression('#^<theme title="Contact us">\s*<meta name="robots" content="noindex, follow">#', $html);
        $this->assertStringContainsString('<h1>Contact us</h1>', $html);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $html);
    }

    public function test_the_page_of_a_form_follows_the_language(): void
    {
        Language::query()->create(['code' => 'de', 'name' => 'Deutsch']);
        $this->contactForm();

        $this->get('/de/forms/contact')
            ->assertOk()
            ->assertSee('<html lang="de">', false)
            ->assertSee('action="'.url('/de/forms/contact').'"', false);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function missingPages(): array
    {
        return [
            'a form that is off' => ['/forms/off'],
            'no such form' => ['/forms/nothing'],
            'a code with a dot' => ['/forms/contact.php'],
        ];
    }

    #[DataProvider('missingPages')]
    public function test_there_is_no_page_for_a_form_that_is_not_there(string $url): void
    {
        $this->contactForm();
        Form::query()->create(['name' => 'Off', 'code' => 'off', 'active' => false]);

        $this->get($url)->assertNotFound();
    }

    public function test_the_page_of_a_form_is_not_held_to_the_rate_limit_of_sending(): void
    {
        config(['atrium-forms.per_minute' => 1]);
        $this->contactForm();

        foreach (range(1, 3) as $ignored) {
            $this->get('/forms/contact')->assertOk();
        }

        $this->postJson('/forms/contact', $this->submission())->assertOk();
        $this->postJson('/forms/contact', $this->submission())->assertTooManyRequests();
    }

    public function test_a_plain_form_reloads_the_page_as_before(): void
    {
        $this->contactForm();

        $html = Blade::render('<x-atrium-forms::form code="contact" />');

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('data-atrium-form', $html);
        $this->assertStringNotContainsString('novalidate', $html);
        $this->assertStringNotContainsString('<dialog', $html);
        // The places the script writes to are there all the same, empty.
        $this->assertStringContainsString('<p class="atrium-form__sent" role="status" tabindex="-1" data-atrium-sent></p>', $html);
        $this->assertStringContainsString('<p class="atrium-form__error" role="alert" tabindex="-1" data-atrium-alert></p>', $html);
    }

    public function test_an_ajax_form_brings_the_script_once_and_lets_it_check_the_fields(): void
    {
        $this->contactForm();
        Form::query()->create(['name' => 'Call me', 'code' => 'call']);

        $html = Blade::render('<x-atrium-forms::form code="contact" ajax /><x-atrium-forms::form code="call" ajax />');
        $script = '<script src="'.url('/forms/script.js?v='.ScriptController::version()).'" defer></script>';

        $this->assertSame(1, substr_count($html, $script));
        $this->assertSame(1, substr_count($html, '<script'));
        $this->assertSame(2, substr_count($html, ' data-atrium-form data-atrium-failed="The form could not be sent. Please check your connection and try again." novalidate'));
        $this->assertStringNotContainsString('<dialog', $html);
    }

    public function test_a_form_in_a_window_opens_from_a_button_that_leads_to_its_page(): void
    {
        $this->contactForm();

        $html = Blade::render('<x-atrium-forms::form code="contact" modal />');

        $this->assertStringContainsString('<a href="'.url('/forms/contact').'" class="atrium-form-open" data-atrium-form-open="contact" aria-haspopup="dialog" aria-controls="form-contact-dialog" aria-expanded="false">Contact us</a>', $html);
        $this->assertStringContainsString('<dialog id="form-contact-dialog" class="atrium-form-dialog" data-atrium-dialog="contact" aria-labelledby="form-contact-title">', $html);
        $this->assertStringContainsString('<h2 id="form-contact-title">Contact us</h2>', $html);
        $this->assertStringContainsString('<button type="button" class="atrium-form-dialog__close" data-atrium-form-close aria-label="Close"><span aria-hidden="true">&times;</span></button>', $html);
        // A window is sent by the script: reloading would close it.
        $this->assertStringContainsString('<script src="'.url('/forms/script.js'), $html);
        $this->assertStringContainsString('data-atrium-form ', $html);
        $this->assertMatchesRegularExpression('#<dialog [^>]+>.*<form id="form-contact".*</form>\s*</dialog>#s', $html);
    }

    public function test_the_window_takes_the_button_text_and_heading_it_is_given(): void
    {
        $this->contactForm();

        $html = Blade::render('<x-atrium-forms::form code="contact" modal trigger="Write to us" title="We answer within a day" />');

        $this->assertStringContainsString('aria-expanded="false">Write to us</a>', $html);
        $this->assertStringContainsString('<h2 id="form-contact-title">We answer within a day</h2>', $html);
    }

    public function test_the_window_speaks_the_page_language(): void
    {
        Language::query()->create(['code' => 'de', 'name' => 'Deutsch']);
        $this->contactForm();

        app()->setLocale('de');
        $html = Blade::render('<x-atrium-forms::form code="contact" modal />');

        $this->assertStringContainsString('aria-label="Schließen"', $html);
        $this->assertStringContainsString('<a href="'.url('/de/forms/contact').'"', $html);
        $this->assertStringContainsString('data-atrium-failed="Das Formular konnte nicht gesendet werden.', $html);
    }

    public function test_the_directive_draws_the_component_in_one_line(): void
    {
        $this->contactForm();

        // The same but for the signed time of drawing, new every time.
        $time = static fn (string $html): string => (string) preg_replace('/name="_t" value="[^"]+"/', 'name="_t"', $html);

        $this->assertSame(
            $time(Blade::render('<x-atrium-forms::form code="contact" />')),
            $time(Blade::render("@atriumForm('contact')")),
        );
        $this->assertSame('', Blade::render("@atriumForm('nothing')"));
        // Compiled afresh, not taken from the views compiled before.
        $this->assertSame(
            "<?php echo \\Atrium\\Forms\\View\\Components\\Form::inline('contact', ['ajax' => true]); ?>",
            Blade::compileString("@atriumForm('contact', ['ajax' => true])"),
        );
    }

    public function test_the_directive_takes_the_options_and_gives_the_rest_to_the_form(): void
    {
        $this->contactForm();

        $html = Blade::render("@atriumForm('contact', ['modal' => 1, 'trigger' => 'Write to us', 'title' => 'Write', 'class' => 'mine', 'data-place' => 'footer'])");

        $this->assertStringContainsString('aria-expanded="false">Write to us</a>', $html);
        $this->assertStringContainsString('<h2 id="form-contact-title">Write</h2>', $html);
        $this->assertSame(1, preg_match('/<form id="form-contact"[^>]*>/', $html, $form));
        $this->assertStringContainsString('class="atrium-form mine"', $form[0]);
        $this->assertStringContainsString('data-place="footer"', $form[0]);
        $this->assertStringContainsString('data-atrium-form', $form[0]);

        foreach (['modal=', 'trigger=', 'title=', 'ajax='] as $option) {
            $this->assertStringNotContainsString($option, $form[0], "{$option} is an option, not an attribute");
        }
    }

    public function test_the_directive_sends_by_the_script_when_asked(): void
    {
        $this->contactForm();

        $ajax = Blade::render("@atriumForm('contact', ['ajax' => 1])");
        $this->assertStringContainsString('data-atrium-form', $ajax);
        $this->assertStringNotContainsString('<dialog', $ajax);
        $this->assertStringNotContainsString('ajax=', $ajax);

        $plain = Blade::render("@atriumForm('contact', ['ajax' => false, 'trigger' => 5, 'title' => ['x']])");
        $this->assertStringNotContainsString('data-atrium-form', $plain);
    }

    public function test_texts_that_are_not_text_are_left_for_the_form_name(): void
    {
        $this->contactForm();

        $html = Blade::render("@atriumForm('contact', ['modal' => true, 'trigger' => 5, 'title' => ['x']])");

        $this->assertStringContainsString('aria-expanded="false">Contact us</a>', $html);
        $this->assertStringContainsString('<h2 id="form-contact-title">Contact us</h2>', $html);
    }

    public function test_messages_of_a_plain_form_land_in_the_same_places(): void
    {
        $this->contactForm();

        $this->from('/contacts')->post('/forms/contact', $this->submission());
        $this->get('/contacts')->assertSee('<p class="atrium-form__sent" role="status" tabindex="-1" data-atrium-sent>Thank you! Your message has been sent.</p>', false);

        $this->from('/contacts')->post('/forms/contact', ['_t' => 'forged'] + $this->submission());
        $this->get('/contacts')->assertSee('<p class="atrium-form__error" role="alert" tabindex="-1" data-atrium-alert>The form was sent too quickly.', false);

        $this->from('/contacts')->post('/forms/contact', $this->submission(['email' => 'nope']));
        $this->get('/contacts')->assertSee('id="form-contact-email-error" data-atrium-error>', false);
    }

    public function test_a_json_answer_brings_a_new_captcha(): void
    {
        Mail::fake();
        config(['atrium.captcha.driver' => 'image', 'atrium-forms.per_minute' => 20]);
        $this->contactForm();

        $this->get('/contacts')->assertSee('data-error-for="captcha"', false);

        $refused = $this->postJson('/forms/contact', $this->submission(['email' => 'nope']) + ['captcha' => '1', 'captcha_token' => 'forged'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fields.email', 'captcha']);

        $this->assertStringContainsString('name="captcha_token"', $refused->json('captcha'));
        $this->assertStringStartsWith('The e-mail field must be a valid email address.', $refused->json('message'));

        $this->assertSame(0, FormSubmission::query()->count());
    }

    public function test_a_json_answer_without_a_captcha_brings_none(): void
    {
        $this->contactForm();

        $this->postJson('/forms/contact', $this->submission())->assertOk()->assertExactJson(['message' => 'Thank you! Your message has been sent.']);
        $this->postJson('/forms/contact', $this->submission(['email' => 'nope']))->assertUnprocessable()->assertJsonMissingPath('captcha');

        // A captcha the site has but keeps off its forms.
        config(['atrium.captcha.driver' => 'image', 'atrium-forms.captcha' => false]);
        $this->postJson('/forms/contact', $this->submission())->assertOk()->assertExactJson(['message' => 'Thank you! Your message has been sent.']);
        $this->postJson('/forms/contact', ['_t' => 'forged'] + $this->submission())
            ->assertUnprocessable()
            ->assertExactJson(['message' => 'The form was sent too quickly. Please check it and send it again.', 'errors' => ['form' => ['The form was sent too quickly. Please check it and send it again.']]]);
    }

    public function test_a_sent_json_form_brings_a_new_captcha_for_the_next_one(): void
    {
        Mail::fake();
        config(['atrium.captcha.driver' => 'image']);
        $this->contactForm();
        $page = (string) $this->get('/contacts')->getContent();
        preg_match('/name="captcha_token" value="([^"]+)"/', $page, $token);
        $answer = (string) decrypt($token[1])['a'];

        $sent = $this->postJson('/forms/contact', $this->submission() + ['captcha_token' => $token[1], 'captcha' => $answer])->assertOk();

        $this->assertSame('Thank you! Your message has been sent.', $sent->json('message'));
        $this->assertStringContainsString('name="captcha_token"', $sent->json('captcha'));
        $this->assertStringNotContainsString($token[1], $sent->json('captcha'));
        $this->assertSame(1, FormSubmission::query()->count());
    }
}
