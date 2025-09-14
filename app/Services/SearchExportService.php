<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

/**
 * SearchExportService
 * 
 * Service class containing SearchExportService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class SearchExportService
{
    private const EXPORT_CACHE_PREFIX = 'search_export:';
    private const EXPORT_CACHE_TTL = 3600; // 1 hour
    private const MAX_EXPORT_RESULTS = 1000;

    /**
     * Handle exportSearchResults functionality with proper error handling.
     * @param array $results
     * @param string $query
     * @param string $format
     * @param array $options
     * @return array
     */
    public function exportSearchResults(array $results, string $query, string $format = 'json', array $options = []): array
    {
        try {
            $format = strtolower($format);
            $exportId = $this->generateExportId($query, $format, $options);
            
            // Limit results for export
            $exportResults = array_slice($results, 0, self::MAX_EXPORT_RESULTS);
            
            $exportData = [
                'export_id' => $exportId,
                'query' => $query,
                'format' => $format,
                'total_results' => count($results),
                'exported_results' => count($exportResults),
                'exported_at' => now()->toISOString(),
                'options' => $options,
                'data' => $this->formatExportData($exportResults, $format, $options),
            ];
            
            // Store export data
            $this->storeExportData($exportId, $exportData);
            
            return [
                'success' => true,
                'export_id' => $exportId,
                'download_url' => $this->generateDownloadUrl($exportId),
                'expires_at' => now()->addHour()->toISOString(),
                'total_results' => count($results),
                'exported_results' => count($exportResults),
                'format' => $format,
            ];
        } catch (\Exception $e) {
            \Log::warning('Search export failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Export failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle getExportData functionality with proper error handling.
     * @param string $exportId
     * @return array|null
     */
    public function getExportData(string $exportId): ?array
    {
        try {
            $cacheKey = self::EXPORT_CACHE_PREFIX . $exportId;
            return Cache::get($cacheKey);
        } catch (\Exception $e) {
            \Log::warning('Export data retrieval failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle generateShareableLink functionality with proper error handling.
     * @param array $results
     * @param string $query
     * @param array $options
     * @return array
     */
    public function generateShareableLink(array $results, string $query, array $options = []): array
    {
        try {
            $shareId = $this->generateShareId($query, $options);
            
            $shareData = [
                'share_id' => $shareId,
                'query' => $query,
                'results_count' => count($results),
                'created_at' => now()->toISOString(),
                'expires_at' => now()->addDays(7)->toISOString(),
                'options' => $options,
                'preview_data' => $this->generatePreviewData($results),
            ];
            
            // Store share data
            $this->storeShareData($shareId, $shareData);
            
            return [
                'success' => true,
                'share_id' => $shareId,
                'share_url' => $this->generateShareUrl($shareId),
                'expires_at' => $shareData['expires_at'],
                'preview' => $shareData['preview_data'],
            ];
        } catch (\Exception $e) {
            \Log::warning('Shareable link generation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Share link generation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle getSharedSearch functionality with proper error handling.
     * @param string $shareId
     * @return array|null
     */
    public function getSharedSearch(string $shareId): ?array
    {
        try {
            $cacheKey = 'search_share:' . $shareId;
            $shareData = Cache::get($cacheKey);
            
            if (!$shareData) {
                return null;
            }
            
            // Check if expired
            if (now()->isAfter($shareData['expires_at'])) {
                Cache::forget($cacheKey);
                return null;
            }
            
            return $shareData;
        } catch (\Exception $e) {
            \Log::warning('Shared search retrieval failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle formatExportData functionality with proper error handling.
     * @param array $results
     * @param string $format
     * @param array $options
     * @return string
     */
    private function formatExportData(array $results, string $format, array $options): string
    {
        return match ($format) {
            'json' => $this->formatAsJson($results, $options),
            'csv' => $this->formatAsCsv($results, $options),
            'xml' => $this->formatAsXml($results, $options),
            'xlsx' => $this->formatAsXlsx($results, $options),
            default => $this->formatAsJson($results, $options),
        };
    }

    /**
     * Handle formatAsJson functionality with proper error handling.
     * @param array $results
     * @param array $options
     * @return string
     */
    private function formatAsJson(array $results, array $options): string
    {
        $jsonOptions = JSON_PRETTY_PRINT;
        
        if (isset($options['minify']) && $options['minify']) {
            $jsonOptions = 0;
        }
        
        return json_encode($results, $jsonOptions);
    }

    /**
     * Handle formatAsCsv functionality with proper error handling.
     * @param array $results
     * @param array $options
     * @return string
     */
    private function formatAsCsv(array $results, array $options): string
    {
        if (empty($results)) {
            return '';
        }
        
        $delimiter = $options['delimiter'] ?? ',';
        $enclosure = $options['enclosure'] ?? '"';
        
        $output = fopen('php://temp', 'r+');
        
        // Write headers
        $headers = array_keys($results[0]);
        fputcsv($output, $headers, $delimiter, $enclosure);
        
        // Write data
        foreach ($results as $result) {
            fputcsv($output, array_values($result), $delimiter, $enclosure);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Handle formatAsXml functionality with proper error handling.
     * @param array $results
     * @param array $options
     * @return string
     */
    private function formatAsXml(array $results, array $options): string
    {
        $rootElement = $options['root_element'] ?? 'search_results';
        $itemElement = $options['item_element'] ?? 'result';
        
        $xml = new \SimpleXMLElement("<{$rootElement}></{$rootElement}>");
        
        foreach ($results as $result) {
            $item = $xml->addChild($itemElement);
            $this->arrayToXml($result, $item);
        }
        
        return $xml->asXML();
    }

    /**
     * Handle formatAsXlsx functionality with proper error handling.
     * @param array $results
     * @param array $options
     * @return string
     */
    private function formatAsXlsx(array $results, array $options): string
    {
        // For XLSX, we'll create a simple CSV-like format that can be opened in Excel
        // In a real implementation, you'd use a library like PhpSpreadsheet
        return $this->formatAsCsv($results, $options);
    }

    /**
     * Handle arrayToXml functionality with proper error handling.
     * @param array $data
     * @param \SimpleXMLElement $xml
     * @return void
     */
    private function arrayToXml(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                $this->arrayToXml($value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }

    /**
     * Handle generateExportId functionality with proper error handling.
     * @param string $query
     * @param string $format
     * @param array $options
     * @return string
     */
    private function generateExportId(string $query, string $format, array $options): string
    {
        $data = [
            'query' => $query,
            'format' => $format,
            'options' => $options,
            'timestamp' => now()->timestamp,
        ];
        
        return 'export_' . md5(serialize($data));
    }

    /**
     * Handle generateShareId functionality with proper error handling.
     * @param string $query
     * @param array $options
     * @return string
     */
    private function generateShareId(string $query, array $options): string
    {
        $data = [
            'query' => $query,
            'options' => $options,
            'timestamp' => now()->timestamp,
        ];
        
        return 'share_' . md5(serialize($data));
    }

    /**
     * Handle storeExportData functionality with proper error handling.
     * @param string $exportId
     * @param array $exportData
     * @return void
     */
    private function storeExportData(string $exportId, array $exportData): void
    {
        $cacheKey = self::EXPORT_CACHE_PREFIX . $exportId;
        Cache::put($cacheKey, $exportData, self::EXPORT_CACHE_TTL);
    }

    /**
     * Handle storeShareData functionality with proper error handling.
     * @param string $shareId
     * @param array $shareData
     * @return void
     */
    private function storeShareData(string $shareId, array $shareData): void
    {
        $cacheKey = 'search_share:' . $shareId;
        Cache::put($cacheKey, $shareData, 7 * 24 * 60 * 60); // 7 days
    }

    /**
     * Handle generateDownloadUrl functionality with proper error handling.
     * @param string $exportId
     * @return string
     */
    private function generateDownloadUrl(string $exportId): string
    {
        return route('api.autocomplete.export.download', ['exportId' => $exportId]);
    }

    /**
     * Handle generateShareUrl functionality with proper error handling.
     * @param string $shareId
     * @return string
     */
    private function generateShareUrl(string $shareId): string
    {
        return route('api.autocomplete.share.view', ['shareId' => $shareId]);
    }

    /**
     * Handle generatePreviewData functionality with proper error handling.
     * @param array $results
     * @return array
     */
    private function generatePreviewData(array $results): array
    {
        $preview = array_slice($results, 0, 5);
        
        return [
            'total_count' => count($results),
            'preview_count' => count($preview),
            'preview_results' => $preview,
            'types_summary' => $this->getTypesSummary($results),
        ];
    }

    /**
     * Handle getTypesSummary functionality with proper error handling.
     * @param array $results
     * @return array
     */
    private function getTypesSummary(array $results): array
    {
        $types = [];
        
        foreach ($results as $result) {
            $type = $result['type'] ?? 'unknown';
            $types[$type] = ($types[$type] ?? 0) + 1;
        }
        
        return $types;
    }

    /**
     * Handle cleanupExpiredExports functionality with proper error handling.
     * @return int
     */
    public function cleanupExpiredExports(): int
    {
        try {
            $cleaned = 0;
            
            // This would typically scan all export cache keys and remove expired ones
            // For now, we'll return a placeholder
            return $cleaned;
        } catch (\Exception $e) {
            \Log::warning('Export cleanup failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Handle getExportStatistics functionality with proper error handling.
     * @return array
     */
    public function getExportStatistics(): array
    {
        try {
            return [
                'total_exports' => 0, // Would be calculated from cache
                'active_exports' => 0,
                'expired_exports' => 0,
                'most_popular_format' => 'json',
                'average_results_per_export' => 0,
            ];
        } catch (\Exception $e) {
            \Log::warning('Export statistics failed: ' . $e->getMessage());
            return [];
        }
    }
}
