<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Admin\Models\Language;
use Atrium\Admin\Support\ContentLanguages;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormField;
use Atrium\Forms\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The fields of a form, built on the form's own page: how they are written,
 * read back, checked and drawn on the site.
 */
final class BuilderTest extends TestCase
{
    public function test_the_builder_reads_the_fields_in_order(): void
    {
        $form = $this->contactForm();
        $email = $form->fields[1];

        $this->assertSame([
            'id' => $email->id,
            'name' => 'email',
            'type' => 'email',
            'required' => true,
            'width' => 12,
            'min' => null,
            'max' => null,
            'label' => ['en' => 'E-mail'],
            'hint' => [],
            'placeholder' => [],
            'options' => [],
        ], $form->builder[1]);
        $this->assertSame(['name', 'email', 'topic', 'message', 'consent'], array_column($form->builder, 'name'));
        $this->assertSame(['en' => "Order\nDelivery"], $form->builder[2]['options']);
        $this->assertSame('email', $form->toArray()['builder'][1]['name']);
    }

    public function test_a_save_keeps_the_fields_still_in_the_list_and_drops_the_rest(): void
    {
        $form = $this->contactForm();
        [$name, $email, , $message] = $form->fields->all();
        $form->load('fields');

        $form->afterAdminSave(['builder' => [
            ['id' => (string) $email->id, 'name' => 'mail', 'type' => 'email', 'label' => ['en' => 'Mail'], 'required' => true, 'width' => '6'],
            ['id' => $name->id, 'name' => 'name', 'type' => 'text', 'label' => ['en' => 'Name'], 'width' => 6],
            ['name' => 'city', 'type' => 'text', 'label' => ['en' => 'City'], 'width' => 4],
            ['id' => null, 'name' => 'zip', 'type' => 'text', 'label' => ['en' => 'Zip'], 'width' => 8],
            ['id' => $message->id, 'name' => 'message', 'type' => 'textarea', 'label' => ['en' => 'Message'], 'width' => 12, 'required' => null],
        ]]);

        $fields = FormField::query()->where('form_id', $form->id)->orderBy('sort')->get();

        $this->assertSame(['mail', 'name', 'city', 'zip', 'message'], $fields->pluck('name')->all());
        $this->assertSame([10, 20, 30, 40, 50], $fields->pluck('sort')->all());
        $this->assertSame([6, 6, 4, 8, 12], $fields->pluck('width')->all());
        $this->assertSame([true, false, false, false, false], $fields->pluck('required')->all());
        $this->assertSame([$email->id, $name->id], $fields->take(2)->pluck('id')->all());
        $this->assertSame($message->id, $fields[4]->id);
        $this->assertNotContains($fields[2]->id, [$name->id, $email->id, $message->id]);
        $this->assertSame(5, FormField::query()->count());

        // The form gives back what was written, not what it had loaded before.
        $this->assertSame(['mail', 'name', 'city', 'zip', 'message'], array_column($form->builder, 'name'));
    }

    public function test_the_order_of_the_list_counts_and_not_its_keys(): void
    {
        $form = $this->contactForm();

        $form->afterAdminSave(['builder' => [
            7 => ['name' => 'first', 'type' => 'text', 'label' => ['en' => 'First'], 'width' => 12],
            3 => ['name' => 'second', 'type' => 'text', 'label' => ['en' => 'Second'], 'width' => 12],
        ]]);

        $fields = FormField::query()->where('form_id', $form->id)->orderBy('sort')->get();
        $this->assertSame(['first' => 10, 'second' => 20], $fields->pluck('sort', 'name')->all());
        $this->assertTrue($fields[0]->form->is($form));
    }

