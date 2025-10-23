<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\SendContactMessageJob;
use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ContactController extends Controller
{
    public function index(): View
    {
        $supportEmail = SystemSetting::getPublic('mail.support_email', config('mail.from.address'));
        $company = Company::query()->active()->first();

        return view('frontend.contact.index', [
            'supportEmail' => $supportEmail,
            'company' => $company,
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'order_number' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $contactMessage = ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'phone' => $validated['phone'] ?? null,
            'order_number' => $validated['order_number'] ?? null,
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        SendContactMessageJob::dispatch($contactMessage);

        return back()->with('success', __('frontend/contact.flash.success'));
    }
}
