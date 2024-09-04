<?php

namespace Initbiz\SeoStorm\Controllers;

use Initbiz\SeoStorm\Classes\Robots;

class RobotsController
{
    public function index()
    {
        return Robots::generate();
    }
}
