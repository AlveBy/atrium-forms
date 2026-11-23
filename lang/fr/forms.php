<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Formulaires',
        'submissions' => 'Envois',
    ],
    'fields' => [
        'builder' => 'Champs',
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
        'submitted_at' => 'Envoyé',
        'content' => 'Contenu',
        'page_url' => 'Page',
        'locale' => 'Langue',
        'ip' => 'Adresse IP',
        'user_agent' => 'Navigateur',
    ],
    'hints' => [
        'builder' => 'Un champ occupe de 1 à 12 colonnes ; les champs qui tiennent côte à côte partagent une ligne. Sur un téléphone, chaque champ prend toute la ligne.',
        'code' => 'Lettres latines, chiffres, « - » et « _ ». Sur une page : <x-atrium-forms::form code="contact" />',
        'recipients' => 'Adresses e-mail, une par ligne ou séparées par des virgules. Vide : les envois sont seulement conservés ici.',
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
    ],
    'mail' => [
        'subject' => 'Nouvel envoi : :form',
        'heading' => 'Nouvel envoi : :form',
        'page' => 'Envoyé depuis',
    ],
];
