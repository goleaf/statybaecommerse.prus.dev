<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

final class HandleImpersonation
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('impersonate') && Auth::check()) {
            $impersonateData = session('impersonate');
            $impersonatedUserId = $impersonateData['impersonated_user_id'] ?? null;
            
            if ($impersonatedUserId) {
                $impersonatedUser = User::find($impersonatedUserId);
                
                if ($impersonatedUser && Auth::id() !== $impersonatedUserId) {
                    // Store original user ID for returning if not already stored
                    if (!session()->has('original_user')) {
                        session(['original_user' => Auth::id()]);
                    }
                    
                    Auth::login($impersonatedUser);
                    
                    // Add impersonation banner data to view
                    view()->share('impersonating', [
                        'user' => $impersonatedUser,
                        'original_user' => User::find(session('original_user')),
                    ]);
                }
            }
        }

        return $next($request);
    }
}
