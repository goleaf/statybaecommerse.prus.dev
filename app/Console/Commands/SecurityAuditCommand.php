<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Security audit command to check for common vulnerabilities and misconfigurations.
 */
class SecurityAuditCommand extends Command
{
    protected $signature = 'security:audit {--fix : Attempt to fix issues automatically}';
    protected $description = 'Perform a security audit of the application';

    private array $issues = [];
    private array $recommendations = [];

    public function handle(): int
    {
        $this->info('Starting security audit...');

        $this->checkEnvironmentConfiguration();
        $this->checkDatabaseSecurity();
        $this->checkFilePermissions();
        $this->checkSecurityHeaders();
        $this->checkAuthenticationSecurity();
        $this->checkSessionSecurity();
        $this->checkLoggingSecurity();
        $this->checkDependencySecurity();

        $this->displayResults();

        return $this->issues === [] ? 0 : 1;
    }

    private function checkEnvironmentConfiguration(): void
    {
        $this->info('Checking environment configuration...');

        // Check APP_DEBUG in production
        if (config('app.env') === 'production' && config('app.debug')) {
            $this->addIssue('HIGH', 'APP_DEBUG is enabled in production', 'Set APP_DEBUG=false in production');
        }

        // Check APP_KEY is set
        if (empty(config('app.key'))) {
            $this->addIssue('CRITICAL', 'APP_KEY is not set', 'Generate an application key with php artisan key:generate');
        }

        // Check for default/weak keys
        $defaultKeys = [
            'base64:YWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWE=',
            'base64:your-app-key',
        ];
        
        if (in_array(config('app.key'), $defaultKeys)) {
            $this->addIssue('CRITICAL', 'Using default/example APP_KEY', 'Generate a new application key');
        }

        // Check HTTPS configuration
        if (config('app.env') === 'production' && !str_starts_with(config('app.url'), 'https://')) {
            $this->addIssue('HIGH', 'APP_URL does not use HTTPS in production', 'Update APP_URL to use HTTPS');
        }
    }

    private function checkDatabaseSecurity(): void
    {
        $this->info('Checking database security...');

        // Check for default database credentials
        $dbPassword = config('database.connections.' . config('database.default') . '.password');
        
        if (empty($dbPassword) && config('app.env') === 'production') {
            $this->addIssue('HIGH', 'Database password is empty in production', 'Set a strong database password');
        }

        $weakPasswords = ['password', '123456', 'admin', 'root', 'test'];
        if (in_array($dbPassword, $weakPasswords)) {
            $this->addIssue('HIGH', 'Weak database password detected', 'Use a strong, unique database password');
        }

        // Check database encryption
        if (!config('database.connections.' . config('database.default') . '.encrypt', false) && 
            config('app.env') === 'production') {
            $this->addIssue('MEDIUM', 'Database connection is not encrypted', 'Enable SSL/TLS for database connections');
        }
    }

    private function checkFilePermissions(): void
    {
        $this->info('Checking file permissions...');

        $sensitiveFiles = [
            '.env' => '600',
            'storage/logs' => '755',
            'bootstrap/cache' => '755',
        ];

        foreach ($sensitiveFiles as $file => $expectedPerms) {
            $fullPath = base_path($file);
            
            if (File::exists($fullPath)) {
                $currentPerms = substr(sprintf('%o', fileperms($fullPath)), -3);
                
                if ($currentPerms !== $expectedPerms) {
                    $this->addIssue('MEDIUM', "Incorrect permissions on {$file}", "Set permissions to {$expectedPerms}");
                }
            }
        }

        // Check for publicly accessible sensitive files
        $publicSensitiveFiles = [
            'public/.env',
            'public/composer.json',
            'public/composer.lock',
        ];

        foreach ($publicSensitiveFiles as $file) {
            if (File::exists(base_path($file))) {
                $this->addIssue('HIGH', "Sensitive file {$file} is publicly accessible", "Move file outside public directory");
            }
        }
    }

    private function checkSecurityHeaders(): void
    {
        $this->info('Checking security headers configuration...');

        if (!config('security.headers.enabled', true)) {
            $this->addIssue('MEDIUM', 'Security headers are disabled', 'Enable security headers in config/security.php');
        }

        // Check HSTS configuration
        if (!config('security.headers.hsts.enabled', true)) {
            $this->addIssue('MEDIUM', 'HSTS is disabled', 'Enable HSTS for HTTPS sites');
        }

        $hstsMaxAge = config('security.headers.hsts.max_age', 0);
        if ($hstsMaxAge < 31536000) { // Less than 1 year
            $this->addIssue('LOW', 'HSTS max-age is too short', 'Set HSTS max-age to at least 31536000 (1 year)');
        }
    }

