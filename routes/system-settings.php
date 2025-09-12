<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// System Settings Admin Routes (bypassing Filament compatibility issues)
Route::prefix('admin/system-settings')->middleware(['web'])->group(function () {
    Route::get('/', function () {
        $categories = DB::table('system_setting_categories')
            ->orderBy('sort_order')
            ->get();
        
        $settings = DB::table('system_settings')
            ->join('system_setting_categories', 'system_settings.category_id', '=', 'system_setting_categories.id')
            ->select('system_settings.*', 'system_setting_categories.name as category_name', 'system_setting_categories.color as category_color')
            ->orderBy('system_setting_categories.sort_order')
            ->orderBy('system_settings.sort_order')
            ->get()
            ->groupBy('category_name');
        
        return view('admin.system-settings.index', compact('categories', 'settings'));
    })->name('admin.system-settings.index');
    
    Route::post('/update', function (Request $request) {
        $settings = $request->input('settings', []);
        
        foreach ($settings as $key => $value) {
            DB::table('system_settings')
                ->where('key', $key)
                ->update([
                    'value' => $value,
                    'updated_at' => now()
                ]);
        }
        
        return redirect()->back()->with('success', 'Settings updated successfully!');
    })->name('admin.system-settings.update');
    
    Route::get('/export', function () {
        $settings = DB::table('system_settings')
            ->join('system_setting_categories', 'system_settings.category_id', '=', 'system_setting_categories.id')
            ->select('system_settings.*', 'system_setting_categories.name as category_name')
            ->get();
        
        $data = $settings->map(function ($setting) {
            return [
                'key' => $setting->key,
                'name' => $setting->name,
                'value' => $setting->value,
                'type' => $setting->type,
                'group' => $setting->group,
                'category' => $setting->category_name,
                'description' => $setting->description,
                'help_text' => $setting->help_text,
                'is_public' => $setting->is_public,
                'is_required' => $setting->is_required,
                'is_encrypted' => $setting->is_encrypted,
                'is_readonly' => $setting->is_readonly,
                'options' => $setting->options,
                'default_value' => $setting->default_value,
            ];
        });
        
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    })->name('admin.system-settings.export');
    
    Route::get('/stats', function () {
        $stats = [
            'total_settings' => DB::table('system_settings')->count(),
            'active_settings' => DB::table('system_settings')->where('is_active', true)->count(),
            'public_settings' => DB::table('system_settings')->where('is_public', true)->count(),
            'encrypted_settings' => DB::table('system_settings')->where('is_encrypted', true)->count(),
            'categories' => DB::table('system_setting_categories')->where('is_active', true)->count(),
            'by_type' => DB::table('system_settings')
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'by_group' => DB::table('system_settings')
                ->selectRaw('group, count(*) as count')
                ->groupBy('group')
                ->pluck('count', 'group'),
        ];
        
        return response()->json($stats);
    })->name('admin.system-settings.stats');
});

// Public API for frontend to access public settings
Route::get('/api/settings/public', function () {
    $settings = DB::table('system_settings')
        ->where('is_public', true)
        ->where('is_active', true)
        ->pluck('value', 'key');
    
    return response()->json($settings);
})->name('api.settings.public');

// Individual setting access
Route::get('/api/settings/{key}', function ($key) {
    $setting = DB::table('system_settings')
        ->where('key', $key)
        ->where('is_active', true)
        ->first();
    
    if (!$setting) {
        return response()->json(['error' => 'Setting not found'], 404);
    }
    
    return response()->json([
        'key' => $setting->key,
        'value' => $setting->value,
        'type' => $setting->type,
        'is_public' => $setting->is_public,
    ]);
})->name('api.settings.get');
