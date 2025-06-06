# Drupal Module Email Obfuscator

The Drupal Email Obfuscator Module uses an event listener for each generated
response to search for emails using regexes. The emails are obfuscated
depending on where the text is found, helping to prevent email scraping and
reduce spam.

## Obfuscations

### Emails in a Mailto-Link

Example: `<a href="mailto:test@email.com">`

- The email string excluding `mailto:` is reversed,
  e.g. `<a href="mailto:moc.liame@tset">`
- An onfocus and an onmousedown JS listeners are added inline; these will
  revert the email address following the `mailto:` prefix back to its original
  form. These two events cover the following cases: right-click, left-click
  and focus with tab.  
  Note that:
  - onfocus would do it for most browsers, but Safari needs onmousedown
  - Reverting the already reversed email is only done once in a page load

### All other Emails

Example: `<a>test@email.com</a>`

- A span with `display:none` containing a text with delimiters that are invalid
  email characters is added in the middle of the email, e.g.
  `<a>test@<span style='display:none'>!zilch!</span>email.com</a>`

## Exclusions

- Any email that is invalid (according to PHP's `filter_var` function)
- Everything in the backoffice (admin pages)
- Emails inside HTML-attributes (placeholder attribute for input fields)
- Exclude Ajax webform request:
    - Because Ajax is usually used when sending a web form.  
      This means that the request does not contain HTML, but a JSON object in
      which HTML is encoded with Unicode.   
      The regex does not apply here and it is not necessary to obfuscate this
      email address as it is added by the sender.
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
