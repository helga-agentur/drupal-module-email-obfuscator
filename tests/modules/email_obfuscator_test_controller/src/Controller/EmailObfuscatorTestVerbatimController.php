<?php

declare(strict_types=1);

namespace Drupal\email_obfuscator_test_controller\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses containing whatever the 'content' query param contains.
 */
final class EmailObfuscatorTestVerbatimController extends ControllerBase {

  /**
   * Returns a simple response containing the 'content' query parameter.
   */
  public function respond(Request $request): Response {
    return new Response($request->query->get('content'));
  }

}
