<?php

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => '表单',
        'form_fields' => '表单字段',
        'submissions' => '提交记录',
    ],
    'fields' => [
        'id' => 'ID',
        'active' => '启用',
        'name' => '名称',
        'code' => '代码',
        'recipients' => '提交发送至',
        'button_label' => '按钮文字',
        'success_message' => '提交后的提示',
        'updated_at' => '更新时间',
        'form' => '表单',
        'label' => '标签',
        'field_name' => '字段代码',
        'type' => '类型',
        'required' => '必填',
        'options' => '选项',
        'sort' => '排序',
        'submitted_at' => '提交时间',
        'content' => '内容',
        'page_url' => '页面',
        'locale' => '语言',
        'ip' => 'IP 地址',
        'user_agent' => '浏览器',
    ],
    'hints' => [
        'code' => '拉丁字母、数字、“-”和“_”。在页面中: <x-atrium-forms::form code="contact" />',
        'recipients' => '电子邮件地址，每行一个或用逗号分隔。留空则提交只保存在这里。',
        'field_name' => '拉丁字母、数字、“-”和“_”；在表单内唯一。',
        'options' => '用于列表或单选按钮：每行一个选项。',
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
        'field_name_taken' => '此表单已有使用该代码的字段。',
    ],
    'mail' => [
        'subject' => '新提交：:form',
        'heading' => '新提交：:form',
        'page' => '提交页面',
    ],
];
