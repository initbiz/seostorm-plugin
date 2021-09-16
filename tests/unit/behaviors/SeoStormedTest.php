<?php

namespace Initbiz\SeoStorm\Tests\Behaviors;

use Initbiz\SeoStorm\Tests\Classes\FakeStormedModel;
use Initbiz\SeoStorm\Tests\Classes\StormedTestCase;

class SeoStormedTest extends StormedTestCase
{
    public function testAutomaticRelationBinding()
    {
        $model = new FakeStormedModel();
        $model->name = 'test';
        $model->save();
    }
}
