<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Admin\Fields\Field;
use Atrium\Forms\Mail\SubmissionMail;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormField;
use Atrium\Forms\Models\FormSubmission;
use Atrium\Forms\Rules\EmailList;
use Atrium\Forms\Rules\UniqueFieldName;
use Atrium\Forms\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Forms, their fields and their submissions as the panel and the mail see
 * them, and the two rules the panel checks.
 */
final class ModelsTest extends TestCase
{
    public function test_a_form_is_read_back_as_on_or_off(): void
    {
        $this->assertTrue(Form::query()->create(['name' => 'A', 'code' => 'a', 'active' => 1])->fresh()?->active);
        $this->assertFalse(Form::query()->create(['name' => 'B', 'code' => 'b', 'active' => 0])->fresh()?->active);
    }

    public function test_a_form_lists_its_own_submissions(): void
    {
        $contact = $this->contactForm();
        $other = Form::query()->create(['name' => 'Other', 'code' => 'other']);
        $mine = $this->submissionOf($contact);
        $this->submissionOf($other);

        $this->assertSame([$mine->id], $contact->submissions()->pluck('id')->all());
        $this->assertTrue($mine->form?->is($contact));
    }

    public function test_the_form_of_the_panel(): void
    {
        $fields = $this->byName(Form::getAdministrationFields());

        $this->assertSame(['active', 'name', 'code', 'recipients', 'button_label', 'success_message'], array_keys($fields));
        $this->assertTrue($fields['active']->getDefault());
        $this->assertContains('alpha_dash:ascii', $fields['code']->getValidationRules());
        $this->assertSame(
            ['id', 'name', 'code', 'active', 'submissions_count', 'updated_at'],
            array_map(fn ($column): string => $column->getName(), Form::getAdministrationListColumns()),
        );
    }

    /**
     * @return array<string,array{0:string,1:bool}>
     */
    public static function formCodes(): array
    {
        return [
            'letters, digits, dash and underscore' => ['call-back_2', true],
            'a hundred characters' => [str_repeat('a', 100), true],
            'too long' => [str_repeat('a', 101), false],
            'a space' => ['call back', false],
            'Cyrillic' => ['звонок', false],
            'a slash' => ['call/back', false],
        ];
    }

    #[DataProvider('formCodes')]
    public function test_the_code_of_a_form_goes_into_its_address(string $code, bool $valid): void
    {
        $response = $this->actingAs($this->admin(), 'admin')->post('/admin/forms', ['name' => 'Call', 'code' => $code, 'active' => true]);

        $valid ? $response->assertSessionHasNoErrors() : $response->assertSessionHasErrors('code');
        $this->assertSame($valid ? 1 : 0, Form::query()->count());
    }

    public function test_a_field_names_its_form(): void
    {
        $form = $this->contactForm();
        $field = FormField::query()->where('form_id', $form->id)->firstOrFail();

        $this->assertSame('Contact us', $field->getFormNameAttribute());
        $this->assertSame('Contact us', $field->toArray()['form_name']);
        $this->assertSame('', (new FormField)->form_name);
    }

    public function test_the_options_are_one_per_line_without_blanks_or_repeats(): void
    {
        $field = new FormField(['options' => ['en' => "  Order  \n\nDelivery\r\nOrder\n   \nReturn"]]);

        $this->assertSame(['Order', 'Delivery', 'Return'], $field->optionList());
        $this->assertSame([], (new FormField)->optionList());
    }

    public function test_an_option_with_the_letter_x_in_cyrillic_stays_whole(): void
    {
        // The second byte of "х" (D1 85) is NEL to a byte-wise \R.
        $field = new FormField(['options' => ['en' => "Сухари\rОрехи\r\nХлеб"]]);

        $this->assertSame(['Сухари', 'Орехи', 'Хлеб'], $field->optionList());
    }

    /**
     * @return array<string,array{0:string,1:bool,2:list<string>}>
     */
    public static function rulesByType(): array
    {
        return [
            'text' => ['text', false, ['nullable', 'string', 'max:1000']],
            'text, required' => ['text', true, ['required', 'string', 'max:1000']],
            'e-mail' => ['email', true, ['required', 'string', 'email', 'max:255']],
            'phone' => ['tel', false, ['nullable', 'string', 'max:50', 'regex:/^[0-9+()\-.\s]+$/']],
            'number' => ['number', false, ['nullable', 'numeric']],
            'date' => ['date', true, ['required', 'date']],
            'long text' => ['textarea', false, ['nullable', 'string', 'max:10000']],
            'list' => ['select', false, ['nullable', 'string', 'in:"Order","Delivery"']],
            'choice' => ['radio', true, ['required', 'string', 'in:"Order","Delivery"']],
            'consent' => ['checkbox', true, ['required', 'accepted']],
            'a tick' => ['checkbox', false, ['nullable', 'boolean']],
            'an unknown type: text' => ['colour', false, ['nullable', 'string', 'max:1000']],
        ];
    }

