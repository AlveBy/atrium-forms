<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Admin\Embeds\Embeds;
use Atrium\Admin\Models\AdminRole;
use Atrium\Admin\Models\AdminUser;
use Atrium\Admin\Models\Site;
use Atrium\Forms\Embeds\FormEmbed;
use Atrium\Forms\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

/**
 * "Insert → Form" in the editor: the form is picked from the list and the
 * site draws it in the place of the placeholder.
 */
final class FormEmbedTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        parent::defineRoutes($router);

        // A page of the site whose text holds a form.
        Route::middleware('web')->get('embedded/{id}', fn (string $id) => Blade::render('<main>@embeds($html)</main>', ['html' => '<p>Text</p><div data-atrium-embed="form" data-id="'.$id.'"></div>']));
    }

    private function placeholder(int|string $id): string
    {
        return '<div data-atrium-embed="form" data-id="'.$id.'"></div>';
    }

    private function embeds(): Embeds
    {
        return $this->app->make(Embeds::class);
    }

    /**
     * @param  array<int,string>  $permissions
     */
    private function editor(array $permissions): AdminUser
    {
        $user = AdminUser::query()->create(['name' => 'Editor', 'email' => 'editor'.AdminUser::query()->count().'@example.com', 'password' => 'secret-password', 'is_active' => true]);
        $user->roles()->attach(AdminRole::query()->create(['name' => 'Role', 'permissions' => $permissions]));

        return $user->fresh();
    }

    public function test_the_module_registers_the_form_type_for_the_editor(): void
    {
        $this->assertInstanceOf(FormEmbed::class, $this->embeds()->get('form'));
        $this->assertSame([['type' => 'form', 'label' => 'Form', 'url' => '/admin/embeds/form']], $this->embeds()->types($this->admin()));
    }

    public function test_the_placeholder_becomes_the_form_with_its_fields_and_the_text_stays(): void
    {
        $form = $this->contactForm();

        $html = $this->embeds()->render('<p>Write to us:</p>'.$this->placeholder($form->id).'<p>Thanks</p>');

        $this->assertStringStartsWith('<p>Write to us:</p>', $html);
        $this->assertStringEndsWith('<p>Thanks</p>', $html);
        $this->assertStringContainsString('<form id="form-contact" method="post" action="'.url('/forms/contact').'"', $html);
        $this->assertStringContainsString('name="fields[email]"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        // Sent without reloading the page, as an embedded form should be.
        $this->assertStringContainsString('data-atrium-form', $html);
        $this->assertStringContainsString('/forms/script.js', $html);
        $this->assertStringNotContainsString('data-atrium-embed', $html);
    }

    public function test_a_form_that_is_switched_off_or_gone_leaves_nothing(): void
    {
        $off = $this->contactForm(['active' => false]);

        $this->assertSame('<p>a</p>', $this->embeds()->render('<p>a</p>'.$this->placeholder($off->id)));
        $this->assertSame('<p>a</p>', $this->embeds()->render('<p>a</p>'.$this->placeholder(9999)));
        $this->assertSame('<p>a</p>', $this->embeds()->render('<p>a</p>'.$this->placeholder('contact')));
    }

    public function test_a_page_of_the_site_shows_its_form_and_not_the_form_of_another_site(): void
    {
        $other = Site::query()->create(['code' => 'shop', 'name' => 'Shop', 'domains' => ['shop.example.org'], 'languages' => ['en']]);
        $theirs = $this->contactForm(['site_id' => $other->id, 'code' => 'theirs']);
        $ours = $this->contactForm(['name' => 'Ours', 'code' => 'ours']);

        $this->get('/embedded/'.$ours->id)->assertOk()->assertSee('<form id="form-ours"', false);
        $this->get('/embedded/'.$theirs->id)->assertOk()->assertDontSee('<form', false)->assertDontSee('theirs');
    }

    public function test_the_picker_offers_the_forms_by_name_to_one_who_may_see_them(): void
    {
        $contact = $this->contactForm();
        $this->contactForm(['name' => 'Callback', 'code' => 'callback']);
        $this->actingAs($this->editor(['forms.view']), 'admin');

        $this->getJson('/admin/embeds/form?q=contact')->assertOk()->assertExactJson(['data' => [['id' => (string) $contact->id, 'label' => 'Contact us']]]);
        $this->getJson('/admin/embeds/form')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson("/admin/embeds/form?ids={$contact->id}")->assertOk()->assertExactJson(['data' => [['id' => (string) $contact->id, 'label' => 'Contact us']]]);
    }

    public function test_the_picker_is_closed_to_one_who_may_not_see_the_forms(): void
    {
        $this->contactForm();
        $this->actingAs($this->editor([]), 'admin');

        $this->getJson('/admin/embeds/form')->assertForbidden();
    }
}
