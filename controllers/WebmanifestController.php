<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Controllers;

use Event;
use Response;
use Illuminate\Http\Request;
use Initbiz\SeoStorm\Models\Settings;

class WebmanifestController
{
    public function index(Request $request)
    {
        $settings = Settings::instance();

        $webmanifestArray = [
            'name' => $settings->webmanifest_name,
            'short_name' => $settings->webmanifest_short_name,
            'theme_color' => $settings->webmanifest_theme_color,
            'background_color' => $settings->webmanifest_background_color,
            'display' => $settings->webmanifest_display,
        ];

        foreach ($settings->webmanifest_custom_attributes as $webmanifestAttribute) {
            $webmanifestArray[$webmanifestAttribute['key']] = $webmanifestAttribute['value'];
        }

        if ($settings->favicon_enabled) {
            $webmanifestArray['icons'] = $this->getIconsItem();
        }

        Event::fire('initbiz.seostorm.webmanifestDataGenerate', [&$webmanifestArray]);

        $response = Response::make(json_encode($webmanifestArray, JSON_UNESCAPED_SLASHES));
        $response->header('Content-Type', 'text/plain');
        return $response;
    }

    /**
     * Generate Webmanifest icons item basing on favicon sizes
     *
     * @return array
     */
    protected function getIconsItem(): array
    {
        $settings = Settings::instance();

        $favicon = $settings->getFaviconObject();
        $sizes = array_column($settings->favicon_sizes, 'size');

        // 32 and 180 are used as default sizes in HTML
        $sizes = array_merge(['32', '180'], $sizes);

        $stripLength = strlen(url('/'));
        $iconsItem = [];
        foreach ($sizes as $size) {
            $iconsItem[] = [
                "src" => substr($favicon->getThumb($size, $size), $stripLength),
                "type" => $favicon->getContentType(),
                "sizes" => $size . "x" . $size,
            ];
        }

        return $iconsItem;
    }
}
