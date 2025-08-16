<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Console;

use Illuminate\Console\Command;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\Seostorm\Models\SitemapItem;
use Initbiz\Seostorm\Models\SitemapMedia;
use Symfony\Component\Console\Input\InputOption;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;

class SitemapRefresh extends Command
{
    protected $name = 'sitemap:refresh';
    protected $description = 'Refresh sitemap items in the DB';

    protected $force = null;

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force updates.'],
        ];
    }

    public function handle()
    {
        $force = $this->option('force') ?? false;

        if ($force) {
            $this->deleteAllItems();
        }

        PagesGenerator::resetCache();
        $settings = Settings::instance();

        foreach ($settings->getSitesEnabledInSitemap() as $site) {
            $pagesGenerator = new PagesGenerator($site);
            $pagesGenerator->makeDOMElements();
        }
    }

    public function deleteAllItems(): void
    {
        $mediaItems = SitemapMedia::all();
        foreach ($mediaItems as $mediaItem) {
            $mediaItem->delete();
        }

        $items = SitemapItem::all();
        foreach ($items as $item) {
            $item->delete();
        }
    }
}
