<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Machine-translated from en/forms.php.
return [
    'modules' => [
        'forms' => 'Formlar',
        'submissions' => 'Gönderimler',
    ],
    'fields' => [
        'builder' => 'Alanlar',
        'id' => 'ID',
        'active' => 'Etkin',
        'name' => 'Ad',
        'code' => 'Kod',
        'recipients' => 'Gönderimlerin gideceği adresler',
        'button_label' => 'Düğme metni',
        'success_message' => 'Gönderimden sonraki mesaj',
        'updated_at' => 'Güncellendi',
        'submissions_count' => 'Gönderimler',
        'form' => 'Form',
        'submitted_at' => 'Gönderildi',
        'content' => 'İçerik',
        'page_url' => 'Sayfa',
        'locale' => 'Dil',
        'ip' => 'IP adresi',
        'user_agent' => 'Tarayıcı',
    ],
    'hints' => [
        'builder' => 'Bir alan 1 ile 12 sütun arasında yer kaplar; yan yana sığan alanlar aynı satırı paylaşır. Telefonda her alan tüm satırı kaplar.',
        'code' => 'Latin harfleri, rakamlar, "-" ve "_". Bir sayfada: <x-atrium-forms::form code="contact" />',
        'recipients' => 'E-posta adresleri, her satıra bir tane veya virgülle ayrılmış. Boş: gönderimler yalnızca burada saklanır.',
    ],
    'types' => [
        'text' => 'Metin',
        'email' => 'E-posta',
        'tel' => 'Telefon',
        'number' => 'Sayı',
        'date' => 'Tarih',
        'textarea' => 'Uzun metin',
        'select' => 'Liste',
        'radio' => 'Seçenek düğmeleri',
        'checkbox' => 'Onay kutusu',
    ],
    'snippets' => [
        'label' => 'Sitede',
        'blade' => 'Bir Blade şablonunda',
        'markup' => 'Kendi işaretlemeniz',
        'markup_note' => 'Bu sitenin bir şablonu için: gizli alanlar belirteci ve spam korumasını taşır. Metinler sitenin varsayılan dilindedir.',
        'script_note' => 'Sayfayı yenilemeden göndermek için <form> etiketine data-atrium-form ekleyin ve betiği sayfaya bir kez koyun:',
        'fetch' => 'Kendi betiğinizle gönderme',
        'fetch_note' => 'Aynı işaretleme, fetch ile gönderilir:',
        'json' => 'Sunucunun yanıtı',
        'json_note' => 'Captcha açıkken her yanıt "captcha" getirir: kullanılanın yerine yenisi.',
    ],
    'public' => [
        'submit' => 'Gönder',
        'sent' => 'Teşekkürler! Mesajınız gönderildi.',
        'too_fast' => 'Form çok hızlı gönderildi. Lütfen kontrol edip yeniden gönderin.',
        'too_many' => 'Çok fazla deneme. Lütfen bir dakika sonra tekrar deneyin.',
        'failed' => 'Form gönderilemedi. Bağlantınızı kontrol edip tekrar deneyin.',
        'close' => 'Kapat',
        'honeypot' => 'Bu alanı boş bırakın',
        'choose' => '— Seçin —',
        'yes' => 'Evet',
        'no' => 'Hayır',
    ],
    'validation' => [
        'recipients' => '":email" bir e-posta adresi değil.',
    ],
    'mail' => [
        'subject' => 'Yeni gönderim: :form',
        'heading' => 'Yeni gönderim: :form',
        'page' => 'Gönderildiği sayfa',
    ],
    'builder' => [
        'modal' => 'Bir düğmeyle pencerede aç',
    ],
];
