<?php

namespace Initbiz\SeoStorm\Console;

use Site;
use Illuminate\Console\Command;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;

class RefreshSitemap extends Command
{
    protected $name = 'sitemap:refresh';
    protected $description = 'Refresh sitemap items in the DB';

    public function handle()
    {
        PagesGenerator::resetCache();
        $pagesGenerator = new PagesGenerator();
        $settings = Settings::instance();
        foreach ($settings->getSitesEnabledInSitemap() as $site) {
            Site::applyActiveSite($site);
            $pagesGenerator->makeDOMElements($site);
        }
    }
}
