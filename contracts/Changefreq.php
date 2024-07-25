<?php

namespace Initbiz\SeoStorm\Contracts;

enum Changefreq: string
{
    case always = 'always';
    case hourly = 'hourly';
    case daily = 'daily';
    case weekly = 'weekly';
    case monthly = 'monthly';
    case yearly = 'yearly';
    case never = 'never';
}
