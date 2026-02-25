{{-- Account Profile Component --}}

<div class="space-y-6">
    <header class="border-b border-gray-200 dark:border-white/10 pb-5 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.frontend') }} (Profile)</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage your profile details and password.</p>
    </header>

    <div class="space-y-8">
        <!-- Profile Info & Password Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 border border-gray-100 dark:border-white/5 shadow-sm">
                <livewire:components.profile.update-profile-information-form />
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 border border-gray-100 dark:border-white/5 shadow-sm">
                <livewire:components.profile.update-password-form />
            </div>
        </div>
        
        <!-- Danger Zone -->
        <div class="bg-red-50 dark:bg-red-900/10 rounded-xl p-6 border border-red-100 dark:border-red-900/30 mt-12">
            <livewire:components.profile.delete-user-form />
        </div>
    </div>
</div>
