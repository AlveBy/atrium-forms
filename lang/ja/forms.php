<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'フォーム',
        'form_fields' => 'フォームの項目',
        'submissions' => '送信内容',
    ],
    'fields' => [
        'id' => 'ID',
        'active' => '有効',
        'name' => '名前',
        'code' => 'コード',
        'recipients' => '送信先',
        'button_label' => 'ボタンの文言',
        'success_message' => '送信後のメッセージ',
        'updated_at' => '更新日',
        'submissions_count' => '送信数',
        'form' => 'フォーム',
        'label' => 'ラベル',
        'field_name' => '項目コード',
        'type' => '種類',
        'required' => '必須',
        'options' => '選択肢',
        'sort' => '並び順',
        'submitted_at' => '送信日時',
        'content' => '内容',
        'page_url' => 'ページ',
        'locale' => '言語',
        'ip' => 'IPアドレス',
        'user_agent' => 'ブラウザ',
    ],
    'hints' => [
        'code' => '半角英字、数字、「-」と「_」。ページでは: <x-atrium-forms::form code="contact" />',
        'recipients' => 'メールアドレス。1行に1つ、またはカンマ区切り。空欄の場合、送信内容はここに保存されるだけです。',
        'field_name' => '半角英字、数字、「-」と「_」。フォーム内で一意。',
        'options' => 'リストまたはラジオボタン用: 1行に1つの選択肢。',
    ],
    'types' => [
        'text' => 'テキスト',
        'email' => 'メール',
        'tel' => '電話',
        'number' => '数値',
        'date' => '日付',
        'textarea' => '長文',
        'select' => 'リスト',
        'radio' => 'ラジオボタン',
        'checkbox' => 'チェックボックス',
    ],
    'public' => [
        'submit' => '送信',
        'sent' => 'ありがとうございます。メッセージを送信しました。',
        'too_fast' => 'フォームの送信が早すぎます。内容を確認して、もう一度送信してください。',
        'too_many' => '試行回数が多すぎます。1分後にもう一度お試しください。',
        'honeypot' => 'この欄は空のままにしてください',
        'choose' => '— 選択 —',
        'yes' => 'はい',
        'no' => 'いいえ',
    ],
    'validation' => [
        'recipients' => '「:email」はメールアドレスではありません。',
        'field_name_taken' => 'このフォームには同じコードの項目がすでにあります。',
    ],
    'mail' => [
        'subject' => '新しい送信: :form',
        'heading' => '新しい送信: :form',
        'page' => '送信元',
    ],
];
