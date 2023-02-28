# Drupal Module Email Obfuscator

Drupal Email Obfuscator Module is used to search everywhere and obfuscate all emails within Drupal using a Middleware.
The following is done to the emails:
- `mailto:` links are removed with a regex and replaced with an onclick
- Plaintext emails are reversed with CSS and made readable (ltr -> rtl)
