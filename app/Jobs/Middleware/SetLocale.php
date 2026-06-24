<?php

declare(strict_types = 1);

namespace App\Jobs\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function __construct(private string $locale)
    {
    }

    public function handle(object $job, Closure $next): mixed
    {
        App::setLocale($this->locale);

        return $next($job);
    }
}