    /**
     * @param  list<string>  $expected
     */
    #[DataProvider('rulesByType')]
    public function test_each_type_has_its_rules(string $type, bool $required, array $expected): void
    {
        $field = new FormField(['type' => $type, 'required' => $required, 'options' => ['en' => "Order\nDelivery"]]);

        $this->assertSame($expected, array_map(fn ($rule): string => (string) $rule, $field->publicRules()));
    }

    /**
     * @return array<string,array{0:string,1:mixed,2:bool}>
     */
    public static function values(): array
    {
        return [
            'a phone number' => ['tel', '+375 (29) 123-45-67', true],
            'a phone with letters' => ['tel', 'call me', false],
            'a phone too long' => ['tel', str_repeat('1', 51), false],
            'a number' => ['number', '12.5', true],
            'not a number' => ['number', 'twelve', false],
            'a date' => ['date', '2026-11-17', true],
            'not a date' => ['date', '2026-13-45', false],
            'an e-mail' => ['email', 'ann@example.com', true],
            'not an e-mail' => ['email', 'ann', false],
            'a long text' => ['textarea', str_repeat('a', 10000), true],
            'a text too long' => ['textarea', str_repeat('a', 10001), false],
            'a short text too long' => ['text', str_repeat('a', 1001), false],
            'Unicode text' => ['text', 'Привет, 世界 👋', true],
            'an option' => ['select', 'Delivery', true],
            'not an option' => ['select', 'Anything', false],
            'a tick' => ['checkbox', '1', true],
            'not a tick' => ['checkbox', 'maybe', false],
            'nothing, not required' => ['text', null, true],
        ];
    }

    #[DataProvider('values')]
    public function test_a_visitor_value_is_checked_by_the_field_type(string $type, mixed $value, bool $valid): void
    {
        $field = new FormField(['type' => $type, 'required' => false, 'options' => ['en' => "Order\nDelivery"]]);

        $this->assertSame($valid, Validator::make(['value' => $value], ['value' => $field->publicRules()])->passes());
    }

    public function test_the_field_form_of_the_panel(): void
    {
        $fields = $this->byName(FormField::getAdministrationFields());

        $this->assertSame(['form_id', 'label', 'name', 'type', 'required', 'options', 'sort'], array_keys($fields));
        $this->assertSame(
            array_combine(FormField::TYPES, array_map(fn (string $type): string => __("atrium-forms::forms.types.{$type}"), FormField::TYPES)),
            array_column($fields['type']->toArray()['options'], 'label', 'value'),
        );
        $this->assertSame('E-mail', array_column($fields['type']->toArray()['options'], 'label', 'value')['email']);
        $this->assertSame('text', $fields['type']->getDefault());
        $this->assertSame(500, $fields['sort']->getDefault());
        $this->assertSame(
            ['id', 'form_name', 'label', 'name', 'type', 'required', 'sort'],
            array_map(fn ($column): string => $column->getName(), FormField::getAdministrationListColumns()),
        );
    }

    public function test_the_hooks_of_a_submission_are_public_api(): void
    {
        // Model::__call and __callStatic hide a protected method, so the
        // visibility is read as declared.
        foreach (['form', 'text', 'isCreatableInAdmin', 'isEditableInAdmin'] as $method) {
            $this->assertTrue((new \ReflectionMethod(FormSubmission::class, $method))->isPublic(), $method);
        }
    }

    public function test_a_submission_without_a_form_counts_nowhere(): void
    {
        $form = $this->contactForm();
        $this->submissionOf($form);

        $loose = $this->submissionOf(null);
        $this->assertSame(1, $form->fresh()?->submissions_count);

        $loose->delete();
        $this->assertSame(1, $form->fresh()?->submissions_count);
    }

    /**
     * @return array<string,array{0:int,1:int}>
     */
    public static function counters(): array
    {
        return [
            'one: none left' => [1, 0],
            'two: one left' => [2, 1],
            'already none: never below' => [0, 0],
        ];
    }

