<?php

namespace Initbiz\SeoStorm\Tests\Behaviors;

use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;
use Initbiz\SeoStorm\Tests\Classes\FakeStormedModel;

class SeoStormedTest extends StormedTestCase
{
    public function testAutomaticRelationBinding()
    {
        /*
         * The definition will be applied through Plugin.php if the APP_ENV is testing
         * It's very early in the process so I couldn't find an easier way to do it
         */

        $model = new FakeStormedModel();
        $model->name = 'test';
        $model->save();

        $model->seo_options = [
            'meta_title' => 'Test title',
        ];

        $model->save();

        // Get the model from DB to ensure that we're not using the same instance
        $model2 = FakeStormedModel::first();

        $this->assertEquals('Test title', $model2->seo_options['meta_title']);
    }
}
