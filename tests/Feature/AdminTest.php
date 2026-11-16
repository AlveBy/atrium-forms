<?php

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormField;
use Atrium\Forms\Models\FormSubmission;
use Atrium\Forms\Tests\TestCase;
use Inertia\Testing\AssertableInertia;

final class AdminTest extends TestCase
{
    public function test_the_three_modules_are_in_the_menu(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get('/admin/forms')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Resource/Index')
                ->where('title', 'Forms')
                ->where('menu', fn ($menu): bool => collect($menu)->pluck('route')->intersect([
                    'admin.forms.index', 'admin.form_fields.index', 'admin.form_submissions.index',
                ])->count() === 3));
    }

    public function test_an_editor_builds_a_form(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/forms', [
                'name' => 'Call me back',
                'code' => 'callback',
                'active' => true,
                'recipients' => "sales@example.com\nboss@example.com",
                'button_label' => ['en' => 'Call me'],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.forms.index'));

        $form = Form::query()->sole();
        $this->assertSame(['sales@example.com', 'boss@example.com'], $form->recipientList());
        $this->assertSame('Call me', $form->button_label);

        $this->post('/admin/form_fields', [
            'form_id' => $form->id,
            'name' => 'phone',
            'type' => 'tel',
            'label' => ['en' => 'Phone'],
            'required' => true,
            'sort' => 10,
        ])->assertSessionHasNoErrors();

        $field = FormField::query()->sole();
        $this->assertSame('tel', $field->type);
        $this->assertTrue($field->required);

        $this->put("/admin/form_fields/{$field->id}", [
            'form_id' => $form->id,
            'name' => 'phone',
            'type' => 'tel',
            'label' => ['en' => 'Your phone'],
            'required' => false,
            'sort' => 10,
        ])->assertSessionHasNoErrors();

        $this->assertSame('Your phone', $field->fresh()?->label);

        $this->get('/admin/form_fields')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('items.data.0.label', 'Your phone')
                ->where('items.data.0.form_name', 'Call me back'));

        $this->delete("/admin/forms/{$form->id}")->assertRedirect();
        $this->assertSame(0, FormField::query()->count());
    }

    public function test_a_wrong_recipient_and_a_repeated_field_code_are_refused(): void
    {
        $form = $this->contactForm();

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/forms', ['name' => 'Other', 'code' => 'other', 'recipients' => 'sales@example.com, not-an-address'])
            ->assertSessionHasErrors('recipients');

        $this->post('/admin/forms', ['name' => 'Again', 'code' => 'contact'])
            ->assertSessionHasErrors('code');

        $this->post('/admin/form_fields', ['form_id' => $form->id, 'name' => 'email', 'type' => 'text', 'label' => 'Again'])
            ->assertSessionHasErrors('name');

        $this->post('/admin/form_fields', ['form_id' => $form->id, 'name' => 'bad name', 'type' => 'text', 'label' => 'Bad'])
            ->assertSessionHasErrors('name');
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

        $submission = FormSubmission::query()->sole();
        $this->assertNull($submission->form_id);
        $this->assertSame('Contact us', $submission->form_name);
    }
}
