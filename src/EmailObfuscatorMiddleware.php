<?php

namespace Drupal\email_obfuscator;

use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;
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
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    $response = $this->httpKernel->handle($request, $type, $catch);

    try {
      if ($content = $response->getContent()) {
        $routes = \Drupal::service('router.no_access_checks')->matchRequest($request);
        $isAdminPath = \Drupal::service('router.admin_context')->isAdminRoute($routes['_route_object']);
        $whitelist = Settings::get('email_obfuscator')['route_whitelist'] ?? [];
        $isWhitelisted = in_array($routes['_route'], $whitelist);

        // Check whether an Ajax route with Webform content has been sent;
        // this content should not be obfuscated.
        $isAjaxRequest = $request->isXmlHttpRequest();
        $isWebForm = $request->request->has('form_id') && str_starts_with($request->request->get('form_id'), 'webform');

        // don't obfuscate emails in backoffice or on whitelisted routes or from webforms with enabled ajax
        if (!$isAdminPath
            && !$isWhitelisted
            && (!$isAjaxRequest && !$isWebForm)
            && $obfuscateEmails = $this->emailObfuscatorService->obfuscateEmails($content)) {
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
