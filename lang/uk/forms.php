<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Форми',
        'form_fields' => 'Поля форм',
        'submissions' => 'Заявки',
    ],
    'fields' => [
        'id' => 'ID',
        'active' => 'Активна',
        'name' => 'Назва',
        'code' => 'Код',
        'recipients' => 'Куди надсилати заявки',
        'button_label' => 'Текст кнопки',
        'success_message' => 'Повідомлення після надсилання',
        'updated_at' => 'Змінено',
        'submissions_count' => 'Заявок',
        'form' => 'Форма',
        'label' => 'Підпис',
        'field_name' => 'Код поля',
        'type' => 'Тип',
        'required' => 'Обовʼязкове',
        'options' => 'Варіанти',
        'sort' => 'Сортування',
        'submitted_at' => 'Надіслано',
        'content' => 'Зміст',
        'page_url' => 'Сторінка',
        'locale' => 'Мова',
        'ip' => 'IP-адреса',
        'user_agent' => 'Браузер',
    ],
    'hints' => [
        'code' => 'Латинські літери, цифри, «-» і «_». На сторінці: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Адреси пошти, по одній у рядку або через кому. Порожньо — заявки лише зберігаються тут.',
        'field_name' => 'Латинські літери, цифри, «-» і «_»; у формі не повторюється.',
        'options' => 'Для списку й перемикачів: по одному варіанту в рядку.',
    ],
    'types' => [
        'text' => 'Рядок',
        'email' => 'E-mail',
        'tel' => 'Телефон',
        'number' => 'Число',
        'date' => 'Дата',
        'textarea' => 'Текст',
        'select' => 'Список',
        'radio' => 'Перемикачі',
        'checkbox' => 'Прапорець',
    ],
    'public' => [
        'submit' => 'Надіслати',
        'sent' => 'Дякуємо! Повідомлення надіслано.',
        'too_fast' => 'Форму надіслано занадто швидко. Перевірте її та надішліть ще раз.',
        'too_many' => 'Забагато спроб. Спробуйте за хвилину.',
        'honeypot' => 'Залиште це поле порожнім',
        'choose' => '— Оберіть —',
        'yes' => 'Так',
        'no' => 'Ні',
    ],
    'validation' => [
        'recipients' => '«:email» — не адреса пошти.',
        'field_name_taken' => 'У цій формі вже є поле з таким кодом.',
    ],
    'mail' => [
        'subject' => 'Нова заявка: :form',
        'heading' => 'Нова заявка: :form',
        'page' => 'Надіслано зі сторінки',
    ],
];
