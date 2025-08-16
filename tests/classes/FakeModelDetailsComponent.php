<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Tests\Classes;

use Cms\Classes\ComponentBase;

class FakeModelDetailsComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Fake model component',
            'description' => 'Component to test twig details'
        ];
    }

    public function onRun()
    {
        $this->page['model'] = FakeStormedModel::first();
    }
}
