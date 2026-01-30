<?php
try {
    $u = App\Models\User::first();
    $r = App\Models\Referral::first();
    echo "User ID: " . $u->id . PHP_EOL;
    echo "Referral ID: " . $r->id . PHP_EOL;
    
    $reward = App\Models\ReferralReward::factory()->create([
        'user_id' => $u->id, 
        'referral_id' => $r->id
    ]);
    
    echo "Created Reward ID: " . $reward->id . PHP_EOL;
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString();
}
