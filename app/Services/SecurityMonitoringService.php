<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Security monitoring and threat detection service.
 */
final class SecurityMonitoringService
{
    private const SUSPICIOUS_IP_CACHE_KEY = 'security:suspicious_ips';
    private const BLOCKED_IP_CACHE_KEY = 'security:blocked_ips';
    private const THREAT_COUNTER_CACHE_KEY = 'security:threat_count';
    
    public function __construct(
        private readonly int $suspiciousThreshold = 10,
        private readonly int $blockThreshold = 20,
        private readonly int $decayMinutes = 15
    ) {}
    
    /**
     * Monitor request for security threats.
     */
    public function monitorRequest(Request $request): array
    {
        $ip = $request->ip();
        $threats = [];
        
        // Check if IP is already blocked
        if ($this->isIpBlocked($ip)) {
            $threats[] = [
                'type' => 'blocked_ip',
                'severity' => 'critical',
                'message' => 'Request from blocked IP address',
            ];
        }
        
        // Analyze request for threats
        $detectedThreats = $this->analyzeRequest($request);
        $threats = array_merge($threats, $detectedThreats);
        
        // Update threat counters
        if (!empty($detectedThreats)) {
            $this->updateThreatCounters($ip, count($detectedThreats));
        }
        
        // Check if IP should be blocked
        if ($this->shouldBlockIp($ip)) {
            $this->blockIp($ip);
            $threats[] = [
                'type' => 'ip_auto_blocked',
                'severity' => 'critical',
                'message' => 'IP automatically blocked due to repeated threats',
            ];
        }
        
        return $threats;
    }
    
    /**
     * Analyze request for security threats.
     */
    private function analyzeRequest(Request $request): array
    {
        $threats = [];
        
        // Check for SQL injection patterns
        if ($this->detectSqlInjection($request)) {
            $threats[] = [
                'type' => 'sql_injection',
                'severity' => 'high',
                'message' => 'SQL injection attempt detected',
            ];
        }
        
        // Check for XSS patterns
        if ($this->detectXss($request)) {
            $threats[] = [
                'type' => 'xss',
                'severity' => 'high',
                'message' => 'XSS attempt detected',
            ];
        }
        
        // Check for path traversal
        if ($this->detectPathTraversal($request)) {
            $threats[] = [
                'type' => 'path_traversal',
                'severity' => 'medium',
                'message' => 'Path traversal attempt detected',
            ];
        }
        
        // Check for command injection
        if ($this->detectCommandInjection($request)) {
            $threats[] = [
                'type' => 'command_injection',
                'severity' => 'high',
                'message' => 'Command injection attempt detected',
            ];
        }
        
        // Check for suspicious user agents
        if ($this->detectSuspiciousUserAgent($request)) {
            $threats[] = [
                'type' => 'suspicious_user_agent',
                'severity' => 'low',
                'message' => 'Suspicious user agent detected',
            ];
        }
        
        return $threats;
    }
    
