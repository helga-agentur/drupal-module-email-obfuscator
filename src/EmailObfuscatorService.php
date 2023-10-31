<?php

namespace Drupal\email_obfuscator;

/**
 * EmailObfuscatorService service.
 */
class EmailObfuscatorService {

  /**
   * Revert emails in mailto-links and add a display-none-span in the email-texts so bots can't
   * read them - hopefully.
   * https://web.archive.org/web/20180908103745/http://techblog.tilllate.com/2008/07/20/ten-methods-to-obfuscate-e-mail-addresses-compared/
   * http://jasonpriem.com/obfuscation-decoder/
   *
   * @param string $content
   *
   * @return string The processed string
   * @throws \Exception
   */
  public function obfuscateEmails(string $content): string {
    // here we find the mailto links, revert them and add onclick which reverts mailto-link back to normal
    $mailtoRegex = '/(href=)"mailto:([^"]+)"/';
    $obfuscatedContent = preg_replace_callback(
      $mailtoRegex,
      function ($matches) {
        return $matches[1] . "\"mailto:" . strrev(
            $matches[2]
          ) . "\" onclick=\"this.href='mailto:' + this.getAttribute('href').substr(7).split('').reverse().join('')\"";
      },
      $content
    ) ?? throw new \Exception(
      'Removing mailtos with regex and adding onclick to email links failed.'
    );

    // exclamation marks are invalid in emails. we use them as delimiters, so we don't replace unwanted parts of the email
    $stringToReplace = "!zilch!";

    // get all emails with optional selector for email texts inside a tag (i.e. value of an attribute)
    $emailRegex = '/(?<!<[^>]*)?([\w\.\+\-]+@)([\w\-\.]+\.[a-zA-Z]{2,})/';

    return preg_replace_callback(
      $emailRegex,
      function ($matches) use ($stringToReplace) {
        if (!empty($matches[1])) {
          // if the email is in a html-attribute don't do anything
          return $matches[0];
        } else {
          // otherwise add the display none text
          return $matches[2] . "<span style='display:none'>" . $stringToReplace . "</span>" . $matches[3];
        }
      },
      $obfuscatedContent
    ) ?? throw new \Exception('Adding display-none-span failed.');
  }

}
