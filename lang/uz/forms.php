<?php

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Shakllar',
        'form_fields' => 'Shakl maydonlari',
        'submissions' => 'Arizalar',
    ],
    'fields' => [
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
        'label' => 'Yorliq',
        'field_name' => 'Maydon kodi',
        'type' => 'Turi',
        'required' => 'Majburiy',
        'options' => 'Variantlar',
        'sort' => 'Saralash',
        'submitted_at' => 'Yuborilgan',
        'content' => 'Mazmuni',
        'page_url' => 'Sahifa',
        'locale' => 'Til',
        'ip' => 'IP manzil',
        'user_agent' => 'Brauzer',
    ],
    'hints' => [
        'code' => 'Lotin harflari, raqamlar, «-» va «_». Sahifada: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Pochta manzillari, har qatorda bittadan yoki vergul bilan. Bo‘sh bo‘lsa — arizalar faqat shu yerda saqlanadi.',
        'field_name' => 'Lotin harflari, raqamlar, «-» va «_»; shaklda takrorlanmaydi.',
        'options' => 'Ro‘yxat va almashtirgichlar uchun: har qatorda bitta variant.',
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
        'honeypot' => 'Bu maydonni bo‘sh qoldiring',
        'choose' => '— Tanlang —',
        'yes' => 'Ha',
        'no' => 'Yo‘q',
    ],
    'validation' => [
        'recipients' => '«:email» pochta manzili emas.',
        'field_name_taken' => 'Bu shaklda shu kodli maydon allaqachon bor.',
    ],
    'mail' => [
        'subject' => 'Yangi ariza: :form',
        'heading' => 'Yangi ariza: :form',
        'page' => 'Yuborilgan sahifa',
    ],
];
