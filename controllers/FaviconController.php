<?php

namespace Initbiz\SeoStorm\Controllers;

use Event;
use Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Initbiz\SeoStorm\Models\Settings;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FaviconController
{
    public function faviconIco(): BinaryFileResponse
    {
        $settings = Settings::instance();

        $favicon = $settings->getFaviconObject();

        return Response::file($favicon, ['Content-Type' => $favicon->getContentType()]);
    }

    public function webmanifest(Request $request): JsonResponse
    {
        $settings = Settings::instance();

        $favicon = $settings->getFaviconObject();
        $sizes = array_column($settings->favicon_sizes, 'size');

        // 32 and 180 are used as default sizes in HTML
        $sizes = array_merge(['32', '180'], $sizes);

        $result = [
            'icons' => [],
        ];

        foreach ($sizes as $size) {
            $result['icons'][] = [
                "src" => $favicon->getThumb($size, $size),
                "type" => $favicon->getContentType(),
                "sizes" => $size . "x" . $size,
            ];
        }

        Event::fire('initbiz.seostorm.webmanifestDataGenerate', [&$result]);

        return Response::json($result);
    }
}
