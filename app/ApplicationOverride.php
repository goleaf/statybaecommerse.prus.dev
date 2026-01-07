<?php

declare(strict_types=1);

namespace App;

use Illuminate\Foundation\Application;

class ApplicationOverride extends Application
{
    /**
     * Get the path to the public / web directory.
     *
     * @param  string $path
     * @return string
     */
    public function publicPath($path = '')
    {
        return $this->joinPaths($this->publicPath ?: $this->basePath(env('CUSTOM_PUBLIC_PATH', 'public')), $path);
    }
}
