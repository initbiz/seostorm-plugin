<?php

namespace Initbiz\SeoStorm\Classes;

class RobotsController
{
    public function index()
    {
        return Robots::generate();
    }
}
