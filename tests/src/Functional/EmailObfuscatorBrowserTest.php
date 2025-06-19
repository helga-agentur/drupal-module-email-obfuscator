<?php

declare(strict_types=1);

namespace Drupal\Tests\email_obfuscator\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\email_obfuscator\Unit\EmailObfuscatorTest as EmailObfuscatorUnitTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the email obfuscation functionality in a browser context.
 *
 * @group email_obfuscator
 */
final class EmailObfuscatorBrowserTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['email_obfuscator_test_controller'];

  /**
   * The route of the controller that returns the content verbatim.
   */
  protected static $verbatimControllerRoute = 'email_obfuscator_test.verbatim_respond';

  /**
   * Tests the email obfuscation functionality in a browser context.
   * 
   * @param string $content
   * @param string $expected
   * 
   * @dataProvider dataProviderForTestEmailObfuscationProxy
   */
  public function testVerbatimSymfonyResponse(string $content, string $expected): void {
    // This controller will return the content as is, which we will
    // then test for email obfuscation.
    // This allows testing the email obfuscation functionality
    // in a browser context, simulating how it would be rendered
    // in a real Drupal page. 
    $response_content = $this->drupalGet($this->getVerbatimControllerUrl(),
      ['query' => ['content' => $content]]);
    $this->assertSame($expected, $response_content,
      "The email obfuscation did not match the expected output for case: $content");
  }

  /**
   * Tests the whitelisting functionality of the email obfuscation.
   * 
   * i.e. the content should be unobfuscated when the route is whitelisted.
   *
   * @param string $content
   * @param string $expected
   *
   * @dataProvider dataProviderForTestEmailObfuscationProxy
   */
  public function testWhitelisting(string $content, string $expected): void {
    $settings['settings']['email_obfuscator']['route_whitelist'] = (object) [
      'value' => [self::$verbatimControllerRoute],
      'required' => TRUE,
    ];
    $this->writeSettings($settings);

    $response_content = $this->drupalGet($this->getVerbatimControllerUrl(),
      ['query' => ['content' => $content]]);
    $this->assertSame($content, $response_content,
      "Despite the whitelisting functionality, emails were obfuscated");
  }

  /**
   * Data provider for testEmailObfuscationProxy.
   *
   * This proxies the data provider from the unit test to be used in the browser
   * test.
   *
   * @return \Generator
   *   A generator yielding arrays with content and expected output.
   */
  public static function dataProviderForTestEmailObfuscationProxy(): \Generator {
    return EmailObfuscatorUnitTest::dataProviderForTestEmailObfuscation();
  }

  /**
   * Returns the URL of the verbatim controller.
   */
  protected function getVerbatimControllerUrl(): string {
    return Url::fromRoute(self::$verbatimControllerRoute, [], ['absolute' => FALSE])->toString();
  }

}