    #[DataProvider('counters')]
    public function test_deleting_a_submission_takes_one_off_the_count(int $count, int $left): void
    {
        $form = $this->contactForm();
        $submission = $this->submissionOf($form);
        Form::query()->whereKey($form->id)->toBase()->update(['submissions_count' => $count]);

        $submission->delete();

        $this->assertSame($left, $form->fresh()?->submissions_count);
    }

    public function test_the_content_and_the_preview_of_a_submission(): void
    {
        $submission = $this->submissionOf(null, [
            ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'value' => 'Ann'],
            ['name' => 'note', 'type' => 'text', 'label' => 'Note', 'value' => ''],
            ['name' => 'age', 'type' => 'number', 'label' => 'Age', 'value' => 30],
            ['name' => 'consent', 'type' => 'checkbox', 'label' => 'Agreed', 'value' => true],
            ['name' => 'news', 'type' => 'checkbox', 'label' => 'News', 'value' => false],
            ['name' => 'gone', 'type' => 'text', 'label' => 'Gone'],
        ]);

        $this->assertSame("Name: Ann\nNote: \nAge: 30\nAgreed: Yes\nNews: No\nGone: ", $submission->getContentAttribute());
        $this->assertSame('Ann · 30 · Yes · No', $submission->getPreviewAttribute());
        $this->assertSame('', (new FormSubmission)->getContentAttribute());
        $this->assertSame('', (new FormSubmission)->getPreviewAttribute());
    }

    /**
     * @return array<string,array{0:int,1:string}>
     */
    public static function previews(): array
    {
        return [
            'a hundred characters: whole' => [100, str_repeat('a', 100)],
            'one more: cut' => [101, str_repeat('a', 100).'...'],
        ];
    }

    #[DataProvider('previews')]
    public function test_the_preview_is_cut_after_a_hundred_characters(int $length, string $preview): void
    {
        $submission = $this->submissionOf(null, [['name' => 'message', 'type' => 'textarea', 'label' => 'Message', 'value' => str_repeat('a', $length)]]);

        $this->assertSame($preview, $submission->getPreviewAttribute());
    }

    public function test_a_submission_is_dated_when_it_comes(): void
    {
        $this->travelTo(Carbon::parse('2026-04-05 06:07:08'));

        $this->assertSame('2026-04-05 06:07:08', $this->submissionOf(null)->getSubmittedAtAttribute());
        $this->assertSame('', (new FormSubmission)->submitted_at);
    }

    /**
     * @return array<string,array{0:mixed,1:string}>
     */
    public static function texts(): array
    {
        return [
            'yes' => [true, 'Yes'],
            'no' => [false, 'No'],
            'a number' => [5, '5'],
            'a fraction' => [2.5, '2.5'],
            'a string' => ['Ann', 'Ann'],
            'nothing' => [null, ''],
            'a list' => [['a', 'b'], ''],
        ];
    }

    #[DataProvider('texts')]
    public function test_a_value_as_text(mixed $value, string $text): void
    {
        $this->assertSame($text, FormSubmission::text($value));
    }

    public function test_the_submission_in_the_panel_is_read_only(): void
    {
        $this->assertFalse(FormSubmission::isCreatableInAdmin());
        $this->assertFalse(FormSubmission::isEditableInAdmin());

        $fields = $this->byName(FormSubmission::getAdministrationFields());
        $this->assertSame(['form_name', 'submitted_at', 'content', 'page_url', 'locale', 'ip', 'user_agent'], array_keys($fields));
        $this->assertSame(10, $fields['content']->toArray()['rows']);
        $this->assertSame(
            ['id', 'submitted_at', 'form_name', 'preview'],
            array_map(fn ($column): string => $column->getName(), FormSubmission::getAdministrationListColumns()),
        );
    }

    public function test_reply_goes_to_the_first_valid_email_field(): void
    {
        $mail = new SubmissionMail($this->submissionOf(null, [
            // An address typed into a text field is not where replies go.
            ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'value' => 'bob@example.com'],
            ['name' => 'email', 'type' => 'email', 'label' => 'E-mail', 'value' => 'ann@example.com'],
        ], 'Contact us'));

        $envelope = $mail->envelope();
        $this->assertSame('New submission: Contact us', $envelope->subject);
        $this->assertSame(['ann@example.com'], array_map(fn ($address): string => $address->address, $envelope->replyTo));
        $this->assertSame('atrium-forms::mail.submission', $mail->content()->view);
    }

    public function test_without_a_valid_email_there_is_nobody_to_reply_to(): void
    {
        $mail = new SubmissionMail($this->submissionOf(null, [
            ['name' => 'email', 'type' => 'email', 'label' => 'E-mail', 'value' => 'not an address'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'value' => 'bob@example.com'],
            ['name' => 'other', 'type' => 'email', 'label' => 'Other'],
        ]));

        $this->assertSame([], $mail->envelope()->replyTo);
    }

    public function test_the_recipients_are_split_once_each(): void
    {
        $this->assertSame(['a@example.com', 'b@example.com', 'c@example.com'], EmailList::split(" a@example.com, a@example.com;b@example.com\n\nc@example.com "));
        $this->assertSame([], EmailList::split(''));
    }

    public function test_a_wrong_recipient_is_named_once(): void
    {
        $validator = Validator::make(['recipients' => 'good@example.com, first-bad second-bad'], ['recipients' => [new EmailList]]);

        $this->assertSame(['"first-bad" is not an e-mail address.'], $validator->errors()->get('recipients'));
        $this->assertTrue(Validator::make(['recipients' => 'good@example.com; other@example.com'], ['recipients' => [new EmailList]])->passes());
    }

    /**
     * @return array<string,array{0:bool,1:bool,2:string,3:bool}>
     */
    public static function fieldNames(): array
    {
        return [
            // form sent?, editing a field?, the name, valid?
            'a new field, a new name' => [true, false, 'phone', true],
            'a new field, a taken name' => [true, false, 'email', false],
            'a name taken in another form only' => [true, false, 'other', true],
            'the field keeps its own name' => [true, true, 'name', true],
            'the field takes a sibling name' => [true, true, 'email', false],
            'the API leaves the form out: the field own form' => [false, true, 'email', false],
            'the API leaves the form out, its own name' => [false, true, 'name', true],
            'no form at all' => [false, false, 'email', true],
        ];
    }

    #[DataProvider('fieldNames')]
    public function test_a_field_name_is_one_per_form(bool $withForm, bool $editing, string $name, bool $valid): void
    {
        $form = $this->contactForm();
        $edited = FormField::query()->where('form_id', $form->id)->where('name', 'name')->sole();
        $other = Form::query()->create(['name' => 'Other', 'code' => 'other']);
        FormField::query()->create(['form_id' => $other->id, 'name' => 'other', 'type' => 'text', 'label' => 'Other']);

        $this->useRequest($withForm ? ['form_id' => $form->id] : [], $editing ? $edited->id : null);

        $this->assertSame($valid, Validator::make(['name' => $name], ['name' => [new UniqueFieldName]])->passes());
    }

    public function test_a_field_moved_to_another_form_is_checked_there(): void
    {
        $form = $this->contactForm();
        $edited = FormField::query()->where('form_id', $form->id)->where('name', 'name')->sole();
        $other = Form::query()->create(['name' => 'Other', 'code' => 'other']);
        FormField::query()->create(['form_id' => $other->id, 'name' => 'name', 'type' => 'text', 'label' => 'Name']);

        $this->useRequest(['form_id' => $other->id], $edited->id);
        $validator = Validator::make(['name' => 'name'], ['name' => [new UniqueFieldName]]);

        $this->assertSame(['This form already has a field with this code.'], $validator->errors()->get('name'));
    }

    /**
     * @param  array<int,Field>  $fields
     * @return array<string,Field>
     */
    private function byName(array $fields): array
    {
        return array_combine(array_map(fn (Field $field): string => $field->getName(), $fields), $fields);
    }

    /**
     * @param  array<int,array<string,mixed>>|null  $data
     */
    private function submissionOf(?Form $form, ?array $data = null, string $name = 'Contact us'): FormSubmission
    {
        $submission = new FormSubmission;
        $submission->forceFill([
            'form_id' => $form?->id,
            'form_name' => $form->name ?? $name,
            'data' => $data ?? [['name' => 'name', 'type' => 'text', 'label' => 'Name', 'value' => 'Ann']],
            'locale' => 'en',
        ])->save();

        return $submission;
    }

    /**
     * @param  array<string,mixed>  $input
     */
    private function useRequest(array $input, ?int $id): void
    {
        $request = Request::create('/admin/form_fields'.($id ? "/{$id}" : ''), $id ? 'PUT' : 'POST', $input);
        $route = (new Route($id ? 'PUT' : 'POST', 'admin/form_fields/{id?}', []))->bind($request);
        $route->setParameter('id', $id);
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request', $request);
    }
}
