<?php

declare(strict_types=1);

namespace App\Support\Tracing;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;

final class Trace
{
    private static ?TraceContext $context = null;

    public static function current(): TraceContext
    {
        $container = self::container();

        if ($container->bound(TraceContext::class)) {
            /** @var TraceContext $resolved */
            $resolved = $container->make(TraceContext::class);
            self::$context = $resolved;

            return $resolved;
        }

        if (self::$context === null) {
            self::store(TraceContext::generate());
        }

        return self::$context;
    }

    public static function store(TraceContext $context): void
    {
        self::$context = $context;

        $container = self::container();
        $container->instance(TraceContext::class, $context);
    }

    public static function childFromCurrent(): TraceContext
    {
        $current = self::current();
        $child = $current->child();
        self::store($child);

        return $child;
    }

    public static function forget(): void
    {
        self::$context = null;

        $container = self::container();

        if ($container->bound(TraceContext::class)) {
            $container->forgetInstance(TraceContext::class);
        }
    }

    private static function container(): Container
    {
        try {
            /** @var Container $container */
            $container = app();
        } catch (BindingResolutionException) {
            $container = \Illuminate\Container\Container::getInstance();
        }

        return $container;
    }
}
