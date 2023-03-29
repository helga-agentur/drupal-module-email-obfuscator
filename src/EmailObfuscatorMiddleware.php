<?php

namespace Drupal\email_obfuscator;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * EmailObfuscatorMiddleware middleware.
 */
class EmailObfuscatorMiddleware implements HttpKernelInterface {

    use StringTranslationTrait;

    /**
     * The kernel.
     *
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    protected $httpKernel;

    /**
     * Constructs the EmailObfuscatorMiddleware object.
     *
     * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
     *   The decorated kernel.
     */
    public function __construct(HttpKernelInterface $http_kernel) {
        $this->httpKernel = $http_kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response {
        $response = $this->httpKernel->handle($request, $type, $catch);

        try {
            if ($content = $response->getContent()) {
                // don't obfuscate emails in backoffice
                if (!\Drupal::service('router.admin_context')->isAdminRoute() && $obfuscateEmails = $this->obfuscateEmails($content)) {
                    $response->setContent($obfuscateEmails);
                }
            }
        } catch (\Exception $e) {
            \Drupal::logger('email_obfuscator')->error($e->getMessage());
        }

        return $response;
    }

    /**
     * Remove emails from mailto links and reverse the emails so bots can't read them - hopefully.
     * https://web.archive.org/web/20180908103745/http://techblog.tilllate.com/2008/07/20/ten-methods-to-obfuscate-e-mail-addresses-compared/
     *
     * @param string $content
     *
     * @return string The processed string
     * @throws \Exception
     */
    private function obfuscateEmails(string $content): string {
        // remove mailto links and replace with onclick rereverse method
        $mailtoRegex = '/(href=)"mailto:[^"]+"/';

        $obfuscatedContent = preg_replace(
            $mailtoRegex,
            "$1\"#\" onclick='this.href=`mailto:` + this.querySelector(`span`).textContent.split(``).reverse().join(``)'",
            $content
        );

        if (!$obfuscatedContent) {
            throw new \Exception('Removing mailtos with regex and adding onclick to email links failed.');
        }

        // css reverse all emails and show correctly by changing the text direction
        $emailRegex = '/([\w\.\+\-]+@[\w\-\.]+\.[a-zA-Z]{2,})/';

        return preg_replace_callback(
            $emailRegex,
            function ($matches) {
                return "<span style='unicode-bidi:bidi-override;direction:rtl'>" . strrev(
                        $matches[0]
                    ) . "</span>";
            },
            $obfuscatedContent
        ) ?? throw new \Exception('CSS reversing emails failed.');
    }
}
