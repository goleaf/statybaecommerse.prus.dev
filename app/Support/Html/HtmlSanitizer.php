<?php

declare(strict_types=1);

namespace App\Support\Html;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

final class HtmlSanitizer
{
    /**
     * Elements that are preserved during sanitization.
     *
     * @var array<int, string>
     */
    private array $allowedElements = [
        'a',
        'abbr',
        'b',
        'blockquote',
        'br',
        'caption',
        'code',
        'div',
        'em',
        'figcaption',
        'figure',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hr',
        'i',
        'li',
        'ol',
        'p',
        'pre',
        's',
        'span',
        'strong',
        'sub',
        'sup',
        'table',
        'tbody',
        'td',
        'tfoot',
        'th',
        'thead',
        'tr',
        'u',
        'ul',
    ];

    /**
     * Elements that are removed completely together with their content.
     *
     * @var array<int, string>
     */
    private array $dangerousElements = [
        'applet',
        'canvas',
        'embed',
        'form',
        'iframe',
        'input',
        'link',
        'meta',
        'object',
        'script',
        'style',
    ];

    /**
     * Allowed attributes grouped by element. "*" applies to all elements.
     *
     * @var array<string, array<int, string>>
     */
    private array $allowedAttributes = [
        '*' => ['style'],
        'a' => ['href', 'title', 'target', 'rel'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan', 'scope'],
        'table' => ['summary'],
        'ol' => ['start', 'reversed', 'type'],
    ];

    /**
     * Allowed URI schemes for href attributes.
     *
     * @var array<int, string>
     */
    private array $allowedSchemes = ['http', 'https', 'mailto', 'tel'];

    /**
     * Allowed CSS properties for inline styles.
     *
     * @var array<int, string>
     */
    private array $allowedCssProperties = [
        'background-color',
        'color',
        'font-style',
        'font-weight',
        'text-align',
        'text-decoration',
    ];

    /**
     * Allowed keywords per CSS property.
     *
     * @var array<string, array<int, string>>
     */
    private array $allowedCssValues = [
        'font-style' => ['normal', 'italic', 'oblique'],
        'font-weight' => ['normal', 'bold', 'bolder', 'lighter', '600', '700'],
        'text-align' => ['left', 'center', 'right', 'justify'],
        'text-decoration' => ['none', 'underline', 'line-through', 'overline'],
    ];

    /**
     * Basic named colors that are considered safe.
     *
     * @var array<int, string>
     */
    private array $allowedColorKeywords = [
        'black',
        'blue',
        'brown',
        'gray',
        'green',
        'grey',
        'navy',
        'olive',
        'orange',
        'purple',
        'red',
        'silver',
        'teal',
        'white',
        'yellow',
    ];

    public function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $trimmed = trim($html);
        if ($trimmed === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousLibxml = libxml_use_internal_errors(true);

        $document->loadHTML('<?xml encoding="utf-8"?><div>'.$trimmed.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxml);

        $wrapper = $document->getElementsByTagName('div')->item(0);
        if (! $wrapper instanceof DOMElement) {
            return '';
        }

        $this->sanitizeChildren($wrapper);

        return $this->getInnerHtml($wrapper);
    }

    private function sanitizeChildren(DOMNode $node): void
    {
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);
            if (! $child instanceof DOMNode) {
                continue;
            }

            $this->sanitizeNode($child);
        }
    }

    private function sanitizeNode(DOMNode $node): void
    {
        if ($node instanceof DOMElement) {
            $tagName = strtolower($node->tagName);

            if (in_array($tagName, $this->dangerousElements, true)) {
                $node->parentNode?->removeChild($node);

                return;
            }

            if (! in_array($tagName, $this->allowedElements, true)) {
                $this->unwrapNode($node);

                return;
            }

            $this->sanitizeAttributes($node);

            if ($node->hasChildNodes()) {
                $this->sanitizeChildren($node);
            }

            return;
        }

        if ($node instanceof DOMText) {
            return;
        }

        $node->parentNode?->removeChild($node);
    }

    private function unwrapNode(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (! $parent instanceof DOMNode) {
            $element->parentNode?->removeChild($element);

            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private function sanitizeAttributes(DOMElement $element): void
    {
        if (! $element->hasAttributes()) {
            return;
        }

        $attributes = [];
        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->name;
        }

        foreach ($attributes as $attributeName) {
            $lowerName = strtolower($attributeName);

            if (str_starts_with($lowerName, 'on')) {
                $element->removeAttribute($attributeName);

                continue;
            }

            if ($lowerName === 'style') {
                $sanitizedStyle = $this->sanitizeStyle($element->getAttribute($attributeName));
                if ($sanitizedStyle === '') {
                    $element->removeAttribute($attributeName);
                } else {
                    $element->setAttribute($attributeName, $sanitizedStyle);
                }

                continue;
            }

            if (! $this->isAttributeAllowed($element->tagName, $lowerName)) {
                $element->removeAttribute($attributeName);

                continue;
            }

            if ($lowerName === 'href') {
                $sanitized = $this->sanitizeUrl($element->getAttribute($attributeName));
                if ($sanitized === null) {
                    $element->removeAttribute($attributeName);
                } else {
                    $element->setAttribute($attributeName, $sanitized);
                }

                continue;
            }

            if ($lowerName === 'target') {
                $value = strtolower($element->getAttribute($attributeName));
                if ($value !== '_blank') {
                    $element->removeAttribute($attributeName);
                } else {
                    $element->setAttribute($attributeName, '_blank');
                    $this->ensureRelAttributes($element);
                }

                continue;
            }

            if ($lowerName === 'rel') {
                $this->ensureRelAttributes($element);
            }
        }
    }

    private function ensureRelAttributes(DOMElement $element): void
    {
        if ($element->tagName !== 'a') {
            return;
        }

        $rel = strtolower($element->getAttribute('rel'));
        $tokens = array_filter(array_map('trim', explode(' ', $rel)));
        $required = ['noopener', 'noreferrer'];

        foreach ($required as $token) {
            if (! in_array($token, $tokens, true)) {
                $tokens[] = $token;
            }
        }

        if (! empty($tokens)) {
            $element->setAttribute('rel', implode(' ', $tokens));
        }
    }

    private function isAttributeAllowed(string $tagName, string $attribute): bool
    {
        $tagName = strtolower($tagName);

        $globalAllowed = $this->allowedAttributes['*'] ?? [];
        $tagAllowed = $this->allowedAttributes[$tagName] ?? [];

        return in_array($attribute, $globalAllowed, true) || in_array($attribute, $tagAllowed, true);
    }

    private function sanitizeUrl(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $lower = strtolower($value);
        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:')) {
            return null;
        }

        if ($value[0] === '#') {
            return $value;
        }

        if ($value[0] === '/') {
            return $value;
        }

        $parsed = parse_url($value);
        if ($parsed === false) {
            return null;
        }

        if (! isset($parsed['scheme'])) {
            return $value;
        }

        $scheme = strtolower($parsed['scheme']);
        if (! in_array($scheme, $this->allowedSchemes, true)) {
            return null;
        }

        return $value;
    }

    private function sanitizeStyle(string $style): string
    {
        $declarations = array_filter(array_map('trim', explode(';', $style)));
        $sanitized = [];

        foreach ($declarations as $declaration) {
            [$property, $value] = array_map('trim', array_pad(explode(':', $declaration, 2), 2, ''));
            if ($property === '' || $value === '') {
                continue;
            }

            $property = strtolower($property);
            if (! in_array($property, $this->allowedCssProperties, true)) {
                continue;
            }

            $normalizedValue = $this->sanitizeCssValue($property, $value);
            if ($normalizedValue === null) {
                continue;
            }

            $sanitized[] = $property.': '.$normalizedValue;
        }

        return implode('; ', $sanitized);
    }

    private function sanitizeCssValue(string $property, string $value): ?string
    {
        $value = trim($value);
        $lower = strtolower($value);

        if (isset($this->allowedCssValues[$property]) && ! in_array($lower, $this->allowedCssValues[$property], true)) {
            return null;
        }

        return match ($property) {
            'color', 'background-color' => $this->sanitizeColor($value),
            default => $lower,
        };
    }

    private function sanitizeColor(string $value): ?string
    {
        $value = trim($value);
        $lower = strtolower($value);

        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value) === 1) {
            return $lower;
        }

        if (preg_match('/^rgba?\((-?\d+\.?\d*%?,\s*){2}-?\d+\.?\d*%?(,\s*(0|1|0?\.\d+))?\)$/i', $value) === 1) {
            return $lower;
        }

        if (in_array($lower, $this->allowedColorKeywords, true)) {
            return $lower;
        }

        return null;
    }

    private function getInnerHtml(DOMElement $element): string
    {
        $html = '';

        foreach ($element->childNodes as $child) {
            $html .= $element->ownerDocument->saveHTML($child);
        }

        return trim($html);
    }
}
