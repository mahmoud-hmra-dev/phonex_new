<?php

namespace Webkul\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Theme
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        themes()->set('phonix');

        return $next($request);
    }
}
