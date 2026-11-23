<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Moduli',
        'submissions' => 'Invii',
    ],
    'fields' => [
        'builder' => 'Campi',
        'id' => 'ID',
        'active' => 'Attivo',
        'name' => 'Nome',
        'code' => 'Codice',
        'recipients' => 'Invia gli invii a',
        'button_label' => 'Testo del pulsante',
        'success_message' => 'Messaggio dopo l’invio',
        'updated_at' => 'Modificato',
        'submissions_count' => 'Invii',
        'form' => 'Modulo',
        'submitted_at' => 'Inviato',
        'content' => 'Contenuto',
        'page_url' => 'Pagina',
        'locale' => 'Lingua',
        'ip' => 'Indirizzo IP',
        'user_agent' => 'Browser',
    ],
    'hints' => [
        'builder' => 'Un campo occupa da 1 a 12 colonne; i campi che stanno affiancati condividono una riga. Su un telefono ogni campo occupa tutta la riga.',
        'code' => 'Lettere latine, cifre, «-» e «_». In una pagina: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Indirizzi e-mail, uno per riga o separati da virgole. Vuoto: gli invii sono solo conservati qui.',
    ],
    'types' => [
        'text' => 'Testo',
        'email' => 'E-mail',
        'tel' => 'Telefono',
        'number' => 'Numero',
        'date' => 'Data',
        'textarea' => 'Testo lungo',
        'select' => 'Elenco',
        'radio' => 'Pulsanti di opzione',
        'checkbox' => 'Casella di controllo',
    ],
    'public' => [
        'submit' => 'Invia',
        'sent' => 'Grazie! Il tuo messaggio è stato inviato.',
        'too_fast' => 'Il modulo è stato inviato troppo in fretta. Controllalo e invialo di nuovo.',
        'too_many' => 'Troppi tentativi. Riprova tra un minuto.',
        'honeypot' => 'Lascia vuoto questo campo',
        'choose' => '— Scegli —',
        'yes' => 'Sì',
        'no' => 'No',
    ],
    'validation' => [
        'recipients' => '«:email» non è un indirizzo e-mail.',
    ],
    'mail' => [
        'subject' => 'Nuovo invio: :form',
        'heading' => 'Nuovo invio: :form',
        'page' => 'Inviato da',
    ],
];
