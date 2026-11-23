<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Формалар',
        'submissions' => 'Өтінімдер',
    ],
    'fields' => [
        'builder' => 'Өрістер',
        'id' => 'ID',
        'active' => 'Белсенді',
        'name' => 'Атауы',
        'code' => 'Коды',
        'recipients' => 'Өтінімдерді қайда жіберу',
        'button_label' => 'Түйме мәтіні',
        'success_message' => 'Жіберілгеннен кейінгі хабарлама',
        'updated_at' => 'Өзгертілді',
        'submissions_count' => 'Өтінімдер',
        'form' => 'Форма',
        'submitted_at' => 'Жіберілді',
        'content' => 'Мазмұны',
        'page_url' => 'Бет',
        'locale' => 'Тіл',
        'ip' => 'IP мекенжайы',
        'user_agent' => 'Браузер',
    ],
    'hints' => [
        'builder' => 'Өріс 1-ден 12-ге дейін баған алады; қатар сыйған өрістер бір жолға тұрады. Телефонда әр өріс бүкіл жолды алады.',
        'code' => 'Латын әріптері, сандар, «-» және «_». Бетте: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Пошта мекенжайлары, әр жолға біреуден немесе үтір арқылы. Бос болса — өтінімдер тек осында сақталады.',
    ],
    'types' => [
        'text' => 'Жол',
        'email' => 'E-mail',
        'tel' => 'Телефон',
        'number' => 'Сан',
        'date' => 'Күн',
        'textarea' => 'Мәтін',
        'select' => 'Тізім',
        'radio' => 'Ауыстырғыштар',
        'checkbox' => 'Құсбелгі',
    ],
    'public' => [
        'submit' => 'Жіберу',
        'sent' => 'Рақмет! Хабарлама жіберілді.',
        'too_fast' => 'Форма тым жылдам жіберілді. Оны тексеріп, қайта жіберіңіз.',
        'too_many' => 'Әрекеттер тым көп. Бір минуттан кейін қайталаңыз.',
        'honeypot' => 'Бұл өрісті бос қалдырыңыз',
        'choose' => '— Таңдаңыз —',
        'yes' => 'Иә',
        'no' => 'Жоқ',
    ],
    'validation' => [
        'recipients' => '«:email» — пошта мекенжайы емес.',
    ],
    'mail' => [
        'subject' => 'Жаңа өтінім: :form',
        'heading' => 'Жаңа өтінім: :form',
        'page' => 'Жіберілген бет',
    ],
];