    public function test_a_field_of_another_form_is_not_taken_over(): void
    {
        $contact = $this->contactForm();
        $other = Form::query()->create(['name' => 'Other', 'code' => 'other']);
        $foreign = FormField::query()->create(['form_id' => $other->id, 'name' => 'phone', 'type' => 'tel', 'label' => 'Phone']);

        $contact->afterAdminSave(['builder' => [
            ['id' => $foreign->id, 'name' => 'phone', 'type' => 'tel', 'label' => ['en' => 'Your phone'], 'width' => 12],
        ]]);

        $this->assertSame('Phone', $foreign->fresh()->label);
        $this->assertSame($other->id, $foreign->fresh()->form_id);
        $field = FormField::query()->where('form_id', $contact->id)->sole();
        $this->assertNotSame($foreign->id, $field->id);
        $this->assertSame('Your phone', $field->label);
    }

    public function test_an_empty_list_takes_every_field_away(): void
    {
        $form = $this->contactForm();

        $form->afterAdminSave(['builder' => []]);

        $this->assertSame(0, FormField::query()->count());
        $this->assertSame([], $form->builder);
    }

    /**
     * @return array<string, array{array<string,mixed>}>
     */
    public static function withoutBuilder(): array
    {
        return [
            'not sent' => [['name' => 'Renamed']],
            'sent empty' => [['builder' => null]],
            'sent as text' => [['builder' => 'name,email']],
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     */
    #[DataProvider('withoutBuilder')]
    public function test_a_save_without_the_list_leaves_the_fields_alone(array $data): void
    {
        $form = $this->contactForm();

        $form->afterAdminSave($data);

        $this->assertSame(5, FormField::query()->count());
    }

    public function test_the_texts_are_kept_per_site_language_trimmed_and_without_blanks(): void
    {
        Language::query()->create(['code' => 'ru', 'name' => 'Русский', 'sort' => 200]);
        app(ContentLanguages::class)->flush();
        $form = Form::query()->create(['name' => 'Ask', 'code' => 'ask']);

        $form->afterAdminSave(['builder' => [[
            'name' => 'topic', 'type' => 'select', 'width' => 12,
            'label' => ['en' => '  Topic ', 'ru' => 'Тема', 'xx' => 'Unknown'],
            'hint' => ['en' => 'Pick one', 'ru' => '   '],
            'placeholder' => ['en' => null, 'ru' => ''],
            'options' => ['en' => "Order\nDelivery", 'ru' => "Заказ\nДоставка"],
        ], [
            'name' => 'note', 'type' => 'text', 'width' => 12, 'label' => ['en' => 'Note'],
        ]]]);

        $topic = $form->fields()->where('name', 'topic')->sole();
        $this->assertSame(['en' => 'Topic', 'ru' => 'Тема'], $topic->getTranslations('label'));
        $this->assertSame(['en' => 'Pick one'], $topic->getTranslations('hint'));
        $this->assertSame([], $topic->getTranslations('placeholder'));
        $this->assertSame(['en' => "Order\nDelivery", 'ru' => "Заказ\nДоставка"], $topic->getTranslations('options'));
        $note = $form->fields()->where('name', 'note')->sole();
        $this->assertSame([], $note->getTranslations('hint'));
    }

    /**
     * @return array<string, array{string, mixed, mixed, ?int, ?int}>
     */
    public static function bounds(): array
    {
        return [
            'text, as numbers' => ['text', 2, 40, 2, 40],
            'text, as digits' => ['text', '2', '40', 2, 40],
            'text, from zero' => ['text', 0, 0, 0, 0],
            'e-mail' => ['email', 5, null, 5, null],
            'phone' => ['tel', null, 20, null, 20],
            'number below zero' => ['number', -10, -1, -10, -1],
            'textarea' => ['textarea', 10, 2000, 10, 2000],
            'date has none' => ['date', 1, 5, null, null],
            'list has none' => ['select', 1, 5, null, null],
            'buttons have none' => ['radio', 1, 5, null, null],
            'a checkbox has none' => ['checkbox', 1, 5, null, null],
            'empty' => ['text', '', null, null, null],
        ];
    }

    #[DataProvider('bounds')]
    public function test_the_least_and_most_are_kept_only_where_they_apply(string $type, mixed $min, mixed $max, ?int $keptMin, ?int $keptMax): void
    {
        $form = Form::query()->create(['name' => 'Ask', 'code' => 'ask']);

        $form->afterAdminSave(['builder' => [
            ['name' => 'value', 'type' => $type, 'width' => 12, 'label' => ['en' => 'Value'], 'options' => ['en' => 'One'], 'min' => $min, 'max' => $max],
        ]]);

        $field = $form->fields()->sole();
        $this->assertSame($keptMin, $field->min);
        $this->assertSame($keptMax, $field->max);
    }

    public function test_a_field_without_a_least_or_most_has_none(): void
    {
        $form = Form::query()->create(['name' => 'Ask', 'code' => 'ask']);

        $form->afterAdminSave(['builder' => [['name' => 'value', 'type' => 'text', 'width' => 12, 'label' => ['en' => 'Value']]]]);

        $field = $form->fields()->sole();
        $this->assertNull($field->min);
        $this->assertNull($field->max);
        $this->assertFalse($field->required);
    }

    /**
     * @return array<string, array{string, ?int, ?int, array<int,string>}>
     */
    public static function rules(): array
    {
        return [
            'text, both' => ['text', 2, 40, ['nullable', 'string', 'max:1000', 'min:2', 'max:40']],
            'text, least only' => ['text', 2, null, ['nullable', 'string', 'max:1000', 'min:2']],
            'text, most only' => ['text', null, 40, ['nullable', 'string', 'max:1000', 'max:40']],
            'text, from zero' => ['text', 0, 0, ['nullable', 'string', 'max:1000', 'min:0', 'max:0']],
            'number' => ['number', -5, 5, ['nullable', 'numeric', 'min:-5', 'max:5']],
            'textarea' => ['textarea', 10, null, ['nullable', 'string', 'max:10000', 'min:10']],
            'date ignores them' => ['date', 1, 5, ['nullable', 'date']],
            'checkbox ignores them' => ['checkbox', 1, 5, ['nullable', 'boolean']],
        ];
    }

    /**
     * @param  array<int,string>  $expected
     */
    #[DataProvider('rules')]
    public function test_the_least_and_most_join_the_rules_of_the_visitor_value(string $type, ?int $min, ?int $max, array $expected): void
    {
        $field = new FormField(['type' => $type, 'min' => $min, 'max' => $max]);

        $this->assertSame($expected, $field->publicRules());
    }

    /**
     * @return array<string, array{string, mixed, bool}>
     */
    public static function values(): array
    {
        return [
            'a name of two letters' => ['text', 'Al', true],
            'a name of one letter' => ['text', 'A', false],
            'a name of five letters' => ['text', 'Alice', true],
            'a name of six letters' => ['text', 'Alicia', false],
            'a number at the least' => ['number', '2', true],
            'a number below the least' => ['number', '1', false],
            'a number at the most' => ['number', '5', true],
            'a number above the most' => ['number', '6', false],
        ];
    }

    #[DataProvider('values')]
    public function test_a_visitor_value_is_held_to_the_least_and_most(string $type, mixed $value, bool $valid): void
    {
        $field = new FormField(['type' => $type, 'min' => 2, 'max' => 5]);

        $this->assertSame($valid, Validator::make(['v' => $value], ['v' => $field->publicRules()])->passes());
    }

    public function test_the_site_lays_the_fields_out_with_their_hints_and_bounds(): void
    {
        $form = Form::query()->create(['name' => 'Contact us', 'code' => 'contact']);
        $form->afterAdminSave(['builder' => [
            ['name' => 'name', 'type' => 'text', 'width' => 6, 'required' => true, 'label' => ['en' => 'Name'], 'placeholder' => ['en' => 'Ann'], 'hint' => ['en' => 'As in your passport'], 'min' => 2, 'max' => 40],
            ['name' => 'age', 'type' => 'number', 'width' => 6, 'label' => ['en' => 'Age'], 'min' => 18, 'max' => 99],
            ['name' => 'day', 'type' => 'date', 'width' => 4, 'label' => ['en' => 'Day']],
            ['name' => 'note', 'type' => 'textarea', 'width' => 8, 'label' => ['en' => 'Note'], 'placeholder' => ['en' => 'Anything else?'], 'max' => 500],
        ]]);

        $html = $this->get('/contacts')->assertOk()->getContent();

        $this->assertStringContainsString('style="--atrium-span: 6" data-field="name"', $html);
        $this->assertStringContainsString('style="--atrium-span: 4" data-field="day"', $html);
        $this->assertStringContainsString('style="--atrium-span: 8" data-field="note"', $html);
        $this->assertMatchesRegularExpression('/<input type="text" id="form-contact-name" name="fields\[name\]" value="" placeholder="Ann"\s+minlength="2"\s+maxlength="40"\s+required\s+aria-describedby="form-contact-name-hint"\s*>/', $html);
        $this->assertStringContainsString('<p class="atrium-form__hint" id="form-contact-name-hint">As in your passport</p>', $html);
        $this->assertMatchesRegularExpression('/<input type="number" id="form-contact-age" name="fields\[age\]" value="" placeholder=""\s+min="18"\s+max="99"\s*>/', $html);
        $this->assertMatchesRegularExpression('/<input type="date" id="form-contact-day" name="fields\[day\]" value="" placeholder=""\s*>/', $html);
        $this->assertMatchesRegularExpression('/<textarea id="form-contact-note" name="fields\[note\]" rows="5" placeholder="Anything else\?"\s+maxlength="500"\s*><\/textarea>/', $html);
        $this->assertStringContainsString('@media (max-width: 40rem)', $html);
    }

    public function test_an_error_and_a_hint_both_describe_the_field(): void
    {
        $form = $this->contactForm();
        $form->fields[1]->update(['hint' => 'We answer here']);

        $this->from('/contacts')->post(route('atrium.forms.submit', 'contact'), $this->submission(['email' => 'nope']))->assertRedirect('/contacts#form-contact');
        $html = $this->get('/contacts')->getContent();

        $this->assertMatchesRegularExpression('/aria-invalid="true"\s+aria-describedby="form-contact-email-hint form-contact-email-error"/', $html);
        $this->assertStringContainsString('id="form-contact-email-error"', $html);
    }

    public function test_old_fields_keep_the_whole_row_after_the_upgrade(): void
    {
        $migration = require dirname(__DIR__, 2).'/database/migrations/2026_11_23_000001_add_layout_to_form_fields.php';
        $form = $this->contactForm();

        $migration->down();
        $this->assertFalse(Schema::hasColumn('form_fields', 'width'));
        DB::table('form_fields')->insert(['form_id' => $form->id, 'name' => 'city', 'type' => 'text', 'label' => json_encode(['en' => 'City']), 'required' => false, 'sort' => 60]);

        $migration->up();

        $fields = $form->fields()->get();
        $this->assertSame([12, 12, 12, 12, 12, 12], $fields->pluck('width')->all());
        $this->assertSame([null], $fields->pluck('min')->merge($fields->pluck('max'))->unique()->values()->all());
        $this->assertSame([], $fields->last()->getTranslations('hint'));
        $this->assertSame('City', $fields->last()->label);

        // The old form still takes a submission as before.
        $this->post(route('atrium.forms.submit', 'contact'), $this->submission(['city' => 'Minsk']))->assertSessionHasNoErrors();
        $this->assertSame(['name' => 'city', 'type' => 'text', 'label' => 'City', 'value' => 'Minsk'], collect($form->submissions()->sole()->data)->firstWhere('name', 'city'));
    }
}
