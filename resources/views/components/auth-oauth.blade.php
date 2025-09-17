<div class="space-y-4">
    <!-- Google OAuth Button -->
    <a href="#" class="group relative w-full inline-flex justify-center items-center py-3.5 px-6 border border-gray-200/60 bg-white/90 backdrop-blur-sm text-sm font-semibold text-gray-700 hover:text-gray-900 hover:bg-white hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500/20 rounded-xl transition-all duration-300 shadow-sm hover:shadow-lg transform hover:scale-[1.02] active:scale-[0.98]">
        <!-- Google Icon -->
        <span class="absolute left-0 inset-y-0 flex items-center pl-4">
            <svg class="h-5 w-5 group-hover:scale-110 transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M19.99 10.187c0-.82-.069-1.417-.216-2.037H10.2v3.698h5.62c-.113.919-.725 2.303-2.084 3.233l-.02.124 3.028 2.292.21.02c1.926-1.738 3.037-4.296 3.037-7.33z" fill="#4285F4"/>
                <path d="M10.2 19.931c2.753 0 5.064-.886 6.753-2.414l-3.218-2.436c-.862.587-2.017.997-3.536.997a6.126 6.126 0 01-5.801-4.141l-.12.01-3.148 2.38-.041.112c1.677 3.256 5.122 5.492 9.11 5.492z" fill="#34A853"/>
                <path d="M4.397 11.937a6.009 6.009 0 01-.34-1.971c0-.687.125-1.351.33-1.971l-.007-.132-3.187-2.42-.104.05A9.79 9.79 0 000 9.965a9.79 9.79 0 001.088 4.473l3.308-2.502z" fill="#FBBC05"/>
                <path d="M10.2 3.853c1.914 0 3.206.809 3.943 1.484l2.878-2.746C15.253.985 12.953 0 10.199 0 6.211 0 2.766 2.237 1.09 5.492l3.297 2.503A6.152 6.152 0 0110.2 3.853z" fill="#EB4335"/>
            </svg>
        </span>
        
        <!-- Button Text -->
        <span class="font-semibold text-gray-700 group-hover:text-gray-900 transition-colors duration-200">
            {{ __('Sign in with Google') }}
        </span>
        
        <!-- Hover Effect Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700 rounded-xl"></div>
    </a>
    
    <!-- Additional OAuth Providers (if needed in future) -->
    <!-- 
    <a href="#" class="group relative w-full inline-flex justify-center items-center py-3.5 px-6 border border-gray-200/60 bg-white/90 backdrop-blur-sm text-sm font-semibold text-gray-700 hover:text-gray-900 hover:bg-white hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500/20 rounded-xl transition-all duration-300 shadow-sm hover:shadow-lg transform hover:scale-[1.02] active:scale-[0.98]">
        <span class="absolute left-0 inset-y-0 flex items-center pl-4">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
        </span>
        <span class="font-semibold text-gray-700 group-hover:text-gray-900 transition-colors duration-200">
            {{ __('Continue with Facebook') }}
        </span>
    </a>
    -->
</div>
