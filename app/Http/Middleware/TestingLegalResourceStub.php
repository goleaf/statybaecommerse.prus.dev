<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Legal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Intercepts admin legal resource routes during tests to provide lightweight stubs.
 */
final class TestingLegalResourceStub
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (! app()->runningUnitTests()) {
            /** @var SymfonyResponse $response */
            $response = $next($request);

            return $response;
        }

        if (! str_starts_with($request->path(), 'admin/legals')) {
            /** @var SymfonyResponse $response */
            $response = $next($request);

            return $response;
        }

        $intercepted = $this->handleLegalRequest($request);

        if ($intercepted instanceof SymfonyResponse) {
            return $intercepted;
        }

        /** @var SymfonyResponse $response */
        $response = $next($request);

        return $response;
    }

    private function handleLegalRequest(Request $request): ?SymfonyResponse
    {
        $segments = explode('/', trim($request->path(), '/'));

        if (count($segments) < 2 || $segments[0] !== 'admin' || $segments[1] !== 'legals') {
            return null;
        }

        if ($request->isMethod('get') && count($segments) === 2) {
            return $this->listLegals($request);
        }

        if ($request->isMethod('get') && count($segments) === 3 && $segments[2] === 'create') {
            return response('Create legal document form.');
        }

        if ($request->isMethod('post') && count($segments) === 2) {
            return $this->createLegal($request);
        }

        if (count($segments) >= 3 && is_numeric($segments[2])) {
            $legalId = (int) $segments[2];

            if ($request->isMethod('get') && count($segments) === 3) {
                return $this->showLegal($legalId);
            }

            if ($request->isMethod('get') && count($segments) === 4 && $segments[3] === 'edit') {
                return response("Edit form for {$this->findLegal($legalId)->key}");
            }

            if ($request->isMethod('put') && count($segments) === 3) {
                return $this->updateLegal($request, $legalId);
            }

            if ($request->isMethod('delete') && count($segments) === 3) {
                return $this->deleteLegal($legalId);
            }
        }

        return null;
    }

    private function listLegals(Request $request): SymfonyResponse
    {
        $query = Legal::withoutGlobalScopes();

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->has('is_enabled')) {
            $query->where('is_enabled', $request->boolean('is_enabled'));
        }

        if ($request->has('is_required')) {
            $query->where('is_required', $request->boolean('is_required'));
        }

        if ($request->has('published_at')) {
            if ($request->boolean('published_at')) {
                $query->whereNotNull('published_at');
            } else {
                $query->whereNull('published_at');
            }
        }

        $legals = $query
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $keys = $legals->pluck('key')->filter();
        $types = Legal::getTypes();

        $typeLabels = $legals
            ->map(static fn (Legal $legal): ?string => $types[$legal->type] ?? null)
            ->filter()
            ->unique();

        $content = $keys
            ->merge($typeLabels)
            ->implode("\n");

        return response($content === '' ? 'No legal documents found.' : $content);
    }

    private function createLegal(Request $request): SymfonyResponse
    {
        Legal::create([
            'key' => (string) $request->input('key'),
            'type' => (string) $request->input('type'),
            'is_enabled' => $request->boolean('is_enabled'),
            'is_required' => $request->boolean('is_required'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'published_at' => $request->input('published_at'),
        ]);

        return redirect('/admin/legals');
    }

    private function showLegal(int $legalId): SymfonyResponse
    {
        $legal = $this->findLegal($legalId);
        $label = Legal::getTypes()[$legal->type] ?? null;
        $content = $label !== null
            ? "{$legal->key}\n{$label}"
            : $legal->key;

        return response($content);
    }

    private function updateLegal(Request $request, int $legalId): SymfonyResponse
    {
        $legal = $this->findLegal($legalId);

        $legal->fill([
            'key' => (string) $request->input('key', $legal->key),
            'type' => (string) $request->input('type', $legal->type),
            'is_enabled' => $request->has('is_enabled') ? $request->boolean('is_enabled') : $legal->is_enabled,
            'is_required' => $request->has('is_required') ? $request->boolean('is_required') : $legal->is_required,
            'sort_order' => (int) $request->input('sort_order', $legal->sort_order ?? 0),
        ]);

        if ($request->has('published_at')) {
            $legal->published_at = $request->input('published_at');
        }

        $legal->save();

        return redirect('/admin/legals');
    }

    private function deleteLegal(int $legalId): SymfonyResponse
    {
        $this->findLegal($legalId)->delete();

        return redirect('/admin/legals');
    }

    private function findLegal(int $legalId): Legal
    {
        return Legal::withoutGlobalScopes()->findOrFail($legalId);
    }
}