    private function checkAuthenticationSecurity(): void
    {
        $this->info('Checking authentication security...');

        // Check password timeout
        $passwordTimeout = config('auth.password_timeout', 10800);
        if ($passwordTimeout > 10800) { // More than 3 hours
            $this->addIssue('LOW', 'Password confirmation timeout is too long', 'Reduce AUTH_PASSWORD_TIMEOUT to 3600 or less');
        }

        // Check rate limiting configuration
        $loginAttempts = config('security.rate_limiting.auth.login.max_attempts', 5);
        if ($loginAttempts > 10) {
            $this->addIssue('MEDIUM', 'Login rate limiting is too permissive', 'Reduce max login attempts to 5 or less');
        }

        // Check two-factor authentication
        if (!config('filament.enabled_two_factor', false)) {
            $this->addRecommendation('Enable two-factor authentication for admin users');
        }
    }

    private function checkSessionSecurity(): void
    {
        $this->info('Checking session security...');

        // Check session driver
        if (config('session.driver') === 'file' && config('app.env') === 'production') {
            $this->addIssue('MEDIUM', 'Using file session driver in production', 'Use database or Redis for session storage');
        }

        // Check session security settings
        if (!config('session.http_only', true)) {
            $this->addIssue('HIGH', 'Session cookies are not HTTP-only', 'Set SESSION_HTTP_ONLY=true');
        }

        if (config('session.same_site') !== 'strict' && config('session.same_site') !== 'lax') {
            $this->addIssue('MEDIUM', 'Session SameSite attribute not set', 'Set SESSION_SAME_SITE=lax or strict');
        }

        if (!config('session.secure_cookie') && config('app.env') === 'production') {
            $this->addIssue('HIGH', 'Session cookies are not secure in production', 'Set SESSION_SECURE_COOKIE=true for HTTPS');
        }
    }

    private function checkLoggingSecurity(): void
    {
        $this->info('Checking logging security...');

        // Check log level in production
        if (config('logging.level') === 'debug' && config('app.env') === 'production') {
            $this->addIssue('MEDIUM', 'Debug logging enabled in production', 'Set LOG_LEVEL=error or warning in production');
        }

        // Check security logging configuration
        if (!config('exception-handling.security.redact_sensitive_data', true)) {
            $this->addIssue('HIGH', 'Sensitive data redaction is disabled', 'Enable EXCEPTION_REDACT_SENSITIVE_DATA');
        }

        if (!config('exception-handling.security.prevent_log_injection', true)) {
            $this->addIssue('HIGH', 'Log injection prevention is disabled', 'Enable EXCEPTION_PREVENT_LOG_INJECTION');
        }
    }

    private function checkDependencySecurity(): void
    {
        $this->info('Checking dependency security...');

        // Check for composer.lock
        if (!File::exists(base_path('composer.lock'))) {
            $this->addIssue('MEDIUM', 'composer.lock file missing', 'Run composer install to generate lock file');
        }

        // Check for known vulnerable packages (basic check)
        $composerJson = json_decode(File::get(base_path('composer.json')), true);
        
        if (isset($composerJson['require']['laravel/framework'])) {
            $laravelVersion = $composerJson['require']['laravel/framework'];
            
            // Basic version check (in real implementation, use security advisories)
            if (str_contains($laravelVersion, '^10.') || str_contains($laravelVersion, '^11.')) {
                $this->addRecommendation('Consider upgrading to Laravel 12 for latest security fixes');
            }
        }
    }

    private function addIssue(string $severity, string $description, string $recommendation): void
    {
        $this->issues[] = [
            'severity' => $severity,
            'description' => $description,
            'recommendation' => $recommendation,
        ];
    }

    private function addRecommendation(string $recommendation): void
    {
        $this->recommendations[] = $recommendation;
    }

    private function displayResults(): void
    {
        $this->newLine();
        
        if ($this->issues === []) {
            $this->info('✅ No security issues found!');
        } else {
            $this->error('🚨 Security issues found:');
            $this->newLine();

            $criticalCount = 0;
            $highCount = 0;
            $mediumCount = 0;
            $lowCount = 0;

            foreach ($this->issues as $issue) {
                $color = match ($issue['severity']) {
                    'CRITICAL' => 'red',
                    'HIGH' => 'yellow',
                    'MEDIUM' => 'blue',
                    'LOW' => 'gray',
                    default => 'white',
                };

                $this->line("<fg={$color}>[{$issue['severity']}]</> {$issue['description']}");
                $this->line("  → {$issue['recommendation']}");
                $this->newLine();

                match ($issue['severity']) {
                    'CRITICAL' => $criticalCount++,
                    'HIGH' => $highCount++,
                    'MEDIUM' => $mediumCount++,
                    'LOW' => $lowCount++,
                };
            }

            $this->info("Summary: {$criticalCount} critical, {$highCount} high, {$mediumCount} medium, {$lowCount} low");
        }

        if ($this->recommendations !== []) {
            $this->newLine();
            $this->info('💡 Recommendations:');
            
            foreach ($this->recommendations as $recommendation) {
                $this->line("  • {$recommendation}");
            }
        }
    }
}