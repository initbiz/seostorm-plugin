<?php

namespace Initbiz\SeoStorm\Console;

use Illuminate\Console\Command;
use RainLab\Pages\Classes\Page;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\SeoStorm\Classes\Migrator;
use October\Rain\Support\Facades\Site;
use Initbiz\Seostorm\Models\SitemapItem;

class IndexAllSiteMapItems extends Command
{
    protected $name = 'seostorm:index:sitemapitems';
    protected $description = 'Migrate the configuration from Arcane.SEO to SEO Storm';

    public function handle()
    {
        $settings = Settings::instance();
        $locales = $settings->getLocalesForSitemap();
        $pages = \Cms\Classes\Page::all();
        $this->withProgressBar($pages, function ($page) use ($locales) {
            foreach ($locales as $site) {
                Site::applyActiveSite($site);
                SitemapItem::makeSitemapItemsForCmsPage($page, $site);
            }
        });

        $pages = Page::all();
        $this->withProgressBar($pages, function ($page) use ($locales) {
            foreach ($locales as $site) {
                Site::applyActiveSite($site);
                SitemapItem::makeSitemapItemsForCmsPage($page, $site);
            }
        });
    }
}
