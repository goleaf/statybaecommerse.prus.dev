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
            'roles_permissions' => 'Roles & Permissions',
        ],
        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Confirm Password',
            'email_verified_at' => 'Email Verified',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'roles' => 'Roles',
            'audit_reason' => 'Reason for change',
        ],
        'helpers' => [
            'roles' => 'Select the administrative roles to assign to this user.',
            'audit_reason' => 'Explain why these role assignments are being modified.',
        ],
    ],
    'filters' => [
        'email_verified' => 'Email Verified',
        'verified' => 'Verified',
        'unverified' => 'Unverified',
        'created_at' => 'Created At',
        'created_from' => 'Created From',
        'created_until' => 'Created Until',
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
