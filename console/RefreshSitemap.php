<?php

namespace Initbiz\SeoStorm\Console;

use Illuminate\Console\Command;
use Initbiz\SeoStorm\SitemapGenerators\PagesGenerator;

class RefreshSitemap extends Command
{
    protected $name = 'sitemap:refresh';
    protected $description = 'Refresh sitemap items in the DB';

    public function handle()
    {
        $pagesGenerator = new PagesGenerator();
        $pagesGenerator->makeItems();
    }
}
