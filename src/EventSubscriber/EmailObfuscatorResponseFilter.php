<?php

declare(strict_types=1);

namespace Drupal\email_obfuscator\EventSubscriber;

use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Site\Settings;
use Drupal\email_obfuscator\EmailObfuscatorService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * Event subscriber to obfuscate email addresses in responses.
 *
 * This subscriber listens to the kernel response event and applies a filter
 * to obfuscate email addresses in the response content.
 * @package Drupal\email_obfuscator\EventSubscriber
 */
final class EmailObfuscatorResponseFilter implements EventSubscriberInterface {

  /**
   * Constructs an EmailObfuscatorSubscriber object.
   */
  public function __construct(
    private readonly EmailObfuscatorService $emailObfuscator,
    private readonly RequestMatcherInterface $requestMatcher,
    private readonly AdminContext $adminContext,
  ) {}

  /**
   * Kernel response event handler.
   */
  public function onKernelResponse(ResponseEvent $event): void {
    $request = $event->getRequest();
    $response = $event->getResponse();

    try {
      if ($content = $response->getContent()) {
        $routes = $this->requestMatcher->matchRequest($request);
        $isAdminPath = $this->adminContext->isAdminRoute($routes['_route_object']);
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
            && $obfuscateEmails = $this->emailObfuscator->obfuscateEmails($content)) {
          $response->setContent($obfuscateEmails);
        }
      }
    }
    catch (ResourceNotFoundException | MethodNotAllowedException) {}
    catch (\Exception $e) {
      \Drupal::logger('email_obfuscator')->error($e->getMessage());
    }

    $event->setResponse($response);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => ['onKernelResponse'],
    ];
  }

}
