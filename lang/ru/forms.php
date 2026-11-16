<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

return [
    'modules' => [
        'forms' => 'Формы',
        'form_fields' => 'Поля форм',
        'submissions' => 'Заявки',
    ],
    'fields' => [
        'id' => 'ID',
        'active' => 'Активна',
        'name' => 'Название',
        'code' => 'Код',
        'recipients' => 'Куда отправлять заявки',
        'button_label' => 'Текст кнопки',
        'success_message' => 'Сообщение после отправки',
        'updated_at' => 'Изменена',
        'submissions_count' => 'Заявок',
        'form' => 'Форма',
        'label' => 'Подпись',
        'field_name' => 'Код поля',
        'type' => 'Тип',
        'required' => 'Обязательное',
        'options' => 'Варианты',
        'sort' => 'Сортировка',
        'submitted_at' => 'Отправлена',
        'content' => 'Содержание',
        'page_url' => 'Страница',
        'locale' => 'Язык',
        'ip' => 'IP-адрес',
        'user_agent' => 'Браузер',
    ],
    'hints' => [
        'code' => 'Латинские буквы, цифры, «-» и «_». На странице: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Адреса почты, по одному в строке или через запятую. Пусто — заявки только хранятся здесь.',
        'field_name' => 'Латинские буквы, цифры, «-» и «_»; в форме не повторяется.',
        'options' => 'Для списка и переключателей: по одному варианту в строке.',
    ],
    'types' => [
        'text' => 'Строка',
        'email' => 'E-mail',
        'tel' => 'Телефон',
        'number' => 'Число',
        'date' => 'Дата',
        'textarea' => 'Текст',
        'select' => 'Список',
        'radio' => 'Переключатели',
        'checkbox' => 'Флажок',
    ],
    'public' => [
        'submit' => 'Отправить',
        'sent' => 'Спасибо! Сообщение отправлено.',
        'too_fast' => 'Форма отправлена слишком быстро. Проверьте её и отправьте ещё раз.',
        'too_many' => 'Слишком много попыток. Попробуйте через минуту.',
        'honeypot' => 'Оставьте это поле пустым',
        'choose' => '— Выберите —',
        'yes' => 'Да',
        'no' => 'Нет',
    ],
    'validation' => [
        'recipients' => '«:email» — не адрес почты.',
        'field_name_taken' => 'В этой форме уже есть поле с таким кодом.',
    ],
    'mail' => [
        'subject' => 'Новая заявка: :form',
        'heading' => 'Новая заявка: :form',
        'page' => 'Отправлена со страницы',
    ],
];
