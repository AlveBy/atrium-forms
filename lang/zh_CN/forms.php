<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => '表单',
        'submissions' => '提交记录',
    ],
    'fields' => [
        'builder' => '字段',
        'id' => 'ID',
        'active' => '启用',
        'name' => '名称',
        'code' => '代码',
        'recipients' => '提交发送至',
        'button_label' => '按钮文字',
        'success_message' => '提交后的提示',
        'updated_at' => '更新时间',
        'submissions_count' => '提交数',
        'form' => '表单',
        'submitted_at' => '提交时间',
        'content' => '内容',
        'page_url' => '页面',
        'locale' => '语言',
        'ip' => 'IP 地址',
        'user_agent' => '浏览器',
    ],
    'hints' => [
        'builder' => '每个字段占 1 到 12 列；放得下的字段并排在同一行。在手机上每个字段占满整行。',
        'code' => '拉丁字母、数字、“-”和“_”。在页面中: <x-atrium-forms::form code="contact" />',
        'recipients' => '电子邮件地址，每行一个或用逗号分隔。留空则提交只保存在这里。',
    ],
    'types' => [
        'text' => '文本',
        'email' => '电子邮件',
        'tel' => '电话',
        'number' => '数字',
        'date' => '日期',
        'textarea' => '长文本',
        'select' => '列表',
        'radio' => '单选按钮',
        'checkbox' => '复选框',
    ],
    'public' => [
        'submit' => '提交',
        'sent' => '谢谢！您的消息已发送。',
        'too_fast' => '表单提交过快。请检查后重新提交。',
        'too_many' => '尝试次数过多。请一分钟后再试。',
        'honeypot' => '请将此字段留空',
        'choose' => '— 请选择 —',
        'yes' => '是',
        'no' => '否',
    ],
    'validation' => [
        'recipients' => '“:email”不是电子邮件地址。',
    ],
    'mail' => [
        'subject' => '新提交：:form',
        'heading' => '新提交：:form',
        'page' => '提交页面',
    ],
];
