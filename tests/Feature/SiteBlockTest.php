<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Forms\Tests\Feature;

use Atrium\Admin\Builder\BlockType;
use Atrium\Admin\Builder\BlockTypes;
use Atrium\Admin\Facades\Hook;
use Atrium\Admin\Fields\Field;
use Atrium\Forms\Builder\FormBlock;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A form as a block of a page made with the site builder.
 */
final class SiteBlockTest extends TestCase
{
    private function type(): BlockType
    {
        $type = $this->app->make(BlockTypes::class)->get('form');
        $this->assertInstanceOf(FormBlock::class, $type);

        return $type;
    }

    /**
     * @return array<string,mixed>
     */
    private function field(string $name): array
    {
        return collect($this->type()->fields())->map(static fn (Field $field): array => $field->toArray())->firstWhere('name', $name);
    }

    private function accepts(mixed $form): bool
    {
        return Validator::make(['form' => $form], ['form' => $this->type()->fields()[0]->getValidationRules()])->passes();
    }

    public function test_the_module_offers_its_block_type_at_its_priority(): void
    {
        $probe = new class extends BlockType
        {
            public function label(): string
            {
                return 'Probe';
            }

            public function view(): string
            {
                return 'probe';
            }
        };

        Hook::register(BlockTypes::REGISTRY, 'form', $probe, 69);
        $this->type();

        Hook::register(BlockTypes::REGISTRY, 'form', $probe, 70);
        $this->assertSame($probe, $this->app->make(BlockTypes::class)->get('form'));
    }

    public function test_the_editor_picks_a_form_by_its_name_and_whether_it_opens_in_a_window(): void
    {
        $zeta = $this->contactForm(['name' => 'Zeta', 'code' => 'zeta']);
        $alpha = $this->contactForm(['name' => 'Alpha', 'code' => 'alpha']);

        $this->assertSame('Form', $this->type()->label());
        $this->assertSame([], $this->type()->translatable());
        $this->assertSame([], $this->type()->styles());

        $form = $this->field('form');
        $this->assertSame(['select', 'Form', true], [$form['type'], $form['label'], $form['required']]);
        $this->assertSame([[$alpha->id, 'Alpha'], [$zeta->id, 'Zeta']], array_map(static fn (array $option): array => [$option['value'], $option['label']], $form['options']));

        $modal = $this->field('modal');
        $this->assertSame(['checkbox', 'Open in a window behind a button', false, false], [$modal['type'], $modal['label'], $modal['required'], $modal['default']]);

        $this->assertTrue($this->accepts($alpha->id));
        $this->assertFalse($this->accepts($alpha->id + 100));
        $this->assertFalse($this->accepts(null));
    }

    public function test_a_form_is_named_by_a_number_even_when_there_is_none_to_pick(): void
    {
        $this->assertTrue($this->accepts(3));
        $this->assertFalse($this->accepts('contact'));
    }

    public function test_a_block_with_an_active_form_shows_it(): void
    {
        $form = $this->contactForm();

        $this->assertSame(['code' => 'contact', 'modal' => false], $this->type()->data(['form' => $form->id]));
        $this->assertSame(['code' => 'contact', 'modal' => true], $this->type()->data(['form' => (string) $form->id, 'modal' => '1']));
        $this->assertSame(['code' => 'contact', 'modal' => false], $this->type()->data(['form' => $form->id, 'modal' => '0']));
    }

    /**
     * @return array<string,array{mixed}>
     */
    public static function noForms(): array
    {
        return [
            'nothing chosen' => [null],
            'a code' => ['contact'],
            'a number with letters' => ['1abc'],
            'a list' => [[1]],
            'a form that is gone' => [999],
        ];
    }

    #[DataProvider('noForms')]
    public function test_a_block_without_a_form_is_not_drawn(mixed $form): void
    {
        // The form number 1: "1abc" and [1] would find it if read as numbers.
        // MySQL and PostgreSQL do not start counting anew for each test.
        Form::unguarded(fn (): Form => $this->contactForm(['id' => 1]));

        $this->assertNull($this->type()->data(['form' => $form]));
        $this->assertNull($this->type()->data([]));
    }

    public function test_a_block_with_a_switched_off_form_is_not_drawn(): void
    {
        $form = $this->contactForm(['active' => false]);

        $this->assertNull($this->type()->data(['form' => $form->id]));
        $this->assertSame(1, Form::query()->count());
    }

    public function test_the_form_is_drawn_to_be_sent_without_reloading_the_page(): void
    {
        $this->contactForm();

        $html = view($this->type()->view(), ['code' => 'contact', 'modal' => false])->render();

        $this->assertStringContainsString('<form id="form-contact" method="post" action="'.url('forms/contact').'"', $html);
        $this->assertStringContainsString('data-atrium-form ', $html);
        $this->assertStringContainsString('forms/script.js', $html);
        $this->assertStringNotContainsString('<dialog', $html);
    }

    public function test_the_form_opens_in_a_window_when_asked(): void
    {
        $this->contactForm();

        $html = view($this->type()->view(), ['code' => 'contact', 'modal' => true])->render();

        $this->assertStringContainsString('<dialog id="form-contact-dialog"', $html);
        $this->assertStringContainsString('data-atrium-form-open="contact"', $html);
    }
}
