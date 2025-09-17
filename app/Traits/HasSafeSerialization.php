<?php

declare (strict_types=1);
namespace App\Traits;

/**
 * HasSafeSerialization
 * 
 * Trait providing reusable functionality across multiple classes.
 */
trait HasSafeSerialization
{
    /**
     * Get safe attributes for public display (excludes sensitive fields)
     * 
     * @param array $additionalExclusions Additional fields to exclude
     * @return array
     */
    public function getSafeAttributes(array $additionalExclusions = []): array
    {
        $defaultExclusions = $this->getDefaultSensitiveFields();
        $exclusions = array_merge($defaultExclusions, $additionalExclusions);
        return $this->except($exclusions);
    }
    /**
     * Get safe attributes for API responses
     * 
     * @param array $additionalExclusions Additional fields to exclude
     * @return array
     */
    public function getApiSafeAttributes(array $additionalExclusions = []): array
    {
        $defaultExclusions = $this->getApiSensitiveFields();
        $exclusions = array_merge($defaultExclusions, $additionalExclusions);
        return $this->except($exclusions);
    }
    /**
     * Get safe attributes for admin display (excludes most sensitive fields)
     * 
     * @param array $additionalExclusions Additional fields to exclude
     * @return array
     */
    public function getAdminSafeAttributes(array $additionalExclusions = []): array
    {
        $defaultExclusions = $this->getAdminSensitiveFields();
        $exclusions = array_merge($defaultExclusions, $additionalExclusions);
        return $this->except($exclusions);
    }
    /**
     * Get default sensitive fields that should be excluded from public display
     * 
     * @return array
     */
    protected function getDefaultSensitiveFields(): array
    {
        return ['password', 'remember_token', 'api_token', 'two_factor_secret', 'two_factor_recovery_codes', 'verification_token', 'password_reset_token', 'password_reset_expires_at', 'stripe_customer_id', 'stripe_account_id', 'last_login_ip', 'phone_verified_at', 'two_factor_confirmed_at'];
    }
    /**
     * Get API sensitive fields that should be excluded from API responses
     * 
     * @return array
     */
    protected function getApiSensitiveFields(): array
    {
        return array_merge($this->getDefaultSensitiveFields(), ['email', 'phone_number', 'phone', 'birth_date', 'date_of_birth', 'preferences', 'privacy_settings', 'marketing_preferences', 'social_links', 'notification_preferences', 'referral_code', 'referral_settings', 'subscription_status', 'subscription_plan', 'subscription_ends_at', 'trial_ends_at', 'status']);
    }
    /**
     * Get admin sensitive fields that should be excluded from admin display
     * 
     * @return array
     */
    protected function getAdminSensitiveFields(): array
    {
        return ['password', 'remember_token', 'api_token', 'two_factor_secret', 'two_factor_recovery_codes', 'verification_token', 'password_reset_token', 'password_reset_expires_at'];
    }
    /**
     * Convert model to array with safe attributes for public display
     * 
     * @param array $additionalExclusions Additional fields to exclude
     * @return array
     */
    public function toSafeArray(array $additionalExclusions = []): array
    {
        return $this->getSafeAttributes($additionalExclusions);
    }
    /**
     * Convert model to array with safe attributes for API responses
     * 
     * @param array $additionalExclusions Additional fields to exclude
     * @return array
     */
    public function toApiSafeArray(array $additionalExclusions = []): array
    {
        return $this->getApiSafeAttributes($additionalExclusions);
    }
    /**
     * Convert model to array with safe attributes for admin display
     * 
     * @param array $additionalExclusions Additional fields to exclude
     * @return array
     */
    public function toAdminSafeArray(array $additionalExclusions = []): array
    {
        return $this->getAdminSafeAttributes($additionalExclusions);
    }
}