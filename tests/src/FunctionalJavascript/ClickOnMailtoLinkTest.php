<?php

declare(strict_types=1);

namespace Drupal\Tests\email_obfuscator\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the JavaScript functionality of the Email Obfuscator module.
 * 
 * @group email_obfuscator
 */
final class ClickOnMailtoLinkTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['email_obfuscator_test_controller'];

  /**
   * The URL of the controller that returns the content verbatim.
   */
  protected static $verbatimControllerUrl = '/email-obfuscator-test/verbatim-respond';

  /**
   * Test callback.
   */
  public function testSomething(): void {
    // Navigate to the test page.
    $this->drupalGet(self::$verbatimControllerUrl, [
      'query' => ['content' => '<a class="mailto-link" href="mailto:test@email.com">Click here to email</a>']]);

    $page_html = $this->getSession()->getPage()->getHtml();
    $this->assertStringContainsString(
      'href="mailto:moc.liame@tset"',
      $page_html,
      'The mailto link should be obfuscated in the page HTML before clicking.'
    );

    // Click on the mailto link.
    $this->getSession()->getPage()->find('css', 'a.mailto-link')->click();

    $page_html = $this->getSession()->getPage()->getHtml();
    $this->assertStringContainsString(
      'href="mailto:test@email.com"',
      $page_html,
      'The mailto link should be un-obfuscated in the page HTML after clicking.'
    );

    // Click again on the mailto link.
    $this->getSession()->getPage()->find('css', 'a.mailto-link')->click();

    $page_html = $this->getSession()->getPage()->getHtml();
    $this->assertStringContainsString(
      'href="mailto:test@email.com"',
      $page_html,
      'The mailto link should be still un-obfuscated in the page HTML after clicking a second time.'
    );

  }

}
