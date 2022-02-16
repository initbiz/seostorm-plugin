<?php

namespace Initbiz\SeoStorm\Tests\Unit\Components;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\Controller;
use Cms\Classes\ComponentManager;
use Initbiz\SeoStorm\Components\Seo;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedModel;
use Initbiz\SeoStorm\Tests\Classes\FakeModelDetailsComponent;

class SeoTest extends StormedTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $componentManager = ComponentManager::instance();
        $componentManager->registerComponent(Seo::class, 'seo');
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
        $page->settings['seoOptionsTitle'] = 'Meta title';
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
        $page->settings['seoOptionsTitle'] = '{{ model.name }}';
        $result = $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $settings = Settings::instance();
        $settings->enable_site_meta = true;

        $component->setSettings($settings);
        $result = $controller->runPage($page);

        $this->assertStringContainsString('<title>test</title>', $result);

        $page->settings['seoOptionsTitle'] = '{{ model.name }} - {{ model.name }}';
        $result = $controller->runPage($page);

        $this->assertStringContainsString('<title>test - test</title>', $result);
    }

    public function testRobots()
    {
        $theme = Theme::load('test');
        $controller = new Controller($theme);
        $page = Page::load($theme, 'with-fake-model.htm');
        $page->settings['seoOptionsRobotIndex'] = 'index';
        $result = $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $this->assertEquals('index', $component->getRobots());

        $page->settings['seoOptionsRobotIndex'] = '';
        $result = $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $this->assertEquals('', $component->getRobots());

        $page->settings['seoOptionsRobotIndex'] = 'noindex';
        $page->settings['seoOptionsRobotFollow'] = 'follow';
        $result = $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $this->assertEquals('noindex,follow', $component->getRobots());

        $model = new FakeStormedModel();
        $model->name = 'test';
        $model->save();

        $page->settings['seoOptionsRobotIndex'] = 'noindex';
        $page->settings['seoOptionsRobotFollow'] = 'follow';
        $page->settings['seoOptionsRobotAdvanced'] = '{{ model.name }}';
        $result = $controller->runPage($page);

        $settings = Settings::instance();
        $settings->enable_robots_meta = true;

        $component->setSettings($settings);
        $result = $controller->runPage($page);

        $this->assertStringContainsString('noindex,follow,test', $result);
    }


    public function testCanonical()
    {
        $theme = Theme::load('test');
        $controller = new Controller($theme);
        $page = Page::load($theme, 'with-fake-model.htm');

        // Test if canonical is <app_url>/model/default if nothing is set

        $page->settings['seoOptionsCanonicalUrl'] = '';
        $result = $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $this->assertEquals(env('APP_URL') . '/model/default', $component->getCanonicalUrl());

        // Test if canonical is <app_url>/model/test when properly set

        $model = new FakeStormedModel();
        $model->name = 'test';
        $model->save();

        $page->settings['seoOptionsCanonicalUrl'] = '/model/{{ model.name }}';

        $settings = Settings::instance();
        $settings->enable_site_meta = true;

        $component->setSettings($settings);
        $result = $controller->runPage($page);

        $this->assertStringContainsString(env('APP_URL') . '/model/test', $result);
    }

    // Open graph

    public function testGetOgTitle()
    {
        $theme = Theme::load('test');
        $controller = new Controller($theme);
        $page = Page::load($theme, 'with-fake-model.htm');
        $result = $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $model = new FakeStormedModel();
        $model->name = 'test';
        $model->description = 'test description';
        $model->save();

        $model->seo_options = [
            'meta_title' => 'Test title seo_options',
        ];
        $model->save();

        // Assert that seo_options has even higher priority
        $page = Page::load($theme, 'with-fake-model.htm');
        $page->settings['seoOptionsTitle'] = '{{ model.name }}';
        $result = $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $settings = Settings::instance();
        $settings->enable_site_meta = true;

        $component->setSettings($settings);
        $result = $controller->runPage($page);

        $this->assertStringContainsString('<title>test</title>', $result);
    }

    public function testGetTitleTranslate()
    {
        $theme = Theme::load('test');
        $page = Page::load($theme, 'empty.htm');
        $controller = new Controller($theme);
        $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $controller->runPage($page);
        $this->assertEquals('Test page title', $component->getTitle());

        // Assert that meta_title has higher priority
        $page->settings['seoOptionsMetaTitle'] = 'Meta title';
        $viewBag = $controller->findComponentByName('viewBag');
        $viewBag->setExternalPropertyName('localeSeoOptionsMetaTitle["pl"]', 'Meta title 2');

        $controller->runPage($page);
        $component = $controller->findComponentByName('seo');

        $this->assertEquals('Meta title', $component->getTitle());
        $page->rewriteTranslatablePageAttributes('pl');
        $controller->runPage($page);
        $this->assertEquals('Meta title 2', $component->getTitle());
    }
}
