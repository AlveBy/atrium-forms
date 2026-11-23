<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Admin\Fields\DisplayField;
use Atrium\Forms\Http\Controllers\ScriptController;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormField;
use Atrium\Forms\Models\FormSubmission;
use Atrium\Forms\Support\Snippets;
use Atrium\Forms\Tests\TestCase;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;

/**
 * What a developer copies from the form's page: it says this form's code and
 * fields, and what it shows really sends the form.
 */
final class SnippetsTest extends TestCase
{
    public function test_the_blade_tags_name_the_form(): void
    {
        $this->assertSame(
            "<x-atrium-forms::form code=\"contact\" />\n"
            ."<x-atrium-forms::form code=\"contact\" ajax />\n"
            ."<x-atrium-forms::form code=\"contact\" modal />\n"
            ."@atriumForm('contact', ['modal' => true])",
            (new Snippets($this->contactForm()))->blade(),
        );
    }

    public function test_the_markup_has_every_field_in_the_site_s_language_escaped(): void
    {
        $form = Form::query()->create(['name' => 'Call', 'code' => 'call', 'button_label' => ['en' => 'Call me & soon', 'de' => 'Ruf an']]);
        $fields = [
            ['name' => 'name', 'type' => 'text', 'label' => ['en' => 'Name <full>', 'de' => 'Name'], 'required' => true],
            ['name' => 'age', 'type' => 'number', 'label' => 'Age'],
            ['name' => 'when', 'type' => 'radio', 'label' => 'When', 'options' => ['en' => "Today\nTom & Jerry", 'de' => 'Heute'], 'required' => true],
            ['name' => 'topic', 'type' => 'select', 'label' => 'Topic', 'options' => "Order\nDelivery"],
            ['name' => 'note', 'type' => 'textarea', 'label' => 'Note', 'required' => true],
            ['name' => 'agree', 'type' => 'checkbox', 'label' => 'I agree'],
        ];

        foreach ($fields as $index => $field) {
            FormField::query()->create(['form_id' => $form->id, 'sort' => $index, ...$field]);
        }

        // The admin in German: the site, and so the snippet, in English.
        app()->setLocale('de');

        $this->assertSame(<<<'HTML'
            <form id="form-call" method="post" action="{{ route('atrium.forms.submit', 'call') }}">
                @csrf
                <input type="hidden" name="_t" value="{{ \Atrium\Forms\Support\SpamGuard::token() }}">
                <div hidden><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                <p role="status" data-atrium-sent></p>
                <p role="alert" data-atrium-alert></p>

                <div data-field="name">
                    <label for="form-call-name">Name &lt;full&gt;</label>
                    <input type="text" id="form-call-name" name="fields[name]" required>
                </div>

                <div data-field="age">
                    <label for="form-call-age">Age</label>
                    <input type="number" id="form-call-age" name="fields[age]">
                </div>

                <div data-field="when">
                    <fieldset><legend>When</legend>
                        <label><input type="radio" name="fields[when]" value="Today" required> Today</label>
                        <label><input type="radio" name="fields[when]" value="Tom &amp; Jerry" required> Tom &amp; Jerry</label>
                    </fieldset>
                </div>

                <div data-field="topic">
                    <label for="form-call-topic">Topic</label>
                    <select id="form-call-topic" name="fields[topic]">
                        <option value="">— Choose —</option>
                        <option>Order</option>
                        <option>Delivery</option>
                    </select>
                </div>

                <div data-field="note">
                    <label for="form-call-note">Note</label>
                    <textarea id="form-call-note" name="fields[note]" required></textarea>
                </div>

                <div data-field="agree">
                    <label><input type="checkbox" id="form-call-agree" name="fields[agree]" value="1"> I agree</label>
                </div>

                <button type="submit">Call me &amp; soon</button>
            </form>
            HTML, (new Snippets($form->load('fields')))->markup());
    }

    public function test_a_form_without_a_button_text_says_send_in_the_site_s_language(): void
    {
        app()->setLocale('de');

        $this->assertStringContainsString('<button type="submit">Send</button>', (new Snippets($this->contactForm()))->markup());
    }

    public function test_the_markup_drawn_by_blade_is_sent_and_stored(): void
    {
        Mail::fake();
        config(['atrium-forms.min_seconds' => 0]);
        $snippets = new Snippets($this->contactForm()->load('fields'));
        $this->startSession();

        $html = Blade::render($snippets->markup());
        $data = [];
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR);

        foreach ($document->getElementsByTagName('input') as $input) {
            assert($input instanceof DOMElement);
            $data[$input->getAttribute('name')] = $input->getAttribute('value');
        }

        $form = $document->getElementsByTagName('form')->item(0);
        assert($form instanceof DOMElement);
        $this->assertSame(url('/forms/contact'), $form->getAttribute('action'));
        $this->assertSame('', $data['website']);
        $this->assertNotSame('', $data['_token']);

        unset($data['_token']);
        $data['fields'] = ['name' => 'Ann', 'email' => 'ann@example.com', 'consent' => '1'];

