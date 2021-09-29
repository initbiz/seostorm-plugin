<?php

namespace Initbiz\SeoStorm\Tests\Classes;

use Schema;
use PluginTestCase;
use October\Rain\Database\Model;
use System\Classes\MarkupManager;
use System\Classes\PluginManager;
use System\Classes\UpdateManager;
use System\Classes\VersionManager;

abstract class StormedTestCase extends PluginTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Version manager remembers in the databaseVersions all versions
        // between tests even if the table in db is empty
        VersionManager::forgetInstance();

        // Get the plugin manager
        $pluginManager = PluginManager::instance();

        Model::clearExtendedClasses();
        Model::clearBootedModels();

        // Register the plugins to make features like file configuration available
        $pluginManager->registerAll(true);

        // Boot all the plugins to test with dependencies of this plugin
        $pluginManager->bootAll(true);

        $markupManager = MarkupManager::instance();
        $markupManager->listFunctions();

        Schema::create('initbiz_fake_stormed_model', function ($table) {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Get the plugin manager
        $pluginManager = PluginManager::instance();

        // Ensure that plugins are registered again for the next test
        $pluginManager->unregisterAll();
    }

    protected function runPluginRefreshCommand($code, $throwException = true)
    {
        // Plugin refresh does not migrate all of the tables
        // That's why we're running update here so that all migrations
        // will be run by plugin:refresh command
        UpdateManager::instance()->updatePlugin($code);
        parent::runPluginRefreshCommand($code, $throwException);
    }
}
