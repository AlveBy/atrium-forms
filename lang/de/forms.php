<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Formulare',
        'submissions' => 'Einsendungen',
    ],
    'fields' => [
        'builder' => 'Felder',
        'id' => 'ID',
        'active' => 'Aktiv',
        'name' => 'Name',
        'code' => 'Code',
        'recipients' => 'Einsendungen senden an',
        'button_label' => 'Text der Schaltfläche',
        'success_message' => 'Nachricht nach dem Senden',
        'updated_at' => 'Geändert',
        'submissions_count' => 'Einsendungen',
        'form' => 'Formular',
        'submitted_at' => 'Gesendet',
        'content' => 'Inhalt',
        'page_url' => 'Seite',
        'locale' => 'Sprache',
        'ip' => 'IP-Adresse',
        'user_agent' => 'Browser',
    ],
    'hints' => [
        'builder' => 'Ein Feld nimmt 1 bis 12 Spalten ein; Felder, die nebeneinander passen, teilen sich eine Zeile. Auf dem Telefon nimmt jedes Feld die ganze Zeile ein.',
        'code' => 'Lateinische Buchstaben, Ziffern, „-“ und „_“. Auf einer Seite: <x-atrium-forms::form code="contact" />',
        'recipients' => 'E-Mail-Adressen, eine pro Zeile oder durch Kommas getrennt. Leer: Einsendungen werden nur hier gespeichert.',
    ],
    'types' => [
        'text' => 'Text',
        'email' => 'E-Mail',
        'tel' => 'Telefon',
        'number' => 'Zahl',
        'date' => 'Datum',
        'textarea' => 'Langer Text',
        'select' => 'Liste',
        'radio' => 'Optionsfelder',
        'checkbox' => 'Kontrollkästchen',
    ],
    'public' => [
        'submit' => 'Senden',
        'sent' => 'Vielen Dank! Ihre Nachricht wurde gesendet.',
        'too_fast' => 'Das Formular wurde zu schnell gesendet. Bitte prüfen Sie es und senden Sie es erneut.',
        'too_many' => 'Zu viele Versuche. Bitte versuchen Sie es in einer Minute erneut.',
        'failed' => 'Das Formular konnte nicht gesendet werden. Bitte prüfen Sie die Verbindung und versuchen Sie es erneut.',
        'close' => 'Schließen',
        'honeypot' => 'Dieses Feld leer lassen',
        'choose' => '— Auswählen —',
        'yes' => 'Ja',
        'no' => 'Nein',
    ],
    'validation' => [
        'recipients' => '„:email“ ist keine E-Mail-Adresse.',
    ],
    'mail' => [
        'subject' => 'Neue Einsendung: :form',
        'heading' => 'Neue Einsendung: :form',
        'page' => 'Gesendet von',
    ],
];
