@extends('components.layouts.base')

@section('title', $product->seo_title ?? $product->name)
@section('description', $product->seo_description ?? \Illuminate\Support\Str::limit(strip_tags($product->short_description ?? ''), 160))

@section('content')
    <livewire:product-page :product="$product" />
@endsection
