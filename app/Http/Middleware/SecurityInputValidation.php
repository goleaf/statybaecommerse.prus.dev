<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security middleware for input validation and threat detection.
 */
final class SecurityInputValidation
{
    private const SQL_INJECTION_PATTERNS = [
        '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
        '/(\b(OR|AND)\s+\d+\s*=\s*\d+)/i',
        '/(\b(OR|AND)\s+[\'"]?\w+[\'"]?\s*=\s*[\'"]?\w+[\'"]?)/i',
        '/(;|\||&|\$\(|\`)/i',
        '/(\bUNION\b.*\bSELECT\b)/i',
        '/(\b(INFORMATION_SCHEMA|SYSOBJECTS|SYSCOLUMNS)\b)/i',
    ];
    
    private const XSS_PATTERNS = [
        '/<script[^>]*>.*?<\/script>/is',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<iframe[^>]*>.*?<\/iframe>/is',
        '/<object[^>]*>.*?<\/object>/is',
        '/<embed[^>]*>/i',
        '/expression\s*\(/i',
        '/vbscript:/i',
    ];
    
    private const PATH_TRAVERSAL_PATTERNS = [
        '/\.\.\//',
        '/\.\.\\\\/',
        '/%2e%2e%2f/i',
        '/%2e%2e%5c/i',
        '/\.\.\%2f/i',
        '/\.\.\%5c/i',
    ];
    
    private const COMMAND_INJECTION_PATTERNS = [
        '/[;&|`$(){}[\]]/i',
        '/\b(cat|ls|pwd|id|whoami|uname|ps|netstat|ifconfig|ping|wget|curl|nc|telnet|ssh|ftp)\b/i',
        '/(\||;|&|\$\(|\`|>|<)/i',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip validation for safe routes
        if ($this->isSafeRoute($request)) {
            return $next($request);
        }
        
        $threats = $this->detectThreats($request);
        
        if (!empty($threats)) {
            $this->logThreat($request, $threats);
            
            // Block high-risk threats
            if ($this->isHighRiskThreat($threats)) {
                return response()->json([
                    'message' => 'Request blocked for security reasons.',
                    'error_code' => 'SECURITY_VIOLATION',
                ], 403);
            }
        }
        
        return $next($request);
    }
    
    private function isSafeRoute(Request $request): bool
    {
        $safeRoutes = [
            'up', // Health check
            'sanctum/csrf-cookie',
        ];
        
        return in_array($request->path(), $safeRoutes, true);
    }
    
    private function detectThreats(Request $request): array
    {
        $threats = [];
        $allInput = array_merge(
            $request->query(),
            $request->request->all(),
            $request->headers->all()
        );
        
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                $threats = array_merge($threats, $this->scanValue($key, $value));
            } elseif (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (is_string($subValue)) {
                        $threats = array_merge($threats, $this->scanValue("{$key}.{$subKey}", $subValue));
                    }
                }
            }
        }
        
        return $threats;
    }
    
    private function scanValue(string $key, string $value): array
    {
        $threats = [];
        
        // SQL Injection Detection
        foreach (self::SQL_INJECTION_PATTERNS as $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = [
                    'type' => 'sql_injection',
                    'field' => $key,
                    'pattern' => $pattern,
                    'severity' => 'high',
                ];
            }
        }
        
        // XSS Detection
        foreach (self::XSS_PATTERNS as $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = [
                    'type' => 'xss',
                    'field' => $key,
                    'pattern' => $pattern,
                    'severity' => 'high',
                ];
            }
        }
        
        // Path Traversal Detection
        foreach (self::PATH_TRAVERSAL_PATTERNS as $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = [
                    'type' => 'path_traversal',
                    'field' => $key,
                    'pattern' => $pattern,
                    'severity' => 'medium',
                ];
            }
        }
        
        // Command Injection Detection
        foreach (self::COMMAND_INJECTION_PATTERNS as $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = [
                    'type' => 'command_injection',
                    'field' => $key,
                    'pattern' => $pattern,
                    'severity' => 'high',
                ];
            }
        }
        
        return $threats;
    }
    
    private function isHighRiskThreat(array $threats): bool
    {
        foreach ($threats as $threat) {
            if ($threat['severity'] === 'high') {
                return true;
            }
        }
        
        return false;
    }
    
    private function logThreat(Request $request, array $threats): void
    {
        Log::warning('Security threat detected', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'threats' => $threats,
            'request_id' => $request->header('X-Request-ID'),
        ]);
    }
}