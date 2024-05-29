<?php

namespace Drupal\Tests\email_obfuscator\Unit;

use Drupal\email_obfuscator\EmailObfuscatorService;
use Drupal\Tests\UnitTestCase;

/**
 * Test mainly regexes.
 *
 * @group email_obfuscator
 */
class EmailObfuscatorTest extends UnitTestCase {

  protected EmailObfuscatorService $emailObfuscatorService;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->emailObfuscatorService = new EmailObfuscatorService();
  }

  protected function tearDown(): void {
    unset($this->emailObfuscatorService);

    parent::tearDown();
  }

  public function testPlainTextEmail(): void {
    $content = 'test@email.com';
    $this->assertEquals(
      'test@<span style=\'display:none\'>!zilch!</span>email.com',
      $this->emailObfuscatorService->obfuscateEmails($content)
    );
  }

  public function testEmailInMailtoHref(): void {
    $content = '<a href="mailto:test@email.com">';

    $this->assertEquals(
      '<a href="mailto:moc.liame@tset" onfocus="!this.dataset.obfuscated && (this.dataset.obfuscated = true) && this.setAttribute(\'href\', \'mailto:\' + this.getAttribute(\'href\').substring(7).split(\'\').reverse().join(\'\'))" onmousedown="!this.dataset.obfuscated && (this.dataset.obfuscated = true) && this.setAttribute(\'href\', \'mailto:\' + this.getAttribute(\'href\').substring(7).split(\'\').reverse().join(\'\'))">',
      $this->emailObfuscatorService->obfuscateEmails($content)
    );
  }

  public function testInvalidEmailInMailtoHref(): void {
    $content = '<a href="test@email.com">';
    $this->assertEquals($content, $this->emailObfuscatorService->obfuscateEmails($content));
  }

  public function testEmailInHtmlAttribute(): void {
    $content = '<input placeholder="test@email.com">';
    $this->assertEquals($content, $this->emailObfuscatorService->obfuscateEmails($content));
  }

  public function testEmailInHtmlAttributeWithMailto(): void {
    $content = '<input placeholder="mailto:test@email.com">';
    $this->assertEquals($content, $this->emailObfuscatorService->obfuscateEmails($content));
  }

  public function testEmailInMailtoHrefWithSpace() {
    $content = '<a href="mailto: test@ email.com ">';
    $this->assertEquals($content, $this->emailObfuscatorService->obfuscateEmails($content));
  }

  public function testPlaintextEmailInInput() {
    $content = '<input>test@email.com</input>';
    $this->assertEquals($content, $this->emailObfuscatorService->obfuscateEmails($content));

    $content2 = '<input value="test@email">test@email.com</input>';
    $this->assertEquals($content2, $this->emailObfuscatorService->obfuscateEmails($content2));
  }

  public function testEmailsWildlyInsideHtmlElements() {
    $content = "<div test@email.com>test@email.com</div>";
    $this->assertEquals("<div test@email.com>test@<span style='display:none'>!zilch!</span>email.com</div>", $this->emailObfuscatorService->obfuscateEmails($content));

    $content = "<div test@email.com>asdf test@email.com</div>";
    $this->assertEquals("<div test@email.com>asdf test@<span style='display:none'>!zilch!</span>email.com</div>", $this->emailObfuscatorService->obfuscateEmails($content));

    $content = "<div test@email.com test@email.com>asdf test@email.com</div test@email.com>";
    $this->assertEquals("<div test@email.com test@email.com>asdf test@<span style='display:none'>!zilch!</span>email.com</div test@email.com>", $this->emailObfuscatorService->obfuscateEmails($content));

    $content = "<div test@email.com><br/>asdf test@email.com</div test@email.com>";
    $this->assertEquals("<div test@email.com><br/>asdf test@<span style='display:none'>!zilch!</span>email.com</div test@email.com>", $this->emailObfuscatorService->obfuscateEmails($content));
  }

}
