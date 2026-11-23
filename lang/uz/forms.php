<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Shakllar',
        'submissions' => 'Arizalar',
    ],
    'fields' => [
        'builder' => 'Maydonlar',
        'id' => 'ID',
        'active' => 'Faol',
        'name' => 'Nomi',
        'code' => 'Kod',
        'recipients' => 'Arizalar qayerga yuborilsin',
        'button_label' => 'Tugma matni',
        'success_message' => 'Yuborilgandan keyingi xabar',
        'updated_at' => 'O‘zgartirilgan',
        'submissions_count' => 'Arizalar',
        'form' => 'Shakl',
        'submitted_at' => 'Yuborilgan',
        'content' => 'Mazmuni',
        'page_url' => 'Sahifa',
        'locale' => 'Til',
        'ip' => 'IP manzil',
        'user_agent' => 'Brauzer',
    ],
    'hints' => [
        'builder' => 'Maydon 1 dan 12 gacha ustun egallaydi; yonma-yon sig\'adigan maydonlar bir qatorga turadi. Telefonda har bir maydon butun qatorni egallaydi.',
        'code' => 'Lotin harflari, raqamlar, «-» va «_». Sahifada: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Pochta manzillari, har qatorda bittadan yoki vergul bilan. Bo‘sh bo‘lsa — arizalar faqat shu yerda saqlanadi.',
    ],
    'types' => [
        'text' => 'Matn',
        'email' => 'E-mail',
        'tel' => 'Telefon',
        'number' => 'Son',
        'date' => 'Sana',
        'textarea' => 'Uzun matn',
        'select' => 'Ro‘yxat',
        'radio' => 'Almashtirgichlar',
        'checkbox' => 'Belgilash katagi',
    ],
    'public' => [
        'submit' => 'Yuborish',
        'sent' => 'Rahmat! Xabaringiz yuborildi.',
        'too_fast' => 'Shakl juda tez yuborildi. Uni tekshirib, qayta yuboring.',
        'too_many' => 'Urinishlar juda ko‘p. Bir daqiqadan so‘ng qayta urinib ko‘ring.',
        'failed' => 'Shaklni yuborib bo‘lmadi. Ulanishni tekshirib, qayta urinib ko‘ring.',
        'close' => 'Yopish',
        'honeypot' => 'Bu maydonni bo‘sh qoldiring',
        'choose' => '— Tanlang —',
        'yes' => 'Ha',
        'no' => 'Yo‘q',
    ],
    'validation' => [
        'recipients' => '«:email» pochta manzili emas.',
    ],
    'mail' => [
        'subject' => 'Yangi ariza: :form',
        'heading' => 'Yangi ariza: :form',
        'page' => 'Yuborilgan sahifa',
    ],
];
