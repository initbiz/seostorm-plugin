<?php

namespace Arcane\Seo\Middleware;

use Closure;
use Storage;
use Arcane\Seo\Classes\Minifier;

class MinifyHtml
{
    function handle($request, Closure $next)
    {
        $cachePath = 'arcane/seo/minify/html' . $request->getRequestUri() . '/html';

        if (Minifier::isMinifyEnabled('html')) {
            if (!Storage::exists($cachePath)) {
                $response = $next($request);
                $content = $response->getContent();

                Storage::put($cachePath, Minifier::minifyHtml($content));
            } else {
                $content = Storage::get($cachePath);
                $response = response($content);
            }
        } else {
            $response = $next($request);
        }

        return $response;
    }
}
