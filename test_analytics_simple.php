<?php

// Simple test script to check AnalyticsEvent functionality

require_once 'vendor/autoload.php';

use App\Models\AnalyticsEvent;
use App\Models\User;

// Create a simple test
echo "Testing AnalyticsEvent model...\n";

try {
    // Test basic model creation
    $user = new User;
    $user->name = 'Test User';
    $user->email = 'test@example.com';
    $user->password = bcrypt('password');
    $user->save();

    echo "User created successfully\n";

    // Test AnalyticsEvent creation
    $event = new AnalyticsEvent;
    $event->event_name = 'Test Event';
    $event->event_type = 'page_view';
    $event->user_id = $user->id;
    $event->session_id = 'test-session-123';
    $event->save();

    echo "AnalyticsEvent created successfully\n";
    echo 'Event ID: '.$event->id."\n";
    echo 'Event Name: '.$event->event_name."\n";
    echo 'Event Type: '.$event->event_type."\n";

    // Test relationship
    $userFromEvent = $event->user;
    echo 'User from event: '.$userFromEvent->name."\n";

    echo "All tests passed!\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
    echo 'File: '.$e->getFile()."\n";
    echo 'Line: '.$e->getLine()."\n";
}
