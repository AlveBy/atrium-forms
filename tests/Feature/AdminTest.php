<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Admin\History\Trash;
use Atrium\Admin\Models\Site;
use Atrium\Admin\Models\TrashedRecord;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormField;
use Atrium\Forms\Models\FormSubmission;
use Atrium\Forms\Tests\TestCase;
use Inertia\Testing\AssertableInertia;

final class AdminTest extends TestCase
{
    public function test_forms_and_submissions_are_in_the_menu_and_fields_are_not(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get('/admin/forms')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Resource/Index')
                ->where('title', 'Forms')
                ->where('menu', function ($menu): bool {
                    $routes = collect($menu)->pluck('route');

                    return $routes->contains('admin.forms.index')
                        && $routes->contains('admin.form_submissions.index')
                        && ! $routes->contains('admin.form_fields.index');
                }));

        // The fields are edited on the form's page: no list of their own.
        $this->get('/admin/form_fields')->assertNotFound();
    }

    public function test_an_editor_builds_a_form_with_its_fields(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/forms', [
                'name' => 'Call me back',
                'code' => 'callback',
                'active' => true,
                'recipients' => "sales@example.com\nboss@example.com",
                'button_label' => ['en' => 'Call me'],
                'builder' => [
                    ['name' => 'name', 'type' => 'text', 'label' => ['en' => 'Name'], 'required' => true, 'width' => 6],
                    ['name' => 'phone', 'type' => 'tel', 'label' => ['en' => 'Phone'], 'required' => true, 'width' => 6, 'placeholder' => ['en' => '+375 …']],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.forms.index'));

        $form = Form::query()->sole();
        $this->assertSame(['sales@example.com', 'boss@example.com'], $form->recipientList());
        $this->assertSame('Call me', $form->button_label);
        $this->assertTrue($form->active);
        $this->assertSame(['name', 'phone'], $form->fields->pluck('name')->all());
        $this->assertSame([6, 6], $form->fields->pluck('width')->all());
        $this->assertSame('+375 …', $form->fields[1]->placeholder);

        $this->get("/admin/forms/{$form->id}/edit")
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('item.builder.1.name', 'phone')
                ->where('item.builder.1.label', ['en' => 'Phone'])
                ->where('item.builder.1.width', 6));

        $this->delete("/admin/forms/{$form->id}")->assertRedirect();
        $this->assertSame(2, FormField::query()->count(), 'A form in the trash keeps its fields.');

        app(Trash::class)->purge(TrashedRecord::query()->sole());
        $this->assertSame(0, FormField::query()->count());
    }

    public function test_a_wrong_recipient_and_a_repeated_field_code_are_refused(): void
    {
        $this->contactForm();

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/forms', ['name' => 'Other', 'code' => 'other', 'recipients' => 'sales@example.com, not-an-address'])
            ->assertSessionHasErrors('recipients');

        $this->post('/admin/forms', ['name' => 'Again', 'code' => 'contact'])
            ->assertSessionHasErrors('code');

        $twice = ['name' => 'email', 'type' => 'email', 'label' => ['en' => 'E-mail'], 'width' => 12];

        $this->post('/admin/forms', ['name' => 'Other', 'code' => 'other', 'builder' => [$twice, $twice]])
            ->assertSessionHasErrors(['builder' => 'Field "E-mail": Two fields have the code "email".']);

        $this->post('/admin/forms', ['name' => 'Other', 'code' => 'other', 'builder' => [[...$twice, 'name' => 'bad name']]])
            ->assertSessionHasErrors('builder');

        $this->assertSame(1, Form::query()->count());
    }

    public function test_submissions_are_read_and_deleted_but_not_written(): void
    {
        $this->contactForm();
        $this->post('/forms/contact', $this->submission())->assertRedirect();
        $submission = FormSubmission::query()->sole();

        $this->actingAs($this->admin(), 'admin')
            ->get('/admin/form_submissions')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('title', 'Submissions')
                ->where('can.create', false)
                ->where('items.total', 1)
                ->where('items.data.0.form_name', 'Contact us')
                ->where('items.data.0.preview', fn (string $preview): bool => str_contains($preview, 'ann@example.com')));

        $this->get("/admin/form_submissions/{$submission->id}/edit")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('can.update', false)
                ->where('can.delete', true)
                ->where('item.content', fn (string $content): bool => str_contains($content, 'Topic: Delivery')
                    && str_contains($content, 'I agree: Yes')));

        $this->get('/admin/form_submissions/create')->assertForbidden();
        $this->post('/admin/form_submissions', [])->assertForbidden();
        $this->put("/admin/form_submissions/{$submission->id}", [])->assertForbidden();

        $this->delete("/admin/form_submissions/{$submission->id}")->assertRedirect();
        $this->assertSame(0, FormSubmission::query()->count());
    }

    public function test_a_submission_stays_when_its_form_is_deleted(): void
    {
        $form = $this->contactForm();
        $this->post('/forms/contact', $this->submission())->assertRedirect();

        $this->actingAs($this->admin(), 'admin')->delete("/admin/forms/{$form->id}");
        $this->assertSame($form->id, FormSubmission::query()->sole()->form_id, 'The form is in the trash.');
        app(Trash::class)->purge(TrashedRecord::query()->sole());

        $submission = FormSubmission::query()->sole();
        $this->assertNull($submission->form_id);
        $this->assertSame('Contact us', $submission->form_name);
    }

    public function test_the_form_keeps_count_of_its_submissions(): void
    {
        $form = $this->contactForm();
        $updated = $form->fresh()?->updated_at?->toDateTimeString();

        $this->travel(1)->minutes();
        $this->post('/forms/contact', $this->submission())->assertRedirect();
        $this->post('/forms/contact', $this->submission(['name' => 'Bob']))->assertRedirect();
        $this->post('/forms/contact', [...$this->submission(), 'website' => 'spam'])->assertRedirect();

        $this->assertSame(2, $form->fresh()?->submissions_count);
        $this->assertSame($updated, $form->fresh()?->updated_at?->toDateTimeString());

        $this->actingAs($this->admin(), 'admin')
            ->get('/admin/forms')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('columns', fn ($columns): bool => collect($columns)->contains('name', 'submissions_count'))
                ->where('items.data.0.submissions_count', 2));

        $this->delete('/admin/form_submissions/'.FormSubmission::query()->value('id'))->assertRedirect();
        $this->assertSame(1, $form->fresh()?->submissions_count);

        // The migration counts what is already there.
        Form::query()->whereKey($form->id)->toBase()->update(['submissions_count' => 0]);
        $migration = require dirname(__DIR__, 2).'/database/migrations/2026_11_17_000002_add_submissions_count_to_forms.php';
        $migration->down();
        $migration->up();
        $this->assertSame(1, $form->fresh()?->submissions_count);
    }

    public function test_the_count_stays_right_for_other_forms_sites_and_orphans(): void
    {
        $form = $this->contactForm();
        $other = Form::query()->create(['name' => 'Other', 'code' => 'other']);
        $this->post('/forms/contact', $this->submission())->assertRedirect();

        // Another form of the site is not counted.
        $this->assertSame(1, $form->fresh()?->submissions_count);
        $this->assertSame(0, $other->fresh()?->submissions_count);

        // A count that is already 0 does not go below it.
        Form::query()->whereKey($form->id)->toBase()->update(['submissions_count' => 0]);
        $this->actingAs($this->admin(), 'admin')
            ->delete('/admin/form_submissions/'.FormSubmission::query()->value('id'))
            ->assertRedirect();
        $this->assertSame(0, $form->fresh()?->submissions_count);

        // A submission whose form was deleted is deleted without touching any form.
        $this->post('/forms/contact', $this->submission())->assertRedirect();
        $this->delete("/admin/forms/{$form->id}")->assertRedirect();
        $this->delete('/admin/form_submissions/'.FormSubmission::query()->value('id'))->assertRedirect();
        $this->assertSame(0, FormSubmission::query()->count());
        $this->assertSame(0, $other->fresh()?->submissions_count);

        // A form of another site is counted while this site is the current one.
        $site = Site::query()->create(['code' => 'second', 'name' => 'Second', 'folder' => 'site2']);
        $theirs = Form::query()->withoutGlobalScopes()->create(['site_id' => $site->id, 'name' => 'Theirs', 'code' => 'theirs']);
        (new FormSubmission)->forceFill(['site_id' => $site->id, 'form_id' => $theirs->id, 'form_name' => 'Theirs', 'data' => []])->save();
        $this->assertSame(1, Form::query()->withoutGlobalScopes()->find($theirs->id)?->submissions_count);
        $this->assertSame(0, $other->fresh()?->submissions_count);

        // The migration gives a form without submissions 0.
        $migration = require dirname(__DIR__, 2).'/database/migrations/2026_11_17_000002_add_submissions_count_to_forms.php';
        $migration->down();
        $migration->up();
        $this->assertSame(0, $other->fresh()?->submissions_count);
        $this->assertSame(1, Form::query()->withoutGlobalScopes()->find($theirs->id)?->submissions_count);
    }
}
