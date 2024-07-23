<?php

namespace Initbiz\SeoStorm\Tests\Classes;

use Config;
use Schema;
use PluginTestCase;
use Cms\Classes\Theme;
use System\Classes\MarkupManager;

abstract class StormedTestCase extends PluginTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $themesPath = plugins_path('initbiz/seostorm/tests/themes');
        Config::set('system.themes_path', $themesPath);
        app()->useThemesPath($themesPath);
        Theme::setActiveTheme('test');

        $markupManager = MarkupManager::instance();
        $markupManager->listFunctions();

        Schema::create('initbiz_fake_stormed_models', function ($table) {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->integer('category_id')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('initbiz_fake_stormed_categories', function ($table) {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function tearDown(): void
    {
        Theme::resetCache();

        parent::tearDown();
    }
}
