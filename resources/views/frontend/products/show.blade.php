@extends('frontend.layouts.app')

@section('title', $product->getTranslatedSeoTitle() ?: ($product->trans('name') ?? $product->name))
@section('description', $product->getTranslatedSeoDescription())

@section('content')
    @livewire('pages.single-product', ['product' => $product])
@endsection
