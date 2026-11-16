<?php

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Moduli',
        'form_fields' => 'Campi dei moduli',
        'submissions' => 'Invii',
    ],
    'fields' => [
        'id' => 'ID',
        'active' => 'Attivo',
        'name' => 'Nome',
        'code' => 'Codice',
        'recipients' => 'Invia gli invii a',
        'button_label' => 'Testo del pulsante',
        'success_message' => 'Messaggio dopo l’invio',
        'updated_at' => 'Modificato',
        'form' => 'Modulo',
        'label' => 'Etichetta',
        'field_name' => 'Codice del campo',
        'type' => 'Tipo',
        'required' => 'Obbligatorio',
        'options' => 'Opzioni',
        'sort' => 'Ordinamento',
        'submitted_at' => 'Inviato',
        'content' => 'Contenuto',
        'page_url' => 'Pagina',
        'locale' => 'Lingua',
        'ip' => 'Indirizzo IP',
        'user_agent' => 'Browser',
    ],
    'hints' => [
        'code' => 'Lettere latine, cifre, «-» e «_». In una pagina: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Indirizzi e-mail, uno per riga o separati da virgole. Vuoto: gli invii sono solo conservati qui.',
        'field_name' => 'Lettere latine, cifre, «-» e «_»; unico nel modulo.',
        'options' => 'Per un elenco o pulsanti di opzione: un’opzione per riga.',
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
        'field_name_taken' => 'Questo modulo ha già un campo con questo codice.',
    ],
    'mail' => [
        'subject' => 'Nuovo invio: :form',
        'heading' => 'Nuovo invio: :form',
        'page' => 'Inviato da',
    ],
];
