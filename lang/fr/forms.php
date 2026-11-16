<?php

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Formulaires',
        'form_fields' => 'Champs des formulaires',
        'submissions' => 'Envois',
    ],
    'fields' => [
        'id' => 'ID',
        'active' => 'Actif',
        'name' => 'Nom',
        'code' => 'Code',
        'recipients' => 'Envoyer les envois à',
        'button_label' => 'Texte du bouton',
        'success_message' => 'Message après l’envoi',
        'updated_at' => 'Modifié',
        'submissions_count' => 'Envois',
        'form' => 'Formulaire',
        'label' => 'Libellé',
        'field_name' => 'Code du champ',
        'type' => 'Type',
        'required' => 'Obligatoire',
        'options' => 'Options',
        'sort' => 'Tri',
        'submitted_at' => 'Envoyé',
        'content' => 'Contenu',
        'page_url' => 'Page',
        'locale' => 'Langue',
        'ip' => 'Adresse IP',
        'user_agent' => 'Navigateur',
    ],
    'hints' => [
        'code' => 'Lettres latines, chiffres, « - » et « _ ». Sur une page : <x-atrium-forms::form code="contact" />',
        'recipients' => 'Adresses e-mail, une par ligne ou séparées par des virgules. Vide : les envois sont seulement conservés ici.',
        'field_name' => 'Lettres latines, chiffres, « - » et « _ » ; unique dans le formulaire.',
        'options' => 'Pour une liste ou des boutons radio : une option par ligne.',
    ],
    'types' => [
        'text' => 'Texte',
        'email' => 'E-mail',
        'tel' => 'Téléphone',
        'number' => 'Nombre',
        'date' => 'Date',
        'textarea' => 'Texte long',
        'select' => 'Liste',
        'radio' => 'Boutons radio',
        'checkbox' => 'Case à cocher',
    ],
    'public' => [
        'submit' => 'Envoyer',
        'sent' => 'Merci ! Votre message a été envoyé.',
        'too_fast' => 'Le formulaire a été envoyé trop vite. Vérifiez-le et envoyez-le à nouveau.',
        'too_many' => 'Trop de tentatives. Réessayez dans une minute.',
        'honeypot' => 'Laissez ce champ vide',
        'choose' => '— Choisir —',
        'yes' => 'Oui',
        'no' => 'Non',
    ],
    'validation' => [
        'recipients' => '« :email » n’est pas une adresse e-mail.',
        'field_name_taken' => 'Ce formulaire a déjà un champ avec ce code.',
    ],
    'mail' => [
        'subject' => 'Nouvel envoi : :form',
        'heading' => 'Nouvel envoi : :form',
        'page' => 'Envoyé depuis',
    ],
];
