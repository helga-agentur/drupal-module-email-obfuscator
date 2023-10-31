# Drupal Module Email Obfuscator

Drupal Email Obfuscator Module is used to search everywhere and obfuscate all emails within Drupal using a Middleware.

## What is obfuscated and how

- Emails in a `<a href=mailto:`:
    - the email after `mailto:` is reverted
    - an onclick is added that re-reverts the email after the `mailto:`
- For all other emails (e.g. text content in an `<a>`):
    - A span with `display:none` containing a text with delimiters that are invalid email characters is added after
      the @

## Exclusions

- Everything in the backoffice
- Emails in placeholder of an input
- Content in routes that are whitelisted

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
