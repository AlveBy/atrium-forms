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
        'submissions' => 'Zgłoszenia',
    ],
    'fields' => [
        'builder' => 'Pola',
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
        'submitted_at' => 'Wysłano',
        'content' => 'Treść',
        'page_url' => 'Strona',
        'locale' => 'Język',
        'ip' => 'Adres IP',
        'user_agent' => 'Przeglądarka',
    ],
    'hints' => [
        'builder' => 'Pole zajmuje od 1 do 12 kolumn; pola, które mieszczą się obok siebie, dzielą wiersz. Na telefonie każde pole zajmuje cały wiersz.',
        'code' => 'Litery łacińskie, cyfry, „-” i „_”. Na stronie: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Adresy e-mail, po jednym w wierszu lub oddzielone przecinkami. Puste: zgłoszenia są tylko przechowywane tutaj.',
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
        'failed' => 'Nie udało się wysłać formularza. Sprawdź połączenie i spróbuj ponownie.',
        'close' => 'Zamknij',
        'honeypot' => 'Zostaw to pole puste',
        'choose' => '— Wybierz —',
        'yes' => 'Tak',
        'no' => 'Nie',
    ],
    'validation' => [
        'recipients' => '„:email” nie jest adresem e-mail.',
    ],
    'mail' => [
        'subject' => 'Nowe zgłoszenie: :form',
        'heading' => 'Nowe zgłoszenie: :form',
        'page' => 'Wysłano ze strony',
    ],
];
