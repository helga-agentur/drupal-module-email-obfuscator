<?php

namespace Drupal\Tests\email_obfuscator\Unit;

use Drupal\Core\Site\Settings;
use Drupal\email_obfuscator\EmailObfuscatorService;
use Drupal\Tests\UnitTestCase;

/**
 * Test mainly regexes.
 *
 * @group email_obfuscator
 */
class EmailObfuscatorTest extends UnitTestCase {

  /**
   * The email obfuscator service.
   *
   * @var \Drupal\email_obfuscator\EmailObfuscatorService
   */
  protected EmailObfuscatorService $emailObfuscator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->emailObfuscator = new EmailObfuscatorService();
  }

  /**
   * Tests the email obfuscation functionality.
   * 
   * N.B.: This test suite is running via the Browser too, in order to
   * ensure that the email obfuscation works in a real Drupal context.
   * It is kept here to provide a fast way to manage the test cases,
   * i.e. extend and debug via Unit tests, and then run the same
   * test cases in the browser context.
   *
   * @dataProvider dataProviderForTestEmailObfuscation
   */
  public function testEmailInText(string $content, string $expected): void {
    $this->assertSame(
      $expected,
      $this->emailObfuscator->obfuscateEmails($content),
      "The email obfuscation did not match the expected output for case: $content"
    );
  }

  /**
   * Data provider for testEmailInText
   * 
   * This provides various test cases for email obfuscation.
   * 
   * @return \Generator
   *   A generator yielding arrays with content and expected output.
   */
  public static function dataProviderForTestEmailObfuscation(): \Generator {
    yield 'testPlainTextEmail' => [
      'test@email.com',
      'test@<span style=\'display:none\' data-nosnippet>!zilch!</span>email.com',
      'test@<span style=\'display:none\' >!zilch!</span>email.com',
    ];
    yield 'testEmailInMailtoHref' => [
      '<a href="mailto:test@email.com">',
      '<a href="mailto:moc.liame@tset" onfocus="!this.dataset.obfuscated && (this.dataset.obfuscated = true) && this.setAttribute(\'href\', \'mailto:\' + this.getAttribute(\'href\').substring(7).split(\'\').reverse().join(\'\'))" onmousedown="!this.dataset.obfuscated && (this.dataset.obfuscated = true) && this.setAttribute(\'href\', \'mailto:\' + this.getAttribute(\'href\').substring(7).split(\'\').reverse().join(\'\'))">',
      '<a href="mailto:moc.liame@tset" onfocus="!this.dataset.obfuscated && (this.dataset.obfuscated = true) && this.setAttribute(\'href\', \'mailto:\' + this.getAttribute(\'href\').substring(7).split(\'\').reverse().join(\'\'))" onmousedown="!this.dataset.obfuscated && (this.dataset.obfuscated = true) && this.setAttribute(\'href\', \'mailto:\' + this.getAttribute(\'href\').substring(7).split(\'\').reverse().join(\'\'))">',
    ];
    yield 'testInvalidEmailInMailtoHref' => [
      '<a href="test@email.com">',
      '<a href="test@email.com">',
      '<a href="test@email.com">',
    ];
    yield 'testEmailInHtmlAttribute' => [
      '<input placeholder="test@email.com">',
      '<input placeholder="test@email.com">',
      '<input placeholder="test@email.com">',
    ];
    yield 'testEmailInHtmlAttributeWithMailto' => [
      '<input placeholder="mailto:test@email.com">',
      '<input placeholder="mailto:test@email.com">',
      '<input placeholder="mailto:test@email.com">',
    ];
    yield 'testEmailInMailtoHrefWithSpace' => [
      '<a href="mailto: test@ email.com ">',
      '<a href="mailto: test@ email.com ">',
      '<a href="mailto: test@ email.com ">',
    ];
    yield 'testEmailsWildlyInsideHtmlElementsNormal' => [
      "<div test@email.com>test@email.com</div>",
      "<div test@email.com>test@<span style='display:none' data-nosnippet>!zilch!</span>email.com</div>",
      "<div test@email.com>test@<span style='display:none' >!zilch!</span>email.com</div>",
    ];
    yield 'testEmailsWildlyInsideHtmlElementsWithSpace' => [
      "<div test@email.com>asdf test@email.com</div>",
      "<div test@email.com>asdf test@<span style='display:none' data-nosnippet>!zilch!</span>email.com</div>",
      "<div test@email.com>asdf test@<span style='display:none' >!zilch!</span>email.com</div>",
    ];
    yield 'testEmailsWildlyInsideHtmlElementsWithMultipleEmails' => [
      "<div test@email.com test@email.com>asdf test@email.com</div test@email.com>",
      "<div test@email.com test@email.com>asdf test@<span style='display:none' data-nosnippet>!zilch!</span>email.com</div test@email.com>",
      "<div test@email.com test@email.com>asdf test@<span style='display:none' >!zilch!</span>email.com</div test@email.com>",
    ];
    yield 'testEmailsWildlyInsideHtmlElementsWithBr' => [
      "<div test@email.com><br/>asdf test@email.com</div test@email.com>",
      "<div test@email.com><br/>asdf test@<span style='display:none' data-nosnippet>!zilch!</span>email.com</div test@email.com>",
      "<div test@email.com><br/>asdf test@<span style='display:none' >!zilch!</span>email.com</div test@email.com>",
    ];
  }

}
