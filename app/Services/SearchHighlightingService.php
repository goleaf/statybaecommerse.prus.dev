<?php

declare (strict_types=1);
namespace App\Services;

use Illuminate\Support\Str;
/**
 * SearchHighlightingService
 * 
 * Service class containing SearchHighlightingService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class SearchHighlightingService
{
    private const HIGHLIGHT_TAG = '<mark class="search-highlight">';
    private const HIGHLIGHT_TAG_CLOSE = '</mark>';
    private const MAX_SNIPPET_LENGTH = 150;
    private const SNIPPET_PADDING = 20;
    /**
     * Handle highlightSearchTerms functionality with proper error handling.
     * @param string $text
     * @param string $query
     * @return string
     */
    public function highlightSearchTerms(string $text, string $query): string
    {
        if (empty($query) || empty($text)) {
            return $text;
        }
        $searchTerms = $this->extractSearchTerms($query);
        $highlightedText = $text;
        foreach ($searchTerms as $term) {
            $highlightedText = $this->highlightTerm($highlightedText, $term);
        }
        return $highlightedText;
    }
    /**
     * Handle createSnippet functionality with proper error handling.
     * @param string $text
     * @param string $query
     * @param int $maxLength
     * @return string
     */
    public function createSnippet(string $text, string $query, int $maxLength = self::MAX_SNIPPET_LENGTH): string
    {
        if (empty($text)) {
            return '';
        }
        $searchTerms = $this->extractSearchTerms($query);
        $snippet = $this->extractSnippet($text, $searchTerms, $maxLength);
        return $this->highlightSearchTerms($snippet, $query);
    }
    /**
     * Handle highlightResult functionality with proper error handling.
     * @param array $result
     * @param string $query
     * @param array $fields
     * @return array
     */
    public function highlightResult(array $result, string $query, array $fields = ['title', 'subtitle', 'description']): array
    {
        foreach ($fields as $field) {
            if (isset($result[$field]) && is_string($result[$field])) {
                $result[$field] = $this->highlightSearchTerms($result[$field], $query);
            }
        }
        return $result;
    }
    /**
     * Handle highlightResults functionality with proper error handling.
     * @param array $results
     * @param string $query
     * @param array $fields
     * @return array
     */
    public function highlightResults(array $results, string $query, array $fields = ['title', 'subtitle', 'description']): array
    {
        return array_map(function ($result) use ($query, $fields) {
            return $this->highlightResult($result, $query, $fields);
        }, $results);
    }
    /**
     * Handle extractSearchTerms functionality with proper error handling.
     * @param string $query
     * @return array
     */
    private function extractSearchTerms(string $query): array
    {
        // Remove special characters and split by spaces
        $cleanQuery = preg_replace('/[^\w\s]/', ' ', $query);
        $terms = array_filter(explode(' ', $cleanQuery));
        // Remove short terms (less than 2 characters)
        $terms = array_filter($terms, fn($term) => strlen($term) >= 2);
        // Sort by length (longer terms first for better highlighting)
        usort($terms, fn($a, $b) => strlen($b) <=> strlen($a));
        return array_unique($terms);
    }
    /**
     * Handle highlightTerm functionality with proper error handling.
     * @param string $text
     * @param string $term
     * @return string
     */
    private function highlightTerm(string $text, string $term): string
    {
        $pattern = '/\b(' . preg_quote($term, '/') . ')\b/i';
        $replacement = self::HIGHLIGHT_TAG . '$1' . self::HIGHLIGHT_TAG_CLOSE;
        return preg_replace($pattern, $replacement, $text);
    }
    /**
     * Handle extractSnippet functionality with proper error handling.
     * @param string $text
     * @param array $searchTerms
     * @param int $maxLength
     * @return string
     */
    private function extractSnippet(string $text, array $searchTerms, int $maxLength): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        // Find the best position to start the snippet
        $bestPosition = $this->findBestSnippetPosition($text, $searchTerms, $maxLength);
        // Extract snippet around the best position
        $start = max(0, $bestPosition - self::SNIPPET_PADDING);
        $snippet = substr($text, $start, $maxLength);
        // Add ellipsis if needed
        if ($start > 0) {
            $snippet = '...' . $snippet;
        }
        if ($start + $maxLength < strlen($text)) {
            $snippet = $snippet . '...';
        }
        return $snippet;
    }
    /**
     * Handle findBestSnippetPosition functionality with proper error handling.
     * @param string $text
     * @param array $searchTerms
     * @param int $maxLength
     * @return int
     */
    private function findBestSnippetPosition(string $text, array $searchTerms, int $maxLength): int
    {
        $bestPosition = 0;
        $bestScore = 0;
        foreach ($searchTerms as $term) {
            $positions = $this->findTermPositions($text, $term);
            foreach ($positions as $position) {
                $score = $this->calculateSnippetScore($text, $position, $searchTerms, $maxLength);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestPosition = $position;
                }
            }
        }
        return $bestPosition;
    }
    /**
     * Handle findTermPositions functionality with proper error handling.
     * @param string $text
     * @param string $term
     * @return array
     */
    private function findTermPositions(string $text, string $term): array
    {
        $positions = [];
        $offset = 0;
        while (($pos = stripos($text, $term, $offset)) !== false) {
            $positions[] = $pos;
            $offset = $pos + 1;
        }
        return $positions;
    }
    /**
     * Handle calculateSnippetScore functionality with proper error handling.
     * @param string $text
     * @param int $position
     * @param array $searchTerms
     * @param int $maxLength
     * @return int
     */
    private function calculateSnippetScore(string $text, int $position, array $searchTerms, int $maxLength): int
    {
        $score = 0;
        $snippetStart = max(0, $position - self::SNIPPET_PADDING);
        $snippetEnd = min(strlen($text), $snippetStart + $maxLength);
        $snippet = substr($text, $snippetStart, $snippetEnd - $snippetStart);
        // Count occurrences of search terms in snippet
        foreach ($searchTerms as $term) {
            $count = substr_count(strtolower($snippet), strtolower($term));
            $score += $count * 10;
        }
        // Bonus for terms near the beginning of snippet
        $relativePosition = $position - $snippetStart;
        if ($relativePosition < $maxLength / 3) {
            $score += 5;
        }
        return $score;
    }
    /**
     * Handle createSummary functionality with proper error handling.
     * @param string $text
     * @param string $query
     * @param int $maxLength
     * @return string
     */
    public function createSummary(string $text, string $query, int $maxLength = 200): string
    {
        $snippet = $this->createSnippet($text, $query, $maxLength);
        // If snippet is shorter than max length, try to get more context
        if (strlen($snippet) < $maxLength * 0.8) {
            $extendedSnippet = $this->createSnippet($text, $query, $maxLength * 1.5);
            if (strlen($extendedSnippet) > strlen($snippet)) {
                $snippet = $extendedSnippet;
            }
        }
        return $snippet;
    }
    /**
     * Handle getSearchSuggestions functionality with proper error handling.
     * @param string $query
     * @param array $results
     * @return array
     */
    public function getSearchSuggestions(string $query, array $results): array
    {
        $suggestions = [];
        $searchTerms = $this->extractSearchTerms($query);
        foreach ($results as $result) {
            if (isset($result['title'])) {
                $titleWords = $this->extractWords($result['title']);
                foreach ($titleWords as $word) {
                    if (strlen($word) >= 3 && !in_array(strtolower($word), array_map('strtolower', $searchTerms))) {
                        $suggestions[] = $word;
                    }
                }
            }
        }
        // Count word frequency and return top suggestions
        $wordCounts = array_count_values($suggestions);
        arsort($wordCounts);
        return array_slice(array_keys($wordCounts), 0, 5);
    }
    /**
     * Handle extractWords functionality with proper error handling.
     * @param string $text
     * @return array
     */
    private function extractWords(string $text): array
    {
        $words = preg_split('/\s+/', $text);
        return array_filter($words, fn($word) => strlen($word) >= 3);
    }
}