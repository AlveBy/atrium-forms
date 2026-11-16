<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Formularze',
        'form_fields' => 'Pola formularzy',
        'submissions' => 'Zgłoszenia',
    ],
    'fields' => [
        'id' => 'ID',
        'active' => 'Aktywny',
        'name' => 'Nazwa',
        'code' => 'Kod',
        'recipients' => 'Wysyłaj zgłoszenia na',
        'button_label' => 'Tekst przycisku',
        'success_message' => 'Komunikat po wysłaniu',
        'updated_at' => 'Zmieniono',
        'submissions_count' => 'Zgłoszenia',
        'form' => 'Formularz',
        'label' => 'Etykieta',
        'field_name' => 'Kod pola',
        'type' => 'Typ',
        'required' => 'Wymagane',
        'options' => 'Opcje',
        'sort' => 'Sortowanie',
        'submitted_at' => 'Wysłano',
        'content' => 'Treść',
        'page_url' => 'Strona',
        'locale' => 'Język',
        'ip' => 'Adres IP',
        'user_agent' => 'Przeglądarka',
    ],
    'hints' => [
        'code' => 'Litery łacińskie, cyfry, „-” i „_”. Na stronie: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Adresy e-mail, po jednym w wierszu lub oddzielone przecinkami. Puste: zgłoszenia są tylko przechowywane tutaj.',
        'field_name' => 'Litery łacińskie, cyfry, „-” i „_”; unikalny w formularzu.',
        'options' => 'Dla listy lub przycisków opcji: jedna opcja w wierszu.',
    ],
    'types' => [
        'text' => 'Tekst',
        'email' => 'E-mail',
        'tel' => 'Telefon',
        'number' => 'Liczba',
        'date' => 'Data',
        'textarea' => 'Długi tekst',
        'select' => 'Lista',
        'radio' => 'Przyciski opcji',
        'checkbox' => 'Pole wyboru',
    ],
    'public' => [
        'submit' => 'Wyślij',
        'sent' => 'Dziękujemy! Wiadomość została wysłana.',
        'too_fast' => 'Formularz został wysłany zbyt szybko. Sprawdź go i wyślij ponownie.',
        'too_many' => 'Zbyt wiele prób. Spróbuj ponownie za minutę.',
        'honeypot' => 'Zostaw to pole puste',
        'choose' => '— Wybierz —',
        'yes' => 'Tak',
        'no' => 'Nie',
    ],
    'validation' => [
        'recipients' => '„:email” nie jest adresem e-mail.',
        'field_name_taken' => 'Ten formularz ma już pole z tym kodem.',
    ],
    'mail' => [
        'subject' => 'Nowe zgłoszenie: :form',
        'heading' => 'Nowe zgłoszenie: :form',
        'page' => 'Wysłano ze strony',
    ],
];
