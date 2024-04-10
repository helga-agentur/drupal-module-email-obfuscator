# Drupal Module Email Obfuscator

The Drupal Email Obfuscator Module uses a middleware get rendered content from each request. The content is searched for
emails with regexes. The emails are obfuscated depending on where the text is found.

## Obfuscations

### Emails in a Mailto-Link

Example: `<a href="mailto:test@email.com">`

- The email string excluding `mailto:` is reversed
- An onfocus and an onmousedown are added which re-reverse the email after the `mailto:`. These two events cover the
  following cases: right-click, left-click and focus with tab.

_The re-reverse is only done once in order to avoid reversing back to the reversed email_

### All other Emails

Example: `<a>test@email.com</a>`

- A span with `display:none` containing a text with delimiters that are invalid email characters is added in the middle
  of the email

## Exclusions

- Any email that is invalid (according to PHP's `filter_var` function)
- Everything in the backoffice (admin pages)
- Emails inside HTML-attributes (placeholder for input fields)
- Content in routes that are whitelisted (see below)

### Whitelisting Routes

- Define whitelisted (excluded) routes in settings.php
   ```php
   $settings['email_obfuscator'] = [
     'route_whitelist' => [
       'rest.api_layout_footer.GET',
       'editor.link_dialog'
     ]
   ];
   ```
- **IMPORTANT:** If you are using CKEditor 4 you should whitelist the route `editor.link_dialog` to avoid
  obfuscating the email in the CKEditor link dialog.
