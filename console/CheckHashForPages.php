<?php

namespace Initbiz\SeoStorm\Console;

use Illuminate\Console\Command;
use RainLab\Pages\Classes\Page;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\SeoStorm\Classes\Migrator;
use October\Rain\Support\Facades\Site;
use Initbiz\Seostorm\Models\SitemapItem;

class CheckHashForPages extends Command
{
    protected $name = 'seostorm:check:hash';
    protected $description = 'Migrate the configuration from Arcane.SEO to SEO Storm';

    public function handle()
    {
        $settings = Settings::instance();
        $locales = $settings->getSitesEnabledInSitemap();
        $pages = \Cms\Classes\Page::all();
        $this->withProgressBar($pages, function ($page) use ($locales) {
            if (SitemapItem::checkHash($page)) {
                foreach ($locales as $site) {
                    Site::applyActiveSite($site);
                    SitemapItem::makeSitemapItemsForCmsPage($page, $site);
                }
            }
        });
    }
}