        $this->postJson('/forms/contact', $data)->assertOk()->assertExactJson(['message' => 'Thank you! Your message has been sent.']);
        $this->assertSame(['Ann', 'ann@example.com', null, null, true], array_column(FormSubmission::query()->sole()->data, 'value'));
    }

    public function test_the_script_tag_asks_for_the_script_at_its_version(): void
    {
        $snippets = new Snippets($this->contactForm());

        $this->assertSame(
            '<script src="'.url('/forms/script.js?v='.ScriptController::version()).'" defer></script>',
            Blade::render($snippets->script()),
        );
    }

    public function test_the_fetch_example_sends_the_form_and_shows_the_answer(): void
    {
        $this->assertSame(<<<'JS'
            const form = document.getElementById('form-contact');

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const body = new FormData(form);
                const xsrf = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

                // A page kept in a cache has an old token: the cookie has the fresh one.
                if (xsrf) {
                    body.delete('_token');
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { Accept: 'application/json', ...(xsrf && { 'X-XSRF-TOKEN': decodeURIComponent(xsrf[1]) }) },
                    body,
                });
                const data = await response.json();
                const errors = Object.values(data.errors ?? {}).flat();

                form.querySelector('[data-atrium-sent]').textContent = response.ok ? data.message : '';
                form.querySelector('[data-atrium-alert]').textContent = response.ok ? '' : errors.join(' ') || data.message;

                if (response.ok) {
                    form.reset();
                }
            });
            JS, (new Snippets($this->contactForm()))->fetch());
    }

    public function test_the_answers_carry_this_form_s_messages(): void
    {
        $form = $this->contactForm(['success_message' => ['en' => 'We will call you.', 'de' => 'Wir rufen an.']]);
        app()->setLocale('de');

        $this->assertSame(<<<'TEXT'
            200 OK
            {
                "message": "We will call you."
            }

            422 Unprocessable Content
            {
                "message": "The your name field is required.",
                "errors": {
                    "fields.name": [
                        "The your name field is required."
                    ]
                }
            }

            429 Too Many Requests
            {
                "message": "Too many attempts. Please try again in a minute."
            }
            TEXT, (new Snippets($form->load('fields')))->json());
    }

    public function test_the_field_s_name_in_the_error_is_lowercased_in_any_alphabet(): void
    {
        $form = Form::query()->create(['name' => 'Call', 'code' => 'call']);
        FormField::query()->create(['form_id' => $form->id, 'name' => 'name', 'type' => 'text', 'label' => 'Ваше Имя', 'required' => true]);

        $json = (new Snippets($form->load('fields')))->json();

        $this->assertStringContainsString('"message": "The ваше имя field is required."', $json);
        $this->assertStringNotContainsString('Ваше Имя', $json);
    }

    public function test_a_form_without_fields_answers_with_the_form_s_error(): void
    {
        $json = (new Snippets(Form::query()->create(['name' => 'Empty', 'code' => 'empty'])))->json();

        $this->assertStringContainsString("200 OK\n{\n    \"message\": \"Thank you! Your message has been sent.\"\n}", $json);
        $this->assertStringContainsString('"form": [', $json);
        $this->assertStringContainsString('The form was sent too quickly.', $json);
    }

    public function test_with_a_captcha_on_the_snippets_show_it_and_put_the_new_one_in(): void
    {
        config(['atrium.captcha.driver' => 'image']);
        $snippets = new Snippets($this->contactForm()->load('fields'));

        // The captcha comes after the form's own lines and fields, which stay.
        $this->assertStringStartsWith('<form id="form-contact"', $snippets->markup());
        $this->assertStringContainsString('    <div data-field="consent">', $snippets->markup());
        $this->assertStringContainsString("    </div>\n\n    <x-atrium::captcha data-error-for=\"captcha\" />\n\n    <button", $snippets->markup());
        $this->assertStringEndsWith(<<<'JS'
                    form.reset();
                }

                // Every answer uses the captcha up and brings a new one.
                form.querySelector('.atrium-captcha').innerHTML = data.captcha;
            });
            JS, $snippets->fetch());
        $this->assertSame(2, substr_count($snippets->json(), '"captcha": "…"'));
    }

    public function test_a_captcha_kept_off_the_forms_is_not_in_the_snippets(): void
    {
        config(['atrium.captcha.driver' => 'image', 'atrium-forms.captcha' => false]);
        $snippets = new Snippets($this->contactForm()->load('fields'));

        $this->assertStringNotContainsString('captcha', $snippets->markup().$snippets->fetch().$snippets->json());
    }

    public function test_the_form_s_page_in_the_admin_shows_the_snippets(): void
    {
        $form = $this->contactForm();

        $this->actingAs($this->admin(), 'admin')
            ->get("/admin/forms/{$form->id}/edit")
            ->assertOk()
            ->assertInertia(function ($page): void {
                $field = collect($page->toArray()['props']['fields'])->firstWhere('name', 'snippets');
                $this->assertSame('On a site', $field['label']);
            });

        $html = $this->snippetsField()->render($form);

        $this->assertStringContainsString('<pre tabindex="0"', $html);
        $this->assertStringContainsString('data-snippet="fetch"><code>const form = document.getElementById(&#039;form-contact&#039;);', $html);
        $this->assertStringContainsString('&lt;x-atrium-forms::form code=&quot;contact&quot; /&gt;', $html);
        foreach (['blade', 'markup', 'markup_note', 'script_note', 'fetch', 'fetch_note', 'json', 'json_note'] as $key) {
            $this->assertStringContainsString(e(__("atrium-forms::forms.snippets.{$key}")), $html);
        }
        // Another record has no snippets.
        $this->assertSame('', $this->snippetsField()->render(new FormField));
    }

    private function snippetsField(): DisplayField
    {
        $field = collect(Form::getAdministrationFields())->first(fn ($field): bool => $field->getName() === 'snippets');
        assert($field instanceof DisplayField);

        return $field;
    }
}
