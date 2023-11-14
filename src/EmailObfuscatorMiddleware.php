<?php

namespace Drupal\email_obfuscator;

use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

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
        $routes = \Drupal::service('router.no_access_checks')->matchRequest($request);
        $isAdminPath = \Drupal::service('router.admin_context')->isAdminRoute($routes['_route_object']);
        $whitelist = Settings::get('email_obfuscator')['route_whitelist'] ?? [];
        $isWhitelisted = in_array($routes['_route'], $whitelist);

        // don't obfuscate emails in backoffice or on whitelisted routes
        if (!$isAdminPath && !$isWhitelisted && $obfuscateEmails = $this->emailObfuscatorService->obfuscateEmails(
            $content
          )) {
          $response->setContent($obfuscateEmails);
        }
      }
    }
    catch (ResourceNotFoundException | MethodNotAllowedException) {}
    catch (\Exception $e) {
      \Drupal::logger('email_obfuscator')->error($e->getMessage());
    }

    return $response;
  }

}
