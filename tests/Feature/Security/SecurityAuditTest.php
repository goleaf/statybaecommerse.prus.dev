<?php

declare(strict_types=1);

use App\Http\Middleware\SecurityInputValidation;
use App\Models\AdminUser;
use App\Models\User;
use App\Support\Database\SecureQueryBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Comprehensive security audit tests.
 */
class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_prevents_mass_assignment_of_passwords(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'should-not-be-set',
        ];

        // Password should not be in fillable array
        $user = new User();
        $this->assertNotContains('password', $user->getFillable());
        
        // Create user without password to test mass assignment protection
        $userDataSafe = [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ];
        
        $user = User::create($userDataSafe);
        $this->assertNull($user->password);
    }

    /** @test */
    public function it_enforces_password_security_requirements(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Test weak passwords
        $weakPasswords = [
            'weak',           // Too short
            'password',       // No uppercase, numbers, or special chars
            'PASSWORD',       // No lowercase, numbers, or special chars
            'Password',       // No numbers or special chars
            'Password1',      // No special chars
        ];

        foreach ($weakPasswords as $weakPassword) {
            $this->expectException(InvalidArgumentException::class);
            $user->password = $weakPassword;
        }
    }

    /** @test */
    public function it_accepts_secure_passwords(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $securePassword = 'SecureP@ssw0rd!';
        $user->password = $securePassword;

        $this->assertTrue(Hash::check($securePassword, $user->password));
    }

    /** @test */
    public function it_detects_sql_injection_attempts(): void
    {
        $middleware = new SecurityInputValidation();
        
        $request = Request::create('/test', 'POST', [
            'search' => "'; DROP TABLE users; --",
            'filter' => "1' OR '1'='1",
        ]);

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function it_detects_xss_attempts(): void
    {
        $middleware = new SecurityInputValidation();
        
        $request = Request::create('/test', 'POST', [
            'comment' => '<script>alert("XSS")</script>',
            'description' => 'javascript:alert("XSS")',
        ]);

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function it_detects_path_traversal_attempts(): void
    {
        $middleware = new SecurityInputValidation();
        
        $request = Request::create('/test', 'POST', [
            'file' => '../../../etc/passwd',
            'path' => '..\\..\\windows\\system32\\config\\sam',
        ]);

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function it_allows_safe_requests(): void
    {
        $middleware = new SecurityInputValidation();
        
        // Create a request to a safe route
        $request = Request::create('/up', 'GET');

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_validates_secure_query_builder_json_extraction(): void
    {
        $query = User::query();
        
        // Valid JSON extraction
        $result = SecureQueryBuilder::jsonExtract($query, 'preferences', '$.theme', 'LIKE', '%dark%');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $result);
        
        // Invalid column name should throw exception
        $this->expectException(InvalidArgumentException::class);
        SecureQueryBuilder::jsonExtract($query, 'invalid-column', '$.theme', 'LIKE', '%dark%');
    }

    /** @test */
    public function it_validates_secure_query_builder_aggregation(): void
    {
        $query = User::query();
        
        // Valid aggregation
        $result = SecureQueryBuilder::safeAggregate($query, 'COUNT', '*', 'total_users');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $result);
        
        // Invalid function should throw exception
        $this->expectException(InvalidArgumentException::class);
        SecureQueryBuilder::safeAggregate($query, 'INVALID_FUNC', 'id', 'result');
    }

    /** @test */
    public function it_has_secure_cors_configuration(): void
    {
        $corsConfig = config('cors');
        
        // CORS should be configured
        $this->assertIsArray($corsConfig);
        
        // Should not allow all origins by default
        $allowedOrigins = $corsConfig['allowed_origins'] ?? [];
        $this->assertNotContains('*', $allowedOrigins);
        
        // Should support credentials for authenticated requests
        $this->assertTrue($corsConfig['supports_credentials'] ?? false);
    }

    /** @test */
    public function it_has_secure_session_configuration(): void
    {
        $sessionConfig = config('session');
        
        // Session should be encrypted (check if encryption is enabled in config)
        $this->assertTrue($sessionConfig['encrypt'] ?? false);
        
        // Session should be HTTP only
        $this->assertTrue($sessionConfig['http_only']);
        
        // Session should have secure same-site policy
        $this->assertContains($sessionConfig['same_site'], ['lax', 'strict']);
        
        // Session lifetime should be reasonable (not too long)
        $this->assertLessThanOrEqual(120, $sessionConfig['lifetime']);
    }

    /** @test */
    public function it_has_proper_security_headers_configuration(): void
    {
        $securityConfig = config('security.headers');
        
        // Security headers should be enabled
        $this->assertTrue($securityConfig['enabled']);
        
        // Should have proper security header values
        $headers = $securityConfig['values'];
        $this->assertEquals('DENY', $headers['X-Frame-Options']);
        $this->assertEquals('nosniff', $headers['X-Content-Type-Options']);
        $this->assertEquals('same-origin', $headers['Cross-Origin-Opener-Policy']);
    }

    /** @test */
    public function it_sanitizes_error_messages_properly(): void
    {
        $sanitizer = new \App\Support\Exceptions\ErrorMessageSanitizer();
        
        $sensitiveMessage = 'Database error: password=secret123 api_key=abc123';
        $sanitized = $sanitizer->sanitizeMessage($sensitiveMessage);
        
        $this->assertStringNotContainsString('secret123', $sanitized);
        $this->assertStringNotContainsString('abc123', $sanitized);
        $this->assertStringContainsString('[REDACTED]', $sanitized);
    }

    /** @test */
    public function it_prevents_information_disclosure_in_file_paths(): void
    {
        $sanitizer = new \App\Support\Exceptions\ErrorMessageSanitizer();
        
        $filePath = '/var/www/html/app/Models/User.php';
        $sanitized = $sanitizer->sanitizeFilePath($filePath);
        
        $this->assertStringNotContainsString('/var/www/html', $sanitized);
        $this->assertStringContainsString('[APP_ROOT]', $sanitized);
    }

    /** @test */
    public function it_has_rate_limiting_configured(): void
    {
        $securityConfig = config('security.rate_limiting');
        
        // Rate limiting should be configured for auth endpoints
        $this->assertArrayHasKey('auth', $securityConfig);
        $this->assertArrayHasKey('login', $securityConfig['auth']);
        
        // Login attempts should be limited
        $loginConfig = $securityConfig['auth']['login'];
        $this->assertLessThanOrEqual(10, $loginConfig['max_attempts']);
        $this->assertGreaterThanOrEqual(60, $loginConfig['decay_seconds']);
    }

    /** @test */
    public function it_validates_admin_user_security(): void
    {
        $adminUser = new AdminUser([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Password should not be mass assignable
        $this->assertNotContains('password', $adminUser->getFillable());
        
        // Should use secure password handling
        $this->assertContains(\App\Traits\SecurePasswordHandling::class, class_uses($adminUser));
    }
}