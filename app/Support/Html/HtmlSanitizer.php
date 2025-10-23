<?php

declare(strict_types=1);

namespace App\Support\Html;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Small HTML sanitizer tailored for user-generated content coming from rich text editors.
 * The allow-list is intentionally conservative and strips out scripting vectors and inline styles.
 */
final class HtmlSanitizer
{
    /**
     * @var array<int, string>
     */
    private array $allowedElements;

    /**
     * @var array<string, array<int, string>>
     */
    private array $allowedAttributes;

    /**
     * @var array<int, string>
     */
    private array $globalAttributes;

    public function __construct()
    {
        // Allow only semantic elements that are expected inside product descriptions or legal documents.
        $this->allowedElements = [
            'a', 'abbr', 'b', 'blockquote', 'br', 'caption', 'code', 'dd', 'del', 'div', 'dl', 'dt',
            'em', 'figcaption', 'figure', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins',
            'li', 'mark', 'ol', 'p', 'pre', 'q', 's', 'small', 'span', 'strong', 'sub', 'sup', 'table',
            'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'u', 'ul',
        ];

        // Define the attributes that are safe to keep on a per-element basis.
        $this->allowedAttributes = [
            'a' => ['href', 'title', 'target', 'rel'],
            'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
            'table' => ['summary'],
            'th' => ['scope', 'colspan', 'rowspan'],
            'td' => ['colspan', 'rowspan'],
            'ol' => ['start', 'type', 'reversed'],
            'ul' => ['type'],
            'blockquote' => ['cite'],
            'q' => ['cite'],
        ];

        // Allow a minimal global attribute set for accessibility and CSS hooks.
        $this->globalAttributes = ['class', 'id', 'lang', 'dir', 'title', 'aria-label', 'aria-hidden', 'role'];
    }

    public function sanitize(?string $html): string
    {
        if (! is_string($html)) {
            return '';
        }

        $trimmed = trim($html);
        if ($trimmed === '') {
            return '';
        }

        // Wrap content in a neutral container so DOMDocument can parse fragments safely.
        $document = new DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="utf-8" ?><div>'.$trimmed.'</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($internalErrors);
        }

        $container = $document->getElementsByTagName('div')->item(0);
        if (! $container instanceof DOMElement) {
            return '';
        }

        // Recursively sanitize all descendant nodes before extracting the inner HTML.
        $this->sanitizeNode($container);

        $sanitized = '';
        foreach (iterator_to_array($container->childNodes) as $child) {
            $sanitized .= $document->saveHTML($child) ?: '';
        }

        return trim($sanitized);
    }

    private function sanitizeNode(DOMNode $node): void
    {
        if ($node instanceof DOMElement) {
            $tag = strtolower($node->tagName);

            foreach (iterator_to_array($node->childNodes) as $child) {
                $this->sanitizeNode($child);
            }

            if (! in_array($tag, $this->allowedElements, true)) {
                $this->unwrapNode($node);

                return;
            }

            $this->sanitizeAttributes($node, $tag);

            return;
        }

        if ($node instanceof DOMText) {
            // Text nodes are safe by design, nothing to sanitize here.
            return;
        }

        // Drop everything else (comments, processing instructions, etc.).
        $node->parentNode?->removeChild($node);
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
            $name = strtolower($attribute->name);
            $value = $attribute->value;

            if ($this->isAllowedAttribute($tag, $name, $value)) {
                continue;
            }

            $element->removeAttributeNode($attribute);
        }

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $rel = trim($element->getAttribute('rel'). ' noopener noreferrer');
            $rel = implode(' ', array_unique(array_filter(explode(' ', $rel))));
            $element->setAttribute('rel', $rel);
        }

        if ($tag === 'img' && $element->getAttribute('src') === '') {
            // Drop orphan images that lost their source attribute during sanitization.
            $this->unwrapNode($element);
        }
    }

    private function unwrapNode(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (! $parent instanceof DOMNode) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private function isAllowedAttribute(string $tag, string $name, string $value): bool
    {
        if (str_starts_with($name, 'on')) {
            // Event handler attributes are never allowed.
            return false;
        }

        if (str_starts_with($name, 'data-')) {
            return true;
        }

        if (in_array($name, $this->globalAttributes, true)) {
            return true;
        }

        if (! in_array($name, $this->allowedAttributes[$tag] ?? [], true)) {
            return false;
        }

        if ($tag === 'a' && $name === 'href') {
            return $this->isSafeUrl($value);
        }

        if ($tag === 'img' && $name === 'src') {
            return $this->isSafeImageUrl($value);
        }

        if ($tag === 'img' && in_array($name, ['width', 'height'], true)) {
            return ctype_digit((string) $value);
        }

        return true;
    }

    private function isSafeUrl(string $value): bool
    {
        $url = trim($value);
        if ($url === '') {
            return false;
        }

        if ($url[0] === '#' || str_starts_with($url, '/')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($scheme === '' || $scheme === null) {
            return true;
        }

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }

    private function isSafeImageUrl(string $value): bool
    {
        $url = trim($value);
        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($scheme === '' || $scheme === null) {
            return true;
        }

        if (in_array($scheme, ['http', 'https'], true)) {
            return true;
        }

        return str_starts_with($url, 'data:image/');
    }
}
