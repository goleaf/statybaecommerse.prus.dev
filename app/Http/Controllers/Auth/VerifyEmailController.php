<?php

declare (strict_types=1);
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
/**
 * VerifyEmailController
 * 
 * HTTP controller handling VerifyEmailController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
class VerifyEmailController extends Controller
{
    /**
     * Handle __invoke functionality with proper error handling.
     * @param EmailVerificationRequest $request
     * @return RedirectResponse
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('account.index', absolute: false) . '?verified=1');
        }
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }
        return redirect()->intended(route('account.index', absolute: false) . '?verified=1');
    }
}