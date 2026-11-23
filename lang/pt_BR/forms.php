<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Formulários',
        'submissions' => 'Envios',
    ],
    'fields' => [
        'builder' => 'Campos',
        'id' => 'ID',
        'active' => 'Ativo',
        'name' => 'Nome',
        'code' => 'Código',
        'recipients' => 'Enviar os envios para',
        'button_label' => 'Texto do botão',
        'success_message' => 'Mensagem após o envio',
        'updated_at' => 'Atualizado',
        'submissions_count' => 'Envios',
        'form' => 'Formulário',
        'submitted_at' => 'Enviado',
        'content' => 'Conteúdo',
        'page_url' => 'Página',
        'locale' => 'Idioma',
        'ip' => 'Endereço IP',
        'user_agent' => 'Navegador',
    ],
    'hints' => [
        'builder' => 'Um campo ocupa de 1 a 12 colunas; os campos que cabem lado a lado dividem uma linha. No celular cada campo ocupa a linha inteira.',
        'code' => 'Letras latinas, números, "-" e "_". Em uma página: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Endereços de e-mail, um por linha ou separados por vírgulas. Vazio: os envios ficam guardados apenas aqui.',
    ],
    'types' => [
        'text' => 'Texto',
        'email' => 'E-mail',
        'tel' => 'Telefone',
        'number' => 'Número',
        'date' => 'Data',
        'textarea' => 'Texto longo',
        'select' => 'Lista',
        'radio' => 'Botões de opção',
        'checkbox' => 'Caixa de seleção',
    ],
    'snippets' => [
        'label' => 'No site',
        'blade' => 'Em um template Blade',
        'markup' => 'Marcação própria',
        'markup_note' => 'Para um template deste site: os campos ocultos levam o token e a proteção contra spam. Os textos estão no idioma principal do site.',
        'script_note' => 'Para enviar sem recarregar a página, adicione data-atrium-form ao <form> e inclua o script uma vez na página:',
        'fetch' => 'Envio por um script próprio',
        'fetch_note' => 'A mesma marcação, enviada com fetch:',
        'json' => 'O que o servidor responde',
        'json_note' => 'Com o captcha ativo, cada resposta traz "captcha", um novo no lugar do usado.',
    ],
    'public' => [
        'submit' => 'Enviar',
        'sent' => 'Obrigado! Sua mensagem foi enviada.',
        'too_fast' => 'O formulário foi enviado rápido demais. Verifique-o e envie novamente.',
        'too_many' => 'Tentativas demais. Tente novamente em um minuto.',
        'failed' => 'Não foi possível enviar o formulário. Verifique a conexão e tente novamente.',
        'close' => 'Fechar',
        'honeypot' => 'Deixe este campo vazio',
        'choose' => '— Escolha —',
        'yes' => 'Sim',
        'no' => 'Não',
    ],
    'validation' => [
        'recipients' => '":email" não é um endereço de e-mail.',
    ],
    'mail' => [
        'subject' => 'Novo envio: :form',
        'heading' => 'Novo envio: :form',
        'page' => 'Enviado de',
    ],
];
