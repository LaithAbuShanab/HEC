<?php

namespace App\Domain\Shared\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class RichText
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function clean(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        $clean = trim(self::sanitizer()->sanitize((string) $html));

        return $clean === '' || trim(strip_tags($clean)) === '' ? null : $clean;
    }

    private static function sanitizer(): HtmlSanitizer
    {
        return self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('b')
                ->allowElement('em')
                ->allowElement('i')
                ->allowElement('u')
                ->allowElement('h2')
                ->allowElement('h3')
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowElement('blockquote')
                ->allowElement('table')
                ->allowElement('thead')
                ->allowElement('tbody')
                ->allowElement('tr')
                ->allowElement('th', ['colspan', 'rowspan'])
                ->allowElement('td', ['colspan', 'rowspan'])
                ->allowElement('a', ['href', 'title'])
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->forceAttribute('a', 'rel', 'noopener nofollow')
                ->withMaxInputLength(200000),
        );
    }
}
