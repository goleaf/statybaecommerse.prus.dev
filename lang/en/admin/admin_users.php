<?php

return [
    'title' => 'Admin Users',
    'plural' => 'Admin Users',
    'single' => 'Admin User',
    'form' => [
        'tabs' => [
            'basic_information' => 'Basic Information',
            'account_details' => 'Account Details',
        ],
        'sections' => [
            'basic_information' => 'Basic Information',
            'account_details' => 'Account Details',
        ],
        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Confirm Password',
            'email_verified_at' => 'Email Verified',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ],
    ],
    'filters' => [
        'email_verified' => 'Email Verified',
        'verified' => 'Verified',
        'unverified' => 'Unverified',
        'created_at' => 'Created At',
        'recent' => 'Recent (30 days)',
    ],
    'actions' => [
        'verify_email' => 'Verify Email',
        'send_verification' => 'Send Verification',
        'verify_emails' => 'Verify Emails',
        'send_verifications' => 'Send Verifications',
    ],
    'notifications' => [
        'email_verified_successfully' => 'Email verified successfully',
        'verification_sent_successfully' => 'Verification sent successfully',
        'emails_verified_successfully' => 'Emails verified successfully',
        'verifications_sent_successfully' => 'Verifications sent successfully',
    ],
];
