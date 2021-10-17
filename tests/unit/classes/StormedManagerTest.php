<?php

namespace Initbiz\SeoStorm\Tests\Unit\Classes;

use Initbiz\SeoStorm\Classes\StormedManager;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;

class StormedManagerTest extends StormedTestCase
{
    public function testGetStormedModels()
    {
        $stormedManager = StormedManager::instance();
        $editorFields = $stormedManager->getSeoFieldsDefsForEditor();
        foreach ($editorFields as $field) {
            $this->assertArrayHasKey('type', $field);
            $this->assertArrayHasKey('property', $field);
            $this->assertArrayHasKey('title', $field);
        }
    }
}