    /**
     * Detect SQL injection attempts.
     */
    private function detectSqlInjection(Request $request): bool
    {
        $patterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
            '/(\b(OR|AND)\s+\d+\s*=\s*\d+)/i',
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\b(INFORMATION_SCHEMA|SYSOBJECTS|SYSCOLUMNS)\b)/i',
        ];
        
        return $this->checkPatterns($request, $patterns);
    }
    
    /**
     * Detect XSS attempts.
     */
    private function detectXss(Request $request): bool
    {
        $patterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/expression\s*\(/i',
        ];
        
        return $this->checkPatterns($request, $patterns);
    }
    
    /**
     * Detect path traversal attempts.
     */
    private function detectPathTraversal(Request $request): bool
    {
        $patterns = [
            '/\.\.\//',
            '/\.\.\\\\/',
            '/%2e%2e%2f/i',
            '/%2e%2e%5c/i',
        ];
        
        return $this->checkPatterns($request, $patterns);
    }
    
    /**
     * Detect command injection attempts.
     */
    private function detectCommandInjection(Request $request): bool
    {
        $patterns = [
            '/[;&|`$(){}[\]]/i',
            '/\b(cat|ls|pwd|id|whoami|uname|ps|netstat|ifconfig|ping|wget|curl|nc|telnet|ssh|ftp)\b/i',
            '/(\||;|&|\$\(|\`|>|<)/i',
        ];
        
        return $this->checkPatterns($request, $patterns);
    }
    
    /**
     * Detect suspicious user agents.
     */
    private function detectSuspiciousUserAgent(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');
        
        $suspiciousPatterns = [
            'sqlmap',
            'nikto',
            'nessus',
            'openvas',
            'nmap',
            'masscan',
            'zap',
            'burp',
            'w3af',
            'havij',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check patterns against request data.
     */
    private function checkPatterns(Request $request, array $patterns): bool
    {
        $allInput = array_merge(
            $request->query(),
            $request->request->all(),
            [$request->getPathInfo(), $request->userAgent()]
        );
        
        foreach ($allInput as $value) {
            if (!is_string($value)) {
                continue;
            }
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Update threat counters for IP.
     */
    private function updateThreatCounters(string $ip, int $threatCount): void
    {
        $key = self::THREAT_COUNTER_CACHE_KEY . ':' . $ip;
        $current = Cache::get($key, 0);
        Cache::put($key, $current + $threatCount, now()->addMinutes($this->decayMinutes));
        
        // Mark as suspicious if threshold exceeded
        if ($current + $threatCount >= $this->suspiciousThreshold) {
            $this->markIpSuspicious($ip);
        }
    }
    
    /**
     * Mark IP as suspicious.
     */
    private function markIpSuspicious(string $ip): void
    {
        $suspiciousIps = Cache::get(self::SUSPICIOUS_IP_CACHE_KEY, []);
        $suspiciousIps[$ip] = now()->timestamp;
        Cache::put(self::SUSPICIOUS_IP_CACHE_KEY, $suspiciousIps, now()->addHours(24));
        
        Log::warning('IP marked as suspicious', [
            'ip' => $ip,
            'threat_count' => Cache::get(self::THREAT_COUNTER_CACHE_KEY . ':' . $ip, 0),
        ]);
    }
    
    /**
     * Check if IP should be blocked.
     */
    private function shouldBlockIp(string $ip): bool
    {
        $threatCount = Cache::get(self::THREAT_COUNTER_CACHE_KEY . ':' . $ip, 0);
        return $threatCount >= $this->blockThreshold;
    }
    
    /**
     * Block IP address.
     */
    private function blockIp(string $ip): void
    {
        $blockedIps = Cache::get(self::BLOCKED_IP_CACHE_KEY, []);
        $blockedIps[$ip] = now()->timestamp;
        Cache::put(self::BLOCKED_IP_CACHE_KEY, $blockedIps, now()->addHours(24));
        
        Log::critical('IP address blocked', [
            'ip' => $ip,
            'threat_count' => Cache::get(self::THREAT_COUNTER_CACHE_KEY . ':' . $ip, 0),
        ]);
    }
    
    /**
     * Check if IP is blocked.
     */
    public function isIpBlocked(string $ip): bool
    {
        $blockedIps = Cache::get(self::BLOCKED_IP_CACHE_KEY, []);
        return isset($blockedIps[$ip]);
    }
    
    /**
     * Unblock IP address.
     */
    public function unblockIp(string $ip): void
    {
        $blockedIps = Cache::get(self::BLOCKED_IP_CACHE_KEY, []);
        unset($blockedIps[$ip]);
        Cache::put(self::BLOCKED_IP_CACHE_KEY, $blockedIps, now()->addHours(24));
        
        // Also clear threat counters
        Cache::forget(self::THREAT_COUNTER_CACHE_KEY . ':' . $ip);
        
        Log::info('IP address unblocked', ['ip' => $ip]);
    }
    
    /**
     * Get security metrics.
     */
    public function getSecurityMetrics(): array
    {
        $suspiciousIps = Cache::get(self::SUSPICIOUS_IP_CACHE_KEY, []);
        $blockedIps = Cache::get(self::BLOCKED_IP_CACHE_KEY, []);
        
        return [
            'suspicious_ips_count' => count($suspiciousIps),
            'blocked_ips_count' => count($blockedIps),
            'suspicious_ips' => array_keys($suspiciousIps),
            'blocked_ips' => array_keys($blockedIps),
        ];
    }
}