<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Tests\Unit\Components;

use System\Models\File;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\SeoStorm\Components\Schema;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;

class SchemaTest extends StormedTestCase
{
    public function testGetSchemaImageFromSettings()
    {
        $settings = Settings::instance();
        $component = new Schema();
        $component->setSettings($settings);

        $settings->schema_image_from = 'media';
        $settings->schema_image = 'seostorm-icon.png';
        $settings->save();

        $this->assertStringEndsWith('/seostorm-icon.png', $component->getSchemaImageFromSettings());

        $settings->schema_image_from = 'url';
        $settings->schema_image_url = 'https://example.com/schema.png';
        $settings->save();

        $this->assertEquals('https://example.com/schema.png', $component->getSchemaImageFromSettings());

        $filePath = plugins_path('initbiz/seostorm/tests/fixtures/img/seostorm-icon.png');
        $schemaImage = (new File())->fromFile($filePath);
        $schemaImage->save();

        $settings->schema_image_from = 'fileupload';
        $settings->schema_image_fileupload = $schemaImage;
        $settings->save();

        $this->assertEquals($schemaImage->getPath(), $component->getSchemaImageFromSettings());
    }

    public function tearDown(): void
    {
        Settings::clearInternalCache();

        parent::tearDown();
    }
}
