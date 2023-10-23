# Drupal Module Email Obfuscator

Drupal Email Obfuscator Module is used to search everywhere and obfuscate all emails within Drupal using a Middleware.

## What is obfuscated and how

- Emails in a `<a href=mailto:`:
    - the email after `mailto:` is reverted
    - an onclick is added that re-reverts the email after the `mailto:`
- For all other emails (e.g. text content in an `<a>`):
    - A span with `display:none` containing a text with delimiters that are invalid email characters is added after the @

## Exclusions

- Everything in the backoffice
- Emails in placeholder of an input
- Define whitelisted (excluded) routes in settings.php
  ```php
  $settings['email-obfuscator'] = [
    'whitelist' => [
      'rest.api_layout_footer.GET'
    ]
  ];
  ```
