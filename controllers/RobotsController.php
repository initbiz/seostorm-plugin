<?php

namespace Initbiz\SeoStorm\Controllers;

class RobotsController
{
    public function index()
    {
        return Robots::generate();
    }
}
