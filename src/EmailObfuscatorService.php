<?php

namespace Drupal\email_obfuscator;

/**
 * EmailObfuscatorService service.
 */
class EmailObfuscatorService {

  /**
   * Revert emails in mailto-links and add a display-none-span in the email-texts so bots can't read them - hopefully.
   * https://web.archive.org/web/20180908103745/http://techblog.tilllate.com/2008/07/20/ten-methods-to-obfuscate-e-mail-addresses-compared/
   * http://jasonpriem.com/obfuscation-decoder/
   *
   * @param string $content
   *
   * @return string The processed string
   * @throws \Exception
   */
  public function obfuscateEmails(string $content): string {
    $obfuscateMailtoLinks = $this->obfuscateMailtoLinks($content);

    // TODO: maybe use random string for displayNoneText
    return $this->obfuscateEmailStrings($obfuscateMailtoLinks, "zilch");
  }

  /**
   * Find the mailto links, revert them and add onclick which reverts mailto-link back to normal. But only on the first
   * click.
   *
   * Invalid emails are ignored.
   *
   * @param string $content
   *
   * @return string
   * @throws \Exception
   */
  private function obfuscateMailtoLinks(string $content): string {
    $mailtoRegex = '/(href=)"mailto:([^"]+)"/';

    return preg_replace_callback(
      $mailtoRegex,
      function ($matches) {
        // if the email is invalid, don't do anything
        if (!filter_var($matches[2], FILTER_VALIDATE_EMAIL)) {
          return $matches[0];
        }

        // the dataset.obfuscated is used to check if the link has already been reverted
        return $matches[1] . "\"mailto:" . strrev(
            $matches[2]
          ) . "\" onclick=\"!this.dataset.obfuscated && (this.dataset.obfuscated = true) && this.setAttribute('href', 'mailto:' + this.getAttribute('href').substring(7).split('').reverse().join(''))\"";
      },
      $content
    ) ?? throw new \Exception(
      'Removing mailtos with regex and adding onclick to email links failed.'
    );
  }

  /**
   * Get all email strings that are not in an HTML element and add a display-none-span in the middle of the string.
   *
   * Invalid emails are ignored.
   *
   * @param string $content
   * @param string $displayNoneText
   *
   * @return string
   * @throws \Exception
   */
  private function obfuscateEmailStrings(string $content, string $displayNoneText): string {
    // get all email strings that are not in an html element
    $emailRegex = '/(<[^>]+)|(([\w\-\.]+@)([\w\-\.]+\.[a-zA-Z]{2,}))/';

    // exclamation marks are invalid in emails. we use them as delimiters, so we don't replace unwanted parts of the email
    $stringToReplace = "!" . $displayNoneText . "!";

    return preg_replace_callback(
      $emailRegex,
      function ($matches) use ($stringToReplace) {
        // if the email is in an HTML element or if the email is invalid, don't do anything
        if (!empty($matches[1]) || !filter_var($matches[2], FILTER_VALIDATE_EMAIL)) {
          return $matches[0];
        }

        // otherwise add the display-none-span
        return $matches[3] . "<span style='display:none'>" . $stringToReplace . "</span>" . $matches[4];
      },
      $content
    ) ?? throw new \Exception('Adding display-none-span failed.');
  }

}
