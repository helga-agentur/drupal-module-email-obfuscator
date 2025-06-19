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
   * Tests clicking on a mailto: link.
   * 
   * This test ensures that the mailto link is obfuscated
   * before clicking and un-obfuscated after clicking.
   * It simulates a user clicking on a mailto link and checks
   * the HTML content of the page before and after the click.
   */
  public function testRevertOnLinkClick(): void {
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
