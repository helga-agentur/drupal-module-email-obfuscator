<?php

namespace Drupal\email_obfuscator;

use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * EmailObfuscatorMiddleware middleware.
 */
class EmailObfuscatorMiddleware implements HttpKernelInterface {

  protected HttpKernelInterface $httpKernel;

  protected EmailObfuscatorService $emailObfuscatorService;

  /**
   * Constructs the EmailObfuscatorMiddleware object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   */
  public function __construct(
    HttpKernelInterface $http_kernel,
    EmailObfuscatorService $emailObfuscatorService
  ) {
    $this->httpKernel = $http_kernel;
    $this->emailObfuscatorService = $emailObfuscatorService;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = TRUE): Response {
    $response = $this->httpKernel->handle($request, $type, $catch);

    try {
      if ($content = $response->getContent()) {
        $whitelist = Settings::get('email-obfuscator')['whitelist'] ?? [];
        $isWhitelisted = in_array(\Drupal::routeMatch()->getRouteName(), $whitelist);
        $isAdminRoute = \Drupal::service('router.admin_context')->isAdminRoute();

        // don't obfuscate emails in backoffice
        if (!$isAdminRoute && !$isWhitelisted && $obfuscateEmails = $this->emailObfuscatorService->obfuscateEmails(
            $content
          )) {
          $response->setContent($obfuscateEmails);
        }
      }
    } catch (\Exception $e) {
      \Drupal::logger('email_obfuscator')->error($e->getMessage());
    }

    return $response;
  }

}
