<?php

namespace Initbiz\SeoStorm\Tests\Unit\Models;

use Http;
use Config;
use Storage;
use PluginTestCase;
use System\Models\File;
use Illuminate\Http\UploadedFile;
use Initbiz\SeoStorm\Models\Settings;

class SettingsTest extends PluginTestCase
{
    public function testGetFaviconObject(): void
    {
        Storage::fake();

        $settings = Settings::instance();

        $this->assertNull($settings->getFaviconObject());

        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/img/seostorm-icon.png');
        $favicon = (new File())->fromFile($filePath);
        $favicon->save();

        $settings->favicon_from = 'fileupload';
        $settings->favicon_fileupload = $favicon;
        $settings->save();

        $this->assertTrue($settings->getFaviconObject() instanceof File);

        Http::fake([
            url('/storage/app/media/seostorm-icon-2.png') => Http::response([
                UploadedFile::fake()->image('seostorm-icon-2.png')->get()
            ])
        ]);

        $settings->favicon_from = 'media';
        $settings->favicon = 'seostorm-icon-2.png';
        $settings->save();

        $favicon = $settings->getFaviconObject();
        $this->assertTrue($favicon instanceof File);
        $this->assertEquals('seostorm-icon-2.png', $favicon->getFilename());

        Http::fake([
            url('/seostorm-icon-3.png') => Http::response([
                UploadedFile::fake()->image('seostorm-icon-3.png')->get()
            ])
        ]);

        $settings->favicon_from = 'url';
        $settings->favicon_url = url('/seostorm-icon-3.png');
        $settings->save();

        $favicon = $settings->getFaviconObject();
        $this->assertTrue($settings->getFaviconObject() instanceof File);
        $this->assertEquals('seostorm-icon-3.png', $favicon->getFilename());
    }
}
