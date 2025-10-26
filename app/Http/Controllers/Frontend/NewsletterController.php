<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email'       => ['required', 'email', 'max:255'],
            'first_name'  => ['nullable', 'string', 'max:255'],
            'last_name'   => ['nullable', 'string', 'max:255'],
            'company'     => ['nullable', 'string', 'max:255'],
            'interests'   => ['nullable', 'array'],
            'interests.*' => ['string', 'max:255'],
            'source'      => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationFailure($request, $validator);
        }

        $validated = $validator->validated();
        $subscriber = $this->findSubscriberByEmail($validated['email']);

        $attributes = $this->prepareSubscriberAttributes($validated);

        if ($subscriber instanceof \App\Models\Subscriber) {
            if ($subscriber->status === 'unsubscribed') {
                $subscriber->update(array_merge($attributes, [
                    'status'          => 'active',
                    'unsubscribed_at' => null,
                ]));

                return $this->respondWithMessage($request, 'success', __('newsletter.resubscribed_successfully'));
            }

            if ($attributes !== []) {
                $subscriber->update($attributes);
            }

            return $this->respondWithMessage($request, 'info', __('newsletter.already_subscribed'));
        }

        $payload = array_merge([
            'email' => $validated['email'],
        ], $attributes);

        if (! array_key_exists('source', $payload)) {
            $payload['source'] = $validated['source'] ?? 'website';
        }

        Subscriber::subscribe($payload);

        return $this->respondWithMessage($request, 'success', __('newsletter.subscribed_successfully'));
    }

    public function unsubscribe(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationFailure($request, $validator);
        }

        $subscriber = $this->findSubscriberByEmail($validator->validated()['email']);

        if (! $subscriber instanceof \App\Models\Subscriber) {
            return $this->respondWithMessage($request, 'error', __('newsletter.subscription_error'), 404);
        }

        if ($subscriber->status !== 'unsubscribed') {
            $subscriber->unsubscribe();
        }

        return $this->respondWithMessage($request, 'success', __('subscribers.unsubscribed_successfully'));
    }

    private function handleValidationFailure(Request $request, ValidatorContract $validator): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        return redirect()->back()->withErrors($validator)->withInput();
    }

    private function respondWithMessage(Request $request, string $status, string $message, int $code = 200): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status'  => $status,
                'message' => $message,
            ], $code);
        }

        $flashKey = match ($status) {
            'error' => 'error',
            'info'  => 'info',
            default => 'success',
        };

        return redirect()->back()->with($flashKey, $message);
    }

    private function prepareSubscriberAttributes(array $validated): array
    {
        $attributes = [
            'first_name' => $validated['first_name'] ?? null,
            'last_name'  => $validated['last_name'] ?? null,
            'company'    => $validated['company'] ?? null,
            'interests'  => $validated['interests'] ?? null,
        ];

        if (array_key_exists('source', $validated)) {
            $attributes['source'] = $validated['source'];
        }

        return array_filter($attributes, static function ($value): bool {
            if (is_array($value)) {
                return true;
            }

            if (is_string($value)) {
                return trim($value) !== '';
            }

            return $value !== null;
        });
    }

    private function findSubscriberByEmail(string $email): ?Subscriber
    {
        return Subscriber::withoutGlobalScopes()->firstWhere('email', $email);
    }
}
