<?php

declare(strict_types=1);

namespace Drupal\Tests\email_obfuscator;

use Drupal\Core\Url;

trait EmailObfuscatorTestsTrait {

  /**
   * Returns a URL for the given Drupal route.
   * 
   * This is used to get the URL of the controller that returns
   * the content verbatim, which is then tested for email obfuscation.
   * The URL can be absolute or relative, depending on the $absolute parameter.
   * 
   * @param string $route
   *   The route name of the controller.
   * @param bool $absolute
   *   Whether to return an absolute URL or a relative one.
   *   Defaults to FALSE, which returns a relative URL.
   * @return string
   *   The URL of the controller that returns the content verbatim.
   */
  protected function getUrlForRoute(string $route, bool $absolute = FALSE): string {
    return Url::fromRoute($route, [], ['absolute' => $absolute])->toString();
  }

}
