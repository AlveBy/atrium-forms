# Atrium forms

Forms and submissions for [Atrium](https://github.com/AlveBy/atrium): an editor builds a form in the admin, the site shows it with one Blade tag, and what visitors send is kept in the admin and mailed to the addresses set on the form. Spam is stopped without an outside service.

## Install

```bash
composer require alveby/atrium-forms
php artisan atrium:module:install alveby/atrium-forms
```

`atrium:module:install` creates the tables and turns the module on; `atrium:module:remove alveby/atrium-forms` turns it off and asks whether to keep the forms and submissions.

The admin gets two entries:

| Entry | What it holds |
|---|---|
| Forms | name, code, the addresses submissions are mailed to, the button text and the message after sending (both per content language), on/off, and the fields; the list shows how many submissions each form has |
| Submissions | what was sent, with the form, page, language, IP address and browser; read and deleted, never edited |

The number of submissions is kept on the form (`forms.submissions_count`): it goes up when a submission is stored and down when one is deleted in the admin, so the list does not count them on every page.

The fields are built on the form's own page: pick a type to add one, drag it into place, open it to set the label, code, type, required, options, the text shown in an empty field, a hint under it, the least and most (characters for text, the value for a number) and its width, from the whole row down to 1 of 12 columns. Fields side by side share a row on a computer and stand one under another on a phone; the preview beside them shows both. Saving keeps a field's id while it stays in the list, so its column in the submissions stays too.

Forms belong to sites: each site has its own, and the lists show the current site's forms and submissions. Each entry has the usual four permissions in the role form; give an editor `form_submissions.view` to read the submissions.

Field types: text, e-mail, phone, number, date, long text, list, radio buttons, checkbox. A list and radio buttons take their options one per line, in each language. The rules follow the type: a valid e-mail, a phone of digits and `+()-.`, one of the options, a required checkbox must be ticked.

## On a page

```blade
<x-atrium-forms::form code="contact" />
```

draws the form of the current site with that code, in the page's language, or nothing when there is none or it is off. Attributes pass to the `<form>` tag (`class` is added to `atrium-form`). The form posts to `/forms/{code}` (`/de/forms/{code}` in another language) and comes back to the same page, scrolled to the form, with the message or the errors; several forms on one page keep their own errors and input.

### Without reloading the page, and in a window

```blade
<x-atrium-forms::form code="contact" ajax />
<x-atrium-forms::form code="contact" modal trigger="Write to us" title="Ask a question" />
```

`ajax` sends the form with `fetch`: no reload, the errors under their fields (`aria-invalid` and `aria-describedby` set, the first wrong field focused), the message after sending in a live region, the button off while it is on its way so a double click sends once, and a new captcha after every answer. `modal` draws a button (`trigger`, the form's name by default) that opens the form in a `<dialog>` (`title`, the form's name by default): focus goes to the first field and stays inside the window, Escape and the close button shut it and give the focus back to the button. Without JavaScript the button is a link to the form's own page, `/forms/{code}`, which is kept out of search engines. The anti-spam is the same as for a plain form.

The same in one line, for a page that is not a component:

```blade
@atriumForm('contact')
@atriumForm('contact', ['modal' => true, 'trigger' => 'Write to us', 'class' => 'wide'])
```

`ajax`, `modal`, `trigger` and `title` are the component's, the rest go to the `<form>` tag.

Both bring a dependency-free script, `/forms/script.js?v=…` (8 KB, under 3 KB compressed, cached for a year by its version), added once per page however many forms there are. It can be put on a page by hand too: a `<form data-atrium-form>` is sent the same way, and an element with `data-atrium-form-open="contact"` opens `<dialog data-atrium-dialog="contact">`. The form says how it went with the `atrium-form:sent` (`detail.message`) and `atrium-form:failed` (`detail.status`, `detail.errors`) events, and `window.AtriumForms.open('contact')` opens a window from your own code.

A request that wants JSON gets, on success, `{"message": "…"}`, and on an error 422 `{"message": "…", "errors": {"fields.email": ["…"], …}}`; 429 when the limit is reached. With a captcha on, both answers carry `"captcha"`, the HTML of a new one.

The markup is plain HTML with `atrium-form*` classes and a few styles that take the colours of `alveby/atrium-theme`. To change it, `php artisan vendor:publish --tag=atrium-forms-views`.

## Spam and limits

- A hidden field people never see and bots fill in. A submission with it filled is answered as sent and dropped.
- The time the form was drawn, encrypted with the app key. A form sent sooner than `min_seconds` (3) after that, or without the time, is refused with "sent too quickly".
- At most `per_minute` (5) submissions of one form from one IP address per minute.
- The site's captcha, when one is on (`ATRIUM_CAPTCHA`: `image` built in, `recaptcha` for Google reCAPTCHA v2, `none` by default): every form asks it under its fields. `captcha => false` in `config/atrium-forms.php` keeps it off the forms.

`php artisan vendor:publish --tag=atrium-forms-config` copies `config/atrium-forms.php` to change these, the honeypot field name, the captcha and the address prefix; `ATRIUM_FORMS_MIN_SECONDS` and `ATRIUM_FORMS_PER_MINUTE` work without it.

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
