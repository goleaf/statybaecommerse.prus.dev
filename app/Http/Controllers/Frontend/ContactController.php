<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\SendContactMessageRequest;
use App\Jobs\SendContactMessageJob;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class ContactController extends Controller
{
    public function index(): View
    {
        return view('frontend.contact.index');
    }

    public function send(SendContactMessageRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $contactMessage = ContactMessage::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'subject'      => $validated['subject'],
            'phone'        => $validated['phone'] ?? null,
            'order_number' => $validated['order_number'] ?? null,
            'message'      => $validated['message'],
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
        ]);

        SendContactMessageJob::dispatch($contactMessage);

        return back()->with('success', __('frontend.contact.flash.success'));
    }
}
