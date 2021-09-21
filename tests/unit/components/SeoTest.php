<?php

namespace Initbiz\SeoStorm\Tests\Unit\Components;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\Controller;
use Cms\Classes\ComponentManager;
use Cms\Components\ViewBag;
use Initbiz\SeoStorm\Components\Seo;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedModel;
use Initbiz\SeoStorm\Tests\Classes\FakeModelDetailsComponent;

class SeoTest extends StormedTestCase
{

    public function setUp():void
    {
        parent::setUp();
        $componentManager = ComponentManager::instance();
        $componentManager->registerComponent(Seo::class, 'seo');
        $componentManager->registerComponent(ViewBag::class, 'viewBag');
        $componentManager->registerComponent(FakeModelDetailsComponent::class, 'fakeModelDetails');
    }

    public function testGetTitle()
    {
        $theme = Theme::load('test');
        $page = Page::load($theme, 'empty.htm');
        $controller = new Controller($theme);
        $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $this->assertEquals('Test page title', $component->getTitle());

        // Assert that meta_title has higher priority
        $page->settings['meta_title'] = 'Meta title';
        $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $this->assertEquals('Meta title', $component->getTitle());

        // Check if title can get parsed from Twig variable

        $model = new FakeStormedModel();
        $model->name = 'test';
        $model->save();

        $model->seo_options = [
            'meta_title' => 'Test title seo_options',
        ];
        $model->save();

        // Assert that seo_options has even higher priority
        $page = Page::load($theme, 'with-fake-model.htm');
        $page->settings['meta_title'] = '{{ model.name }}';
        $result = $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $settings = Settings::instance();
        $settings->enable_site_meta = true;

        $component->setSettings($settings);
        $result = $controller->runPage($page);

        $this->assertStringContainsString('<title>test</title>', $result);

        // See if properties set in viewBag works and have highest priority
        $viewBag = $controller->findComponentByName('viewBag');
        $viewBag->setProperty('meta_title', '{{ model.seo_options.meta_title }}');
        $result = $controller->runPage($page);

        $this->assertStringContainsString('<title>Test title seo_options</title>', $result);
    }

    public function testViewBagHasHigherPriority()
    {
        $theme = Theme::load('test');
        $page = Page::load($theme, 'with-fake-model-viewbag.htm');
        $controller = new Controller($theme);
        $result = $controller->runPage($page);

        $component = $controller->findComponentByName('seo');

        $settings = Settings::instance();
        $settings->enable_site_meta = true;

        $component->setSettings($settings);
        $result = $controller->runPage($page);

        $model = new FakeStormedModel();
        $model->name = 'test';
        $model->save();

        $model->seo_options = [
            'meta_title' => 'Test title seo_options',
        ];
        $model->save();

        $viewBag = $controller->findComponentByName('viewBag');
        $result = $controller->runPage($page);

        $this->assertStringContainsString('<title>Test title seo_options</title>', $result);
    }

    public function testRobots()
    {
        $theme = Theme::load('test');
        $controller = new Controller($theme);
        $page = Page::load($theme, 'with-fake-model.htm');
        $result = $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $settings = Settings::instance();
        $settings->enable_robots_meta = true;

        $component->setSettings($settings);
        $result = $controller->runPage($page);

        dd($result);

        $this->assertStringContainsString('<title>test</title>', $result);
    }
}
