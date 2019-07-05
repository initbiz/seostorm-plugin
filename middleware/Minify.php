<?php namespace Arcane\Seo\Middleware;

use Closure;
use Arcane\Seo\Models\Settings;
use Arcane\Seo\Classes\Minifier;

class Minify {
    function handle ($request, Closure $next) {
        $response = $next($request);
        $content = $response->getContent();
        $cachePath = 'arcane/seo/minify/html'.$request->getRequestUri();
        $user = \BackendAuth::getUser();
        $settings = Settings::instance();

        // dd($user);
        if($user && $settings->no_minify_for_users ) return $response;

        if ( $settings->minify_html ) {

            if ( ! \Storage::exists($cachePath) ) {
                \Storage::put($cachePath, Minifier::minifyHtml($content) );
            }

            $content = \Storage::get($cachePath);
            $response->setContent($content);
        }
        
        // dd($request);
        return $response;
    }

}