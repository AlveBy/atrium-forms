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
        'submissions' => 'Envíos',
    ],
    'fields' => [
        'builder' => 'Campos',
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
        'submitted_at' => 'Enviado',
        'content' => 'Contenido',
        'page_url' => 'Página',
        'locale' => 'Idioma',
        'ip' => 'Dirección IP',
        'user_agent' => 'Navegador',
    ],
    'hints' => [
        'builder' => 'Un campo ocupa de 1 a 12 columnas; los campos que caben uno al lado del otro comparten fila. En un teléfono cada campo ocupa toda la fila.',
        'code' => 'Letras latinas, cifras, «-» y «_». En una página: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Direcciones de correo, una por línea o separadas por comas. Vacío: los envíos solo se guardan aquí.',
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
    'snippets' => [
        'label' => 'En el sitio',
        'blade' => 'En una plantilla Blade',
        'markup' => 'Marcado propio',
        'markup_note' => 'Para una plantilla de este sitio: los campos ocultos llevan el token y la protección contra spam. Los textos están en el idioma principal del sitio.',
        'script_note' => 'Para enviarlo sin recargar la página, añada data-atrium-form al <form> e incluya el script una vez en la página:',
        'fetch' => 'Envío con un script propio',
        'fetch_note' => 'El mismo marcado, enviado con fetch:',
        'json' => 'Qué responde el servidor',
        'json_note' => 'Con el captcha activado, cada respuesta trae "captcha", uno nuevo en lugar del usado.',
    ],
    'public' => [
        'submit' => 'Enviar',
        'sent' => '¡Gracias! Su mensaje ha sido enviado.',
        'too_fast' => 'El formulario se envió demasiado rápido. Revíselo y envíelo de nuevo.',
        'too_many' => 'Demasiados intentos. Inténtelo de nuevo en un minuto.',
        'failed' => 'No se pudo enviar el formulario. Compruebe la conexión e inténtelo de nuevo.',
        'close' => 'Cerrar',
        'honeypot' => 'Deje este campo vacío',
        'choose' => '— Elija —',
        'yes' => 'Sí',
        'no' => 'No',
    ],
    'validation' => [
        'recipients' => '«:email» no es una dirección de correo.',
    ],
    'mail' => [
        'subject' => 'Nuevo envío: :form',
        'heading' => 'Nuevo envío: :form',
        'page' => 'Enviado desde',
    ],
];
