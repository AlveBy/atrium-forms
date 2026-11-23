<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

return [
    'modules' => [
        'forms' => 'Forms',
        'submissions' => 'Submissions',
    ],
    'fields' => [
        'builder' => 'Fields',
        'id' => 'ID',
        'active' => 'Active',
        'name' => 'Name',
        'code' => 'Code',
        'recipients' => 'Send submissions to',
        'button_label' => 'Button text',
        'success_message' => 'Message after sending',
        'updated_at' => 'Updated',
        'submissions_count' => 'Submissions',
        'form' => 'Form',
        'submitted_at' => 'Sent',
        'content' => 'Content',
        'page_url' => 'Page',
        'locale' => 'Language',
        'ip' => 'IP address',
        'user_agent' => 'Browser',
    ],
    'hints' => [
        'builder' => 'Each field takes 1 to 12 columns; fields that fit side by side share a row. On a phone every field takes the whole row.',
        'code' => 'Latin letters, digits, "-" and "_". On a page: <x-atrium-forms::form code="contact" />',
        'recipients' => 'E-mail addresses, one per line or separated by commas. Empty: submissions are only kept here.',
    ],
    'types' => [
        'text' => 'Text',
        'email' => 'E-mail',
        'tel' => 'Phone',
        'number' => 'Number',
        'date' => 'Date',
        'textarea' => 'Long text',
        'select' => 'List',
        'radio' => 'Radio buttons',
        'checkbox' => 'Checkbox',
    ],
    'public' => [
        'submit' => 'Send',
        'sent' => 'Thank you! Your message has been sent.',
        'too_fast' => 'The form was sent too quickly. Please check it and send it again.',
        'too_many' => 'Too many attempts. Please try again in a minute.',
        'honeypot' => 'Leave this field empty',
        'choose' => '— Choose —',
        'yes' => 'Yes',
        'no' => 'No',
    ],
    'validation' => [
        'recipients' => '":email" is not an e-mail address.',
    ],
    'mail' => [
        'subject' => 'New submission: :form',
        'heading' => 'New submission: :form',
        'page' => 'Sent from',
    ],
];
