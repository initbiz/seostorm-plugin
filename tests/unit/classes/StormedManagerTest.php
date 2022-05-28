<?php

namespace Initbiz\SeoStorm\Tests\Unit\Classes;

use Initbiz\SeoStorm\Classes\StormedManager;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;

class StormedManagerTest extends StormedTestCase
{
    public function testGetFieldsDefsForEditor()
    {
        $stormedManager = StormedManager::instance();
        $editorFields = $stormedManager->getSeoFieldsDefsForEditor();
        foreach ($editorFields as $field) {
            $this->assertArrayHasKey('type', $field);
            $this->assertArrayHasKey('property', $field);
            $this->assertArrayHasKey('title', $field);
        }
    }

    public function testGetSeoFieldsDefs()
    {
        $seoFieldsAttributes = [
            'meta_title',
            'meta_description',
            'canonical_url',
            'robot_index',
            'robot_follow',
            'robot_advanced',
            'og_title',
            'og_description',
            'og_type',
            'og_card',
            'og_image',
            'og_ref_image',
            'enabled_in_sitemap',
            'use_updated_at',
            'lastmod',
            'changefreq',
            'priority',
            'model_class',
            'model_scope',
            'model_params',
        ];

        $stormedManager = StormedManager::instance();
        $seoFields = $stormedManager->getSeoFieldsDefs();
        foreach ($seoFieldsAttributes as $attribute) {
            $this->assertArrayHasKey($attribute, $seoFields);
        }

        $seoFields = $stormedManager->getSeoFieldsDefs(['changeFreq']);
        $this->assertArrayNotHasKey('changeFreq', $seoFields);
        foreach ($seoFieldsAttributes as $attribute) {
            if ($attribute !== 'changeFreq') {
                $this->assertArrayHasKey($attribute, $seoFields);
            }
        }
    }
}
