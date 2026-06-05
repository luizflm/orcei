<?php

declare(strict_types = 1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasCookie('locale') && $request->cookie('locale') !== null && !is_array($request->cookie('locale'))) {
            App::setLocale($request->cookie('locale'));
        }

        return $next($request);
    }
}
