<?php

declare(strict_types=1);

namespace Drupal\Tests\email_obfuscator\Functional;

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
   * The URL of the controller that returns the content verbatim.
   */
  protected static $verbatimControllerUrl = '/email-obfuscator-test/verbatim-respond';

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
    $response_content = $this->drupalGet(self::$verbatimControllerUrl,
      ['query' => ['content' => $content]]);
    $this->assertSame($expected, $response_content,
      "The email obfuscation did not match the expected output for case: $content");
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
