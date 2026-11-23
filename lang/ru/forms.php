<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

return [
    'modules' => [
        'forms' => 'Формы',
        'submissions' => 'Заявки',
    ],
    'fields' => [
        'builder' => 'Поля',
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
        'submitted_at' => 'Отправлена',
        'content' => 'Содержание',
        'page_url' => 'Страница',
        'locale' => 'Язык',
        'ip' => 'IP-адрес',
        'user_agent' => 'Браузер',
    ],
    'hints' => [
        'builder' => 'Поле занимает от 1 до 12 колонок; поля, которые помещаются рядом, встают в одну строку. На телефоне каждое поле — во всю строку.',
        'code' => 'Латинские буквы, цифры, «-» и «_». На странице: <x-atrium-forms::form code="contact" />',
        'recipients' => 'Адреса почты, по одному в строке или через запятую. Пусто — заявки только хранятся здесь.',
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
    ],
    'mail' => [
        'subject' => 'Новая заявка: :form',
        'heading' => 'Новая заявка: :form',
        'page' => 'Отправлена со страницы',
    ],
];
