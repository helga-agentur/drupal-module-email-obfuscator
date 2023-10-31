<?php

namespace Drupal\Tests\email_obfuscator\Unit;

use Drupal\email_obfuscator\EmailObfuscatorService;
use Drupal\Tests\UnitTestCase;

/**
 * Test description.
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
      '<a href="mailto:moc.liame@tset" onclick="this.href=\'mailto:\' + this.getAttribute(\'href\').substr(7).split(\'\').reverse().join(\'\')">',
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

}
