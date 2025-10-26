<?php

declare(strict_types=1);

namespace App\Services\Security;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

final class HtmlContentSanitizer
{
    /**
     * @var list<string>
     */
    private const ALLOWED_IFRAME_HOSTS = [
        'www.youtube.com',
        'youtube.com',
        'www.youtube-nocookie.com',
        'youtube-nocookie.com',
        'player.vimeo.com',
    ];

    private HtmlSanitizerInterface $sanitizer;

    public function __construct()
    {
        $this->sanitizer = new HtmlSanitizer($this->buildConfig());
    }

    public function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $sanitized = trim($this->sanitizer->sanitize($html));
        if ($sanitized === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousState = libxml_use_internal_errors(true);
        $wrapped = '<!DOCTYPE html><html><body>' . $sanitized . '</body></html>';
        $document->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        $body = $document->getElementsByTagName('body')->item(0);
        if (! $body instanceof DOMElement) {
            return $sanitized;
        }

        $this->enforceImagePolicy($document);
        $this->enforceLinkPolicy($document);
        $this->enforceIframePolicy($document);

        $output = '';
        foreach ($body->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    private function buildConfig(): HtmlSanitizerConfig
    {
        $config = new HtmlSanitizerConfig;

        foreach (['p', 'br', 'blockquote'] as $element) {
            $config = $config->allowElement($element, []);
        }

        foreach (['strong', 'em', 'b', 'i', 'u', 'code'] as $element) {
            $config = $config->allowElement($element, []);
        }

        foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $heading) {
            $config = $config->allowElement($heading, []);
        }

        foreach (['ul', 'ol', 'li'] as $listElement) {
            $config = $config->allowElement($listElement, []);
        }

        $config = $config
            ->allowElement('a', ['href', 'title', 'rel', 'target'])
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            ->allowRelativeLinks(false)
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'loading'])
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeMedias(false)
            ->forceHttpsUrls()
            ->forceAttribute('img', 'loading', 'lazy')
            ->allowElement('iframe', ['src', 'title', 'allow', 'allowfullscreen', 'width', 'height', 'loading']);

        return $config;
    }

    private function enforceImagePolicy(DOMDocument $document): void
    {
        foreach ($this->toArray($document->getElementsByTagName('img')) as $image) {
            $src = $image->getAttribute('src');
            if ($src === '' || ! $this->isHttpUrl($src)) {
                $this->removeNode($image);

                continue;
            }

            $image->setAttribute('loading', 'lazy');
        }
    }

    private function enforceLinkPolicy(DOMDocument $document): void
    {
        foreach ($this->toArray($document->getElementsByTagName('a')) as $link) {
            $href = $link->getAttribute('href');
            if ($href !== '' && ! $this->isHttpUrl($href) && ! str_starts_with($href, 'mailto:')) {
                $link->removeAttribute('href');
            }

            $target = $link->getAttribute('target');
            if ($target === '_blank') {
                $link->setAttribute('rel', 'noopener noreferrer');
            } elseif ($target !== '') {
                $link->removeAttribute('target');
            }
        }
    }

    private function enforceIframePolicy(DOMDocument $document): void
    {
        foreach ($this->toArray($document->getElementsByTagName('iframe')) as $iframe) {
            $src = $iframe->getAttribute('src');
            if (! $this->isAllowedIframe($src)) {
                $this->removeNode($iframe);

                continue;
            }

            $iframe->setAttribute('loading', 'lazy');
        }
    }

    private function isAllowedIframe(string $url): bool
    {
        if ($url === '' || ! $this->isHttpUrl($url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host)) {
            return false;
        }

        $host = strtolower($host);

        return in_array($host, self::ALLOWED_IFRAME_HOSTS, true);
    }

    private function isHttpUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return $scheme === 'http' || $scheme === 'https';
    }

    /**
     * @param  DOMNodeList<DOMNode> $nodes
     * @return list<DOMElement>
     */
    private function toArray(DOMNodeList $nodes): array
    {
        $elements = [];
        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                $elements[] = $node;
            }
        }

        return $elements;
    }

    private function removeNode(DOMNode $node): void
    {
        if ($node->parentNode instanceof DOMNode) {
            $node->parentNode->removeChild($node);
        }
    }
}
