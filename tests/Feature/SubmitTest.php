<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Admin\Models\Language;
use Atrium\Admin\Models\Site;
use Atrium\Forms\Mail\SubmissionMail;
use Atrium\Forms\Models\FormField;
use Atrium\Forms\Models\FormSubmission;
use Atrium\Forms\Tests\TestCase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

final class SubmitTest extends TestCase
{
    public function test_the_component_draws_the_form_with_its_traps(): void
    {
        $this->contactForm();

        $this->get('/contacts')
            ->assertOk()
            ->assertSee('id="form-contact"', false)
            ->assertSee('action="'.url('/forms/contact').'"', false)
            ->assertSee('name="fields[email]"', false)
            ->assertSee('type="email"', false)
            ->assertSee('<option value="Delivery"', false)
            ->assertSee('name="website"', false)
            ->assertSee('name="_t"', false)
            ->assertSee('Send');
    }

    public function test_a_form_that_is_off_is_not_drawn_and_not_taken(): void
    {
        $this->contactForm(['active' => false]);

        $this->get('/contacts')->assertOk()->assertDontSee('<form', false);
        $this->post('/forms/contact', $this->submission())->assertNotFound();
    }

    public function test_a_submission_is_stored_and_mailed(): void
    {
        Mail::fake();
        $this->contactForm(['success_message' => ['en' => 'We will call you.']]);

        $this->from(url('/contacts'))
            ->post('/forms/contact', $this->submission())
            ->assertRedirect(url('/contacts').'#form-contact')
            ->assertSessionHas('atrium-forms.sent', 'contact');

        $submission = FormSubmission::query()->sole();
        $this->assertSame('Contact us', $submission->form_name);
        $this->assertSame(url('/contacts'), $submission->page_url);
        $this->assertSame('ann@example.com', $submission->data[1]['value']);
        $this->assertTrue($submission->data[4]['value']);

        Mail::assertSent(SubmissionMail::class, function (SubmissionMail $mail): bool {
            $mail->assertSeeInHtml('Where is my parcel?');

            return $mail->hasTo('sales@example.com')
                && $mail->hasTo('help@example.com')
                && $mail->hasReplyTo('ann@example.com')
                && $mail->hasSubject('New submission: Contact us');
        });

        // Back on the page, the visitor sees the form's own message.
        $this->get('/contacts')->assertSee('We will call you.');
    }

    public function test_without_recipients_nothing_is_mailed(): void
    {
        Mail::fake();
        $this->contactForm(['recipients' => null]);

        $this->post('/forms/contact', $this->submission())->assertRedirect();

        $this->assertSame(1, FormSubmission::query()->count());
        Mail::assertNothingSent();
    }

    public function test_the_form_rules_come_from_its_fields(): void
    {
        Mail::fake();
        $this->contactForm();

        $this->from('/contacts')
            ->post('/forms/contact', $this->submission(['name' => '', 'email' => 'nope', 'topic' => 'Anything', 'consent' => null]))
            ->assertRedirect(url('/contacts').'#form-contact')
            ->assertSessionHasErrorsIn('form-contact', ['fields.name', 'fields.email', 'fields.topic', 'fields.consent']);

        $this->assertSame(0, FormSubmission::query()->count());
        Mail::assertNothingSent();

        // The errors and what was typed come back to this form only.
        $this->get('/contacts')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('value="nope"', false);
    }

    public function test_a_filled_honeypot_looks_sent_but_is_dropped(): void
    {
        Mail::fake();
        $this->contactForm();

        $this->post('/forms/contact', [...$this->submission(), 'website' => 'http://spam.example'])
            ->assertRedirect()
            ->assertSessionHas('atrium-forms.sent', 'contact');

        $this->assertSame(0, FormSubmission::query()->count());
        Mail::assertNothingSent();
    }

    public function test_a_form_sent_too_fast_or_without_its_time_is_refused(): void
    {
        Mail::fake();
        $this->contactForm();

        $this->post('/forms/contact', [...$this->submission(), '_t' => Crypt::encryptString((string) time())])
            ->assertSessionHasErrorsIn('form-contact', 'form');

        $this->post('/forms/contact', [...$this->submission(), '_t' => 'forged'])
            ->assertSessionHasErrorsIn('form-contact', 'form');

        $data = $this->submission();
        unset($data['_t']);
        $this->post('/forms/contact', $data)->assertSessionHasErrorsIn('form-contact', 'form');

        $this->assertSame(0, FormSubmission::query()->count());
        Mail::assertNothingSent();
    }

    public function test_json_clients_get_json(): void
    {
        $this->contactForm();

        $this->postJson('/forms/contact', $this->submission())
            ->assertOk()
            ->assertJson(['message' => 'Thank you! Your message has been sent.']);

        $this->postJson('/forms/contact', $this->submission(['email' => 'nope']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('fields.email');
    }

    public function test_submissions_are_rate_limited(): void
    {
        config(['atrium-forms.per_minute' => 2]);
        $this->contactForm();

        $this->postJson('/forms/contact', $this->submission())->assertOk();
        $this->postJson('/forms/contact', $this->submission())->assertOk();
        $this->postJson('/forms/contact', $this->submission())->assertTooManyRequests();

        $this->post('/forms/contact', $this->submission())
            ->assertSessionHasErrorsIn('form-contact', 'form');

        $this->assertSame(2, FormSubmission::query()->count());
    }

    public function test_labels_and_options_follow_the_page_language(): void
    {
        Mail::fake();
        Language::query()->create(['code' => 'de', 'name' => 'Deutsch']);
        $form = $this->contactForm(['button_label' => ['en' => 'Send', 'de' => 'Senden']]);
        FormField::query()->where('name', 'topic')->sole()->update([
            'label' => ['en' => 'Topic', 'de' => 'Thema'],
            'options' => ['en' => "Order\nDelivery", 'de' => "Bestellung\nLieferung"],
        ]);

        $this->get('/de/contacts')
            ->assertOk()
            ->assertSee('action="'.url('/de/forms/contact').'"', false)
            ->assertSee('Thema')
            ->assertSee('<option value="Lieferung"', false)
            ->assertSee('Senden');

        $this->post('/de/forms/contact', $this->submission(['topic' => 'Lieferung']))->assertSessionHasNoErrors();

        // The staff read the labels in the default language.
        $submission = FormSubmission::query()->sole();
        $this->assertSame('de', $submission->locale);
        $this->assertSame(['name' => 'topic', 'type' => 'select', 'label' => 'Topic', 'value' => 'Lieferung'], $submission->data[2]);
        $this->assertSame($form->id, $submission->form_id);
    }

    public function test_a_form_belongs_to_its_site(): void
    {
        $this->contactForm();
        Site::query()->create(['code' => 'second', 'name' => 'Second', 'folder' => 'site2']);

        $this->get('/site2/contacts')->assertOk()->assertDontSee('<form', false);
        $this->post('/site2/forms/contact', $this->submission())->assertNotFound();
        $this->assertSame(0, FormSubmission::query()->withoutGlobalScopes()->count());
    }
}
