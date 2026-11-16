# Atrium forms

Forms and submissions for [Atrium](https://github.com/AlveBy/atrium): an editor builds a form in the admin, the site shows it with one Blade tag, and what visitors send is kept in the admin and mailed to the addresses set on the form. Spam is stopped without an outside service.

## Install

```bash
composer require alveby/atrium-forms
php artisan migrate
```

The admin gets three entries:

| Entry | What it holds |
|---|---|
| Forms | name, code, the addresses submissions are mailed to, the button text and the message after sending (both per content language), on/off |
| Form fields | the form, label (per content language), field code, type, required, options, sort |
| Submissions | what was sent, with the form, page, language, IP address and browser; read and deleted, never edited |

Forms belong to sites: each site has its own, and the lists show the current site's forms, fields and submissions. Each entry has the usual four permissions in the role form; give an editor `form_submissions.view` to read the submissions.

Field types: text, e-mail, phone, number, date, long text, list, radio buttons, checkbox. A list and radio buttons take their options one per line, in each language. The rules follow the type: a valid e-mail, a phone of digits and `+()-.`, one of the options, a required checkbox must be ticked.

## On a page

```blade
<x-atrium-forms::form code="contact" />
```

draws the form of the current site with that code, in the page's language, or nothing when there is none or it is off. Attributes pass to the `<form>` tag (`class` is added to `atrium-form`). The form posts to `/forms/{code}` (`/de/forms/{code}` in another language) and comes back to the same page, scrolled to the form, with the message or the errors; several forms on one page keep their own errors and input. A request that wants JSON gets `{"message": "…"}` or the usual 422 with `errors`.

The markup is plain HTML with `atrium-form*` classes and a few styles that take the colours of `alveby/atrium-theme`. To change it, `php artisan vendor:publish --tag=atrium-forms-views`.

## Spam and limits

- A hidden field people never see and bots fill in. A submission with it filled is answered as sent and dropped.
- The time the form was drawn, encrypted with the app key. A form sent sooner than `min_seconds` (3) after that, or without the time, is refused with "sent too quickly".
- At most `per_minute` (5) submissions of one form from one IP address per minute.

`php artisan vendor:publish --tag=atrium-forms-config` copies `config/atrium-forms.php` to change these, the honeypot field name and the address prefix; `ATRIUM_FORMS_MIN_SECONDS` and `ATRIUM_FORMS_PER_MINUTE` work without it.

## Mail

Each submission goes to every address of its form through Laravel's mail (`MAIL_*` in `.env`, the same that sends the admin's sign-in codes). "Reply" answers the visitor when the form has an e-mail field. A form without addresses only keeps submissions in the admin. A mail server that is down does not lose a submission: it is stored first, and the error goes to the log.

To do more with a submission (a CRM, a chat), listen to the model's event:

```php
use Atrium\Forms\Models\FormSubmission;

FormSubmission::created(function (FormSubmission $submission) {
    // $submission->form_name, $submission->data: [['name' => 'email', 'type' => 'email', 'label' => 'E-mail', 'value' => '…'], …]
});
```

The labels are kept in the site's default language as they were when the form was sent, so a submission reads the same after the form changes or is deleted.

## Languages

The admin's 15 languages. English and Russian are written by hand, the rest are machine translations awaiting native review.

## Tests

From the repository root:

```bash
vendor/bin/phpunit packages/forms/tests
```

## Contributing

This repository is a read-only copy of `packages/forms` in [AlveBy/atrium](https://github.com/AlveBy/atrium). Issues and pull requests go there.

## License

MIT
