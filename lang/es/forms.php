<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Formularios',
        'form_fields' => 'Campos de formularios',
        'submissions' => 'Envíos',
    ],
    'fields' => [
        'id' => 'ID',
        'active' => 'Activo',
        'name' => 'Nombre',
        'code' => 'Código',
        'recipients' => 'Enviar los envíos a',
        'button_label' => 'Texto del botón',
        'success_message' => 'Mensaje tras el envío',
        'updated_at' => 'Actualizado',
        'submissions_count' => 'Envíos',
        'form' => 'Formulario',
        'label' => 'Etiqueta',
        'field_name' => 'Código del campo',
        'type' => 'Tipo',
        'required' => 'Obligatorio',
        'options' => 'Opciones',
        'sort' => 'Orden',
        'submitted_at' => 'Enviado',
        'content' => 'Contenido',
        'page_url' => 'Página',
        'locale' => 'Idioma',
        'ip' => 'Dirección IP',
        'user_agent' => 'Navegador',
    ],
    'hints' => [
        'code' => 'Letras latinas, cifras, «-» y «_». En una página: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Direcciones de correo, una por línea o separadas por comas. Vacío: los envíos solo se guardan aquí.',
        'field_name' => 'Letras latinas, cifras, «-» y «_»; único dentro del formulario.',
        'options' => 'Para una lista o botones de opción: una opción por línea.',
    ],
    'types' => [
        'text' => 'Texto',
        'email' => 'Correo electrónico',
        'tel' => 'Teléfono',
        'number' => 'Número',
        'date' => 'Fecha',
        'textarea' => 'Texto largo',
        'select' => 'Lista',
        'radio' => 'Botones de opción',
        'checkbox' => 'Casilla',
    ],
    'public' => [
        'submit' => 'Enviar',
        'sent' => '¡Gracias! Su mensaje ha sido enviado.',
        'too_fast' => 'El formulario se envió demasiado rápido. Revíselo y envíelo de nuevo.',
        'too_many' => 'Demasiados intentos. Inténtelo de nuevo en un minuto.',
        'honeypot' => 'Deje este campo vacío',
        'choose' => '— Elija —',
        'yes' => 'Sí',
        'no' => 'No',
    ],
    'validation' => [
        'recipients' => '«:email» no es una dirección de correo.',
        'field_name_taken' => 'Este formulario ya tiene un campo con este código.',
    ],
    'mail' => [
        'subject' => 'Nuevo envío: :form',
        'heading' => 'Nuevo envío: :form',
        'page' => 'Enviado desde',
    ],
];
