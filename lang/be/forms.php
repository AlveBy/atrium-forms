<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Формы',
        'submissions' => 'Заяўкі',
    ],
    'fields' => [
        'builder' => 'Палі',
        'id' => 'ID',
        'active' => 'Актыўная',
        'name' => 'Назва',
        'code' => 'Код',
        'recipients' => 'Куды адпраўляць заяўкі',
        'button_label' => 'Тэкст кнопкі',
        'success_message' => 'Паведамленне пасля адпраўкі',
        'updated_at' => 'Зменена',
        'submissions_count' => 'Заявак',
        'form' => 'Форма',
        'submitted_at' => 'Адпраўлена',
        'content' => 'Змест',
        'page_url' => 'Старонка',
        'locale' => 'Мова',
        'ip' => 'IP-адрас',
        'user_agent' => 'Браўзер',
    ],
    'hints' => [
        'builder' => 'Поле займае ад 1 да 12 калонак; палі, што змяшчаюцца побач, становяцца ў адзін радок. На тэлефоне кожнае поле — на ўвесь радок.',
        'code' => 'Лацінскія літары, лічбы, «-» і «_». На старонцы: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Адрасы пошты, па адным у радку або праз коску. Пуста — заяўкі толькі захоўваюцца тут.',
    ],
    'types' => [
        'text' => 'Радок',
        'email' => 'E-mail',
        'tel' => 'Тэлефон',
        'number' => 'Лік',
        'date' => 'Дата',
        'textarea' => 'Тэкст',
        'select' => 'Спіс',
        'radio' => 'Пераключальнікі',
        'checkbox' => 'Сцяжок',
    ],
    'public' => [
        'submit' => 'Адправіць',
        'sent' => 'Дзякуй! Паведамленне адпраўлена.',
        'too_fast' => 'Форма адпраўлена занадта хутка. Праверце яе і адпраўце яшчэ раз.',
        'too_many' => 'Занадта шмат спроб. Паспрабуйце праз хвіліну.',
        'honeypot' => 'Пакіньце гэта поле пустым',
        'choose' => '— Выберыце —',
        'yes' => 'Так',
        'no' => 'Не',
    ],
    'validation' => [
        'recipients' => '«:email» — не адрас пошты.',
    ],
    'mail' => [
        'subject' => 'Новая заяўка: :form',
        'heading' => 'Новая заяўка: :form',
        'page' => 'Адпраўлена са старонкі',
    ],
];
