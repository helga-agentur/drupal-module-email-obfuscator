<?php

namespace Drupal\email_obfuscator;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * EmailObfuscatorMiddleware middleware.
 */
class EmailObfuscatorMiddleware implements HttpKernelInterface {

  use StringTranslationTrait;

  /**
   * The kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * Constructs the EmailObfuscatorMiddleware object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   */
  public function __construct(HttpKernelInterface $http_kernel) {
    $this->httpKernel = $http_kernel;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    $response = $this->httpKernel->handle($request, $type, $catch);

    try {
      if ($content = $response->getContent()) {
        // don't obfuscate emails in backoffice
                if (!\Drupal::service('router.admin_context')->isAdminRoute() &&$obfuscateEmails = $this->obfuscateEmails($content)) {
          $response->setContent($obfuscateEmails);
        }
      }
    } catch (\Exception $e) {
      \Drupal::logger('email_obfuscator')->error($e->getMessage());
    }

    return $response;
  }

  /**
   * Revert emails in mailto-links and add a display-none-span in the email-texts so bots can't read them - hopefully.
   * https://web.archive.org/web/20180908103745/http://techblog.tilllate.com/2008/07/20/ten-methods-to-obfuscate-e-mail-addresses-compared/
   *http://jasonpriem.com/obfuscation-decoder/
     *
   * @param string $content
   *
   * @return string The processed string
   * @throws \Exception
   */
  private function obfuscateEmails(string $content): string {
    // here we find the mailto links, revert them and add onclick which reverts mailto-link back to normal
    $mailtoRegex = '/(href=)"mailto:([^"]+)"/';

    $obfuscatedContent = preg_replace_callback(
      $mailtoRegex,
      function ($matches) {
                return $matches[1] . "\"mailto:" . strrev($matches[2]) . "\" onclick=\"this.href='mailto:' + this.getAttribute('href').substr(7).split('').reverse().join('')\"";
            },
      $content
    )?? throw new \Exception('Removing mailtos with regex and adding onclick to email links failed.');

    // exclamation marks are invalid in emails. we use them as delimiters, so we don't replace unwanted parts of the email
    $stringToReplace = "!zilch!";

    // get all emails with optional selector for email texts in placeholder or mailto
    $emailRegex = '/(placeholder=\"|mailto:)?([\w\.\+\-]+@)([\w\-\.]+\.[a-zA-Z]{2,})/';

    return preg_replace_callback(
      $emailRegex,
      function ($matches) use ($stringToReplace){
        if (!empty($matches[1])) {
                    // if the email is in a mailto-link or placeholder don't do anything
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
