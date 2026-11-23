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
        'submissions' => 'Заявки',
    ],
    'fields' => [
        'builder' => 'Поля',
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
        'submitted_at' => 'Надіслано',
        'content' => 'Зміст',
        'page_url' => 'Сторінка',
        'locale' => 'Мова',
        'ip' => 'IP-адреса',
        'user_agent' => 'Браузер',
    ],
    'hints' => [
        'builder' => 'Поле займає від 1 до 12 колонок; поля, що вміщуються поруч, стають в один рядок. На телефоні кожне поле — на весь рядок.',
        'code' => 'Латинські літери, цифри, «-» і «_». На сторінці: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Адреси пошти, по одній у рядку або через кому. Порожньо — заявки лише зберігаються тут.',
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
    ],
    'mail' => [
        'subject' => 'Нова заявка: :form',
        'heading' => 'Нова заявка: :form',
        'page' => 'Надіслано зі сторінки',
    ],
];
