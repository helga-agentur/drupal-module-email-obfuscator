<?php

declare(strict_types=1);

namespace Drupal\Tests\email_obfuscator\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\email_obfuscator\EmailObfuscatorTestsTrait;
use Drupal\Tests\email_obfuscator\Unit\EmailObfuscatorTest as EmailObfuscatorUnitTest;

/**
 * Tests the email obfuscation functionality in a browser context.
 *
 * @group email_obfuscator
 */
final class EmailObfuscatorBrowserTest extends BrowserTestBase {

  use EmailObfuscatorTestsTrait;

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
   * The route of the controller that returns the content verbatim for admin users.
   */
  protected static $verbatimControllerAdminRoute = 'email_obfuscator_test.verbatim_respond_admin';

  /**
   * Tests the email obfuscation functionality with an anonymous user.
   * 
   * @param string $content
   * @param string $expected
   * 
   * @dataProvider dataProviderForTestEmailObfuscationProxy
   */
  public function testVerbatimSymfonyResponseAnon(string $content, string $expected): void {
    // This controller will return the content as is, which we will
    // then test for email obfuscation.
    // This allows testing the email obfuscation functionality
    // in a browser context, simulating how it would be rendered
    // in a real Drupal page. 
    $response_content = $this->drupalGet($this->getUrlForRoute(self::$verbatimControllerRoute),
      ['query' => ['content' => $content]]);
    $this->assertSame($expected, $response_content,
      "The email obfuscation did not match the expected output for anonymous users, case: $content");
  }

  /**
   * Tests the email obfuscation functionality with an authenticated user.
   * 
   * @param string $content
   * @param string $expected
   * 
   * @dataProvider dataProviderForTestEmailObfuscationProxy
   */
  public function testVerbatimSymfonyResponseAuth(string $content, string $expected): void {
    $this->drupalLogin($this->drupalCreateUser());
    $response_content = $this->drupalGet($this->getUrlForRoute(self::$verbatimControllerRoute),
      ['query' => ['content' => $content]]);
    $this->assertSame($expected, $response_content,
      "The email obfuscation did not match the expected output for authenticated users, case: $content");
  }

  /**
   * Tests the email obfuscation functionality for an admin route.
   * 
   * This test ensures that the email obfuscation does not
   * modify the content for admin routes, as the email obfuscation
   * is not applied to admin routes by design.
   * This scenario requires an authenticated user.
   * 
   * @param string $content
   * 
   * @dataProvider dataProviderForTestEmailObfuscationProxy
   */
  public function testVerbatimSymfonyResponseAdmin(string $content): void {
    $this->drupalLogin($this->drupalCreateUser());
    $response_content = $this->drupalGet($this->getUrlForRoute(self::$verbatimControllerAdminRoute),
      ['query' => ['content' => $content]]);
    $this->assertSame($content, $response_content,
      "The email obfuscation should not have modified the content for admin routes: $response_content");
  }

  /**
   * Tests the whitelisting functionality of the email obfuscation.
   * 
   * i.e. the content should be unobfuscated when the route is whitelisted.
   *
   * @param string $content
  *
  * @dataProvider dataProviderForTestEmailObfuscationProxy
  */
  public function testWhitelisting(string $content): void {
    $settings['settings']['email_obfuscator']['route_whitelist'] = (object) [
      'value' => [self::$verbatimControllerRoute],
      'required' => TRUE,
    ];
    $this->writeSettings($settings);

    $response_content = $this->drupalGet($this->getUrlForRoute(self::$verbatimControllerRoute),
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

}
