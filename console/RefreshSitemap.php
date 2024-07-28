<?php

namespace Initbiz\SeoStorm\Console;

use Illuminate\Console\Command;
use October\Rain\Support\Facades\Site;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;

class RefreshSitemap extends Command
{
    protected $name = 'sitemap:refresh';
    protected $description = 'Refresh sitemap items in the DB';

    public function handle()
    {
        $pagesGenerator = new PagesGenerator();
        foreach (Site::listEnabled() as $site) {
            $pagesGenerator->makeDOMElements($site);
        }
    }
}
