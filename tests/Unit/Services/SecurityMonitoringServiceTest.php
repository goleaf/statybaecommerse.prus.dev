<?php

declare(strict_types=1);

use App\Services\CacheService;
use App\Services\SecurityMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SecurityMonitoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private SecurityMonitoringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $cacheService = $this->app->make(CacheService::class);
        $this->service = new SecurityMonitoringService($cacheService);
        
        Log::spy();
        Cache::flush();
    }

    /** @test */
    public function it_detects_sql_injection_attempts(): void
    {
        $request = Request::create('/test', 'GET', [
            'search' => "'; DROP TABLE users; --",
        ]);

        $threats = $this->service->monitorRequest($request);

        $this->assertContains('sql_injection', $threats);
        
        Log::shouldHaveReceived('warning')
            ->with('Security threat detected', \Mockery::on(function ($context) {
                return $context['threat_type'] === 'sql_injection';
            }));
    }

    /** @test */
    public function it_detects_xss_attempts(): void
    {
        $request = Request::create('/test', 'POST', [
            'comment' => '<script>alert("xss")</script>',
        ]);

        $threats = $this->service->monitorRequest($request);

        $this->assertContains('xss_attempts', $threats);
    }

    /** @test */
    public function it_detects_path_traversal_attempts(): void
    {
        $request = Request::create('/test', 'GET', [
            'file' => '../../../etc/passwd',
        ]);

        $threats = $this->service->monitorRequest($request);

        $this->assertContains('path_traversal', $threats);
    }

    /** @test */
    public function it_detects_command_injection_attempts(): void
    {
        $request = Request::create('/test', 'POST', [
            'input' => '; cat /etc/passwd',
        ]);

        $threats = $this->service->monitorRequest($request);

        $this->assertContains('command_injection', $threats);
    }

    /** @test */
    public function it_blocks_ip_addresses(): void
    {
        $ipAddress = '192.168.1.100';

        $this->service->blockIp($ipAddress, 60, 'Test block');

        $this->assertTrue($this->service->shouldBlockIp($ipAddress));
        
        Log::shouldHaveReceived('warning')
            ->with('IP address blocked', \Mockery::on(function ($context) use ($ipAddress) {
                return $context['ip_address'] === $ipAddress &&
                       $context['reason'] === 'Test block';
            }));
    }

    /** @test */
    public function it_auto_blocks_after_multiple_threats(): void
    {
        $ipAddress = '192.168.1.101';
        
        // Simulate 5 malicious requests
        for ($i = 0; $i < 5; $i++) {
            $request = Request::create('/test', 'GET', [
                'q' => "'; DROP TABLE test{$i}; --",
            ]);
            $request->server->set('REMOTE_ADDR', $ipAddress);

            $this->service->monitorRequest($request);
        }

        $this->assertTrue($this->service->shouldBlockIp($ipAddress));
    }

    /** @test */
    public function it_sanitizes_sensitive_data_in_logs(): void
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'admin',
            'password' => 'secret123',
            'malicious' => "'; DROP TABLE users; --",
        ]);

        $this->service->monitorRequest($request);

        Log::shouldHaveReceived('warning')
            ->with('Security threat detected', \Mockery::on(function ($context) {
                $requestData = $context['request_data'];
                
                // Should redact password but keep other data
                return isset($requestData['input']['password']) &&
                       $requestData['input']['password'] === '[REDACTED]' &&
                       $requestData['input']['username'] === 'admin';
            }));
    }

    /** @test */
    public function it_handles_clean_requests_without_false_positives(): void
    {
        $request = Request::create('/test', 'GET', [
            'search' => 'normal search query',
            'page' => '1',
        ]);

        $threats = $this->service->monitorRequest($request);

        $this->assertEmpty($threats);
        
        Log::shouldNotHaveReceived('warning');
    }

    /** @test */
    public function it_tracks_request_context_in_threat_logs(): void
    {
        $request = Request::create('/admin/users', 'GET', [
            'filter' => "'; DROP TABLE users; --",
        ]);
        $request->headers->set('User-Agent', 'Mozilla/5.0 Test Browser');
        $request->headers->set('X-Request-ID', 'req_test_123');

        $this->service->monitorRequest($request);

        Log::shouldHaveReceived('warning')
            ->with('Security threat detected', \Mockery::on(function ($context) {
                return $context['url'] === 'http://localhost/admin/users?filter=%27%3B+DROP+TABLE+users%3B+--' &&
                       $context['method'] === 'GET' &&
                       $context['user_agent'] === 'Mozilla/5.0 Test Browser' &&
                       $context['request_id'] === 'req_test_123';
            }));
    }

    /** @test */
    public function it_provides_security_metrics(): void
    {
        $metrics = $this->service->getSecurityMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('threats_detected_today', $metrics);
        $this->assertArrayHasKey('blocked_ips_count', $metrics);
        $this->assertArrayHasKey('top_threat_types', $metrics);
        $this->assertArrayHasKey('suspicious_user_agents', $metrics);
        $this->assertArrayHasKey('failed_login_attempts', $metrics);
    }

    /** @test */
    public function it_handles_unicode_and_encoded_payloads(): void
    {
        $request = Request::create('/test', 'POST', [
            'data' => urlencode('<script>alert("xss")</script>'),
            'unicode' => '&#60;script&#62;alert("xss")&#60;/script&#62;',
        ]);

        $threats = $this->service->monitorRequest($request);

        $this->assertContains('xss_attempts', $threats);
    }

    /** @test */
    public function it_limits_suspicious_activity_tracking(): void
    {
        $ipAddress = '192.168.1.102';
        
        // Generate more than 10 activities
        for ($i = 0; $i < 15; $i++) {
            $request = Request::create('/test', 'GET', [
                'q' => "test{$i}",
            ]);
            $request->server->set('REMOTE_ADDR', $ipAddress);

            $this->service->monitorRequest($request);
        }

        // Should only keep last 10 activities in cache
        $cacheKey = "security:suspicious_activity:{$ipAddress}";
        $activities = Cache::get($cacheKey, []);
        
        $this->assertLessThanOrEqual(10, count($activities));
    }

    /** @test */
    public function it_expires_ip_blocks_automatically(): void
    {
        $ipAddress = '192.168.1.103';

        $this->service->blockIp($ipAddress, 0); // Block for 0 minutes (immediate expiry)

        // Wait a moment for cache to expire
        sleep(1);

        $this->assertFalse($this->service->shouldBlockIp($ipAddress));
    }
}