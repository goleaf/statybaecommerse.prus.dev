<?php

declare (strict_types=1);
namespace App\Http\Controllers;

use App\Models\Legal;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
/**
 * LegalController
 * 
 * HTTP controller handling LegalController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class LegalController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     * @return View
     */
    public function index(): View
    {
        $documents = Legal::enabled()->published()->ordered()->with(['translations' => function ($query) {
            $query->where('locale', app()->getLocale());
        }])->get()->skipWhile(function ($document) {
            // Skip legal documents that are not properly configured for display
            return empty($document->key) || !$document->is_enabled || !$document->is_published || empty($document->type) || $document->translations->isEmpty();
        });
        $groupedDocuments = $documents->groupBy('type');
        return view('legal.index', compact('documents', 'groupedDocuments'));
    }
    /**
     * Display the specified resource with related data.
     * @param string $key
     * @return View
     */
    public function show(string $key): View
    {
        $document = Legal::byKey($key)->enabled()->published()->with(['translations' => function ($query) {
            $query->where('locale', app()->getLocale());
        }])->firstOrFail();
        $translation = $document->translations()->where('locale', app()->getLocale())->first();
        if (!$translation) {
            $translation = $document->translations()->first();
        }
        // Get related documents of the same type
        $relatedDocuments = Legal::byType($document->type)->enabled()->published()->where('id', '!=', $document->id)->ordered()->with(['translations' => function ($query) {
            $query->where('locale', app()->getLocale());
        }])->limit(3)->get()->skipWhile(function ($relatedDocument) {
            // Skip related legal documents that are not properly configured for display
            return empty($relatedDocument->key) || !$relatedDocument->is_enabled || !$relatedDocument->is_published || empty($relatedDocument->type) || $relatedDocument->translations->isEmpty();
        });
        // Get other document types
        $otherDocuments = Legal::enabled()->published()->where('type', '!=', $document->type)->ordered()->with(['translations' => function ($query) {
            $query->where('locale', app()->getLocale());
        }])->limit(5)->get()->skipWhile(function ($otherDocument) {
            // Skip other legal documents that are not properly configured for display
            return empty($otherDocument->key) || !$otherDocument->is_enabled || !$otherDocument->is_published || empty($otherDocument->type) || $otherDocument->translations->isEmpty();
        });
        return view('legal.show', compact('document', 'translation', 'relatedDocuments', 'otherDocuments'));
    }
    /**
     * Handle type functionality with proper error handling.
     * @param string $type
     * @return View
     */
    public function type(string $type): View
    {
        $documents = Legal::byType($type)->enabled()->published()->ordered()->with(['translations' => function ($query) {
            $query->where('locale', app()->getLocale());
        }])->get()->skipWhile(function ($document) {
            // Skip legal documents that are not properly configured for display
            return empty($document->key) || !$document->is_enabled || !$document->is_published || empty($document->type) || $document->translations->isEmpty();
        });
        $typeName = Legal::getTypes()[$type] ?? $type;
        return view('legal.type', compact('documents', 'type', 'typeName'));
    }
    /**
     * Handle sitemap functionality with proper error handling.
     * @return Illuminate\Http\Response
     */
    public function sitemap(): \Illuminate\Http\Response
    {
        $documents = Legal::enabled()->published()->with('translations')->get();
        $content = view('legal.sitemap', compact('documents'))->render();
        return response($content, 200, ['Content-Type' => 'application/xml']);
    }
    /**
     * Handle rss functionality with proper error handling.
     * @return Illuminate\Http\Response
     */
    public function rss(): \Illuminate\Http\Response
    {
        $documents = Legal::enabled()->published()->ordered()->with(['translations' => function ($query) {
            $query->where('locale', app()->getLocale());
        }])->limit(20)->get();
        $content = view('legal.rss', compact('documents'))->render();
        return response($content, 200, ['Content-Type' => 'application/rss+xml']);
    }
    /**
     * Handle search functionality with proper error handling.
     * @param Request $request
     * @return View
     */
    public function search(Request $request): View
    {
        $query = $request->get('q', '');
        $type = $request->get('type', '');
        $documents = Legal::enabled()->published()->when($type, function ($q) use ($type) {
            return $q->byType($type);
        })->when($query, function ($q) use ($query) {
            return $q->whereHas('translations', function ($subQuery) use ($query) {
                $subQuery->where('title', 'like', "%{$query}%")->orWhere('content', 'like', "%{$query}%");
            });
        })->with(['translations' => function ($query) {
            $query->where('locale', app()->getLocale());
        }])->ordered()->paginate(10);
        $types = Legal::getTypes();
        return view('legal.search', compact('documents', 'query', 'type', 'types'));
    }
    /**
     * Handle download functionality with proper error handling.
     * @param string $key
     * @param string $format
     * @return RedirectResponse
     */
    public function download(string $key, string $format = 'pdf'): RedirectResponse
    {
        $document = Legal::byKey($key)->enabled()->published()->with(['translations' => function ($query) {
            $query->where('locale', app()->getLocale());
        }])->firstOrFail();
        $translation = $document->translations()->where('locale', app()->getLocale())->first();
        if (!$translation) {
            $translation = $document->translations()->first();
        }
        // For now, redirect to the document page
        // In the future, you can implement actual PDF generation
        return redirect()->route('legal.show', $document->key);
    }
}