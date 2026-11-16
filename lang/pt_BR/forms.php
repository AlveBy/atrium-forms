<?php

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Formulários',
        'form_fields' => 'Campos dos formulários',
        'submissions' => 'Envios',
    ],
    'fields' => [
        'id' => 'ID',
        'active' => 'Ativo',
        'name' => 'Nome',
        'code' => 'Código',
        'recipients' => 'Enviar os envios para',
        'button_label' => 'Texto do botão',
        'success_message' => 'Mensagem após o envio',
        'updated_at' => 'Atualizado',
        'form' => 'Formulário',
        'label' => 'Rótulo',
        'field_name' => 'Código do campo',
        'type' => 'Tipo',
        'required' => 'Obrigatório',
        'options' => 'Opções',
        'sort' => 'Ordem',
        'submitted_at' => 'Enviado',
        'content' => 'Conteúdo',
        'page_url' => 'Página',
        'locale' => 'Idioma',
        'ip' => 'Endereço IP',
        'user_agent' => 'Navegador',
    ],
    'hints' => [
        'code' => 'Letras latinas, números, "-" e "_". Em uma página: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Endereços de e-mail, um por linha ou separados por vírgulas. Vazio: os envios ficam guardados apenas aqui.',
        'field_name' => 'Letras latinas, números, "-" e "_"; único no formulário.',
        'options' => 'Para uma lista ou botões de opção: uma opção por linha.',
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
    'public' => [
        'submit' => 'Enviar',
        'sent' => 'Obrigado! Sua mensagem foi enviada.',
        'too_fast' => 'O formulário foi enviado rápido demais. Verifique-o e envie novamente.',
        'too_many' => 'Tentativas demais. Tente novamente em um minuto.',
        'honeypot' => 'Deixe este campo vazio',
        'choose' => '— Escolha —',
        'yes' => 'Sim',
        'no' => 'Não',
    ],
    'validation' => [
        'recipients' => '":email" não é um endereço de e-mail.',
        'field_name_taken' => 'Este formulário já tem um campo com este código.',
    ],
    'mail' => [
        'subject' => 'Novo envio: :form',
        'heading' => 'Novo envio: :form',
        'page' => 'Enviado de',
    ],
];
