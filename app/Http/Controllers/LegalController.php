<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class LegalController extends Controller
{
    public function index(): Response
    {
        return response()->view('legal.index');
    }

    public function search(Request $request): Response
    {
        return response()->view('legal.search', [
            'query' => $request->get('q', ''),
        ]);
    }

    public function type(string $type): Response
    {
        return response()->view('legal.type', [
            'type' => $type,
        ]);
    }

    public function show(string $key): Response
    {
        return response()->view('legal.show', [
            'key' => $key,
        ]);
    }

    public function download(string $key, string $format = 'pdf'): Response
    {
        return response()->view('legal.download', [
            'key' => $key,
            'format' => $format,
        ]);
    }

    public function sitemap(): Response
    {
        return response()->view('legal.sitemap');
    }

    public function rss(): Response
    {
        return response()->view('legal.rss');
    }
}
