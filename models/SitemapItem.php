<?php

namespace Initbiz\Seostorm\Models;

use Site;
use Cache;
use Model;
use Cms\Classes\Page;
use Cms\Classes\Controller;
use System\Models\SiteDefinition;
use Illuminate\Support\Facades\Queue;
use Initbiz\SeoStorm\Jobs\ParseSiteJob;
use RainLab\Pages\Classes\Page as StaticPage;
use Initbiz\SeoStorm\Classes\SitemapGenerator;

class SitemapItem extends Model
{
    use \October\Rain\Database\Traits\Validation;

    const HASH_PAGE_CACHE_KEY = 'initbiz.seostorm.hash_pages';

    /**
     * @var string table name
     */
    public $table = 'initbiz_seostorm_sitemap_items';

    /**
     * @var array rules for validation
     */
    public $rules = [
        'loc' => 'required',
        'images',
        'videos',
        'base_file_name'
    ];

    public $jsonable = [
        'images',
        'videos'
    ];

    public $belongsToMany = [
        'media' => [
            SitemapMedia::class,
            'table' => 'initbiz_seostorm_sitemap_items_media'
        ]
    ];

    public $belongsTo = [
        'siteDefinition' => SiteDefinition::class
    ];

    public static function makeSitemapItemsForCmsPage($page): void
    {
        $sitemapGenerator = new SitemapGenerator();
        $sitemapItems = $sitemapGenerator->makeItemsForCmsPage($page);
        $sitemapItemModels = self::where('base_file_name', $page['base_file_name'])
            ->where('site_definition_id', $site->id)->get(['loc', 'base_file_name']);
        foreach ($sitemapItems as $sitemapItem) {
            $sitemapItemModel = $sitemapItemModels->where('loc', $sitemapItem['loc'])->first();

            if ($sitemapItemModel) {
                $sitemapItemModel->queueParseSite();
                // $sitemapItemModel->is_enabled = $sitemapItemModel->isAvailable();
                $sitemapItemModel->save();
                continue;
            }

            $sitemapItemModel = new self();
            $sitemapItemModel->loc = $sitemapItem['loc'];
            $sitemapItemModel->base_file_name = $page['base_file_name'];
            $sitemapItemModel->site_definition_id = $site->id;
            // $sitemapItemModel->is_enabled = $sitemapItemModel->isAvailable();
            $sitemapItemModel->save();
            $sitemapItemModel->queueParseSite();
        }
    }

    public static function makeSitemapItemsForStaticPage($page, ?SiteDefinition $site = null): void
    {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        $sitemapItemModel = self::where('loc', StaticPage::url($page->fileName))->first();
        if ($sitemapItemModel) {
            $sitemapItemModel->queueParseSite();
            return;
        }

        $sitemapItemModel = new self();
        $sitemapItemModel->loc = StaticPage::url($page->fileName);
        $sitemapItemModel->base_file_name = $page->fileName;
        if ($site) {
            $sitemapItemModel->site_definition_id = $site->id;
        }
        $sitemapItemModel->save();
        $sitemapItemModel->queueParseSite();
    }

    public function queueParseSite(): void
    {
        Queue::push(ParseSiteJob::class, ['url' => $this->loc]);
    }

    public function isAvailable(): bool
    {
        $page = Page::find($this->base_file_name);
        if (!$page) {
            return false;
        }

        $siteCode = $this->siteDefinition->code;
        if (!isset($page->attributes["viewBag"]["localeSeoOptionsEnabledInSitemap"])) {
            if (!$page->seoOptionsEnabledInSitemap) {
                return false;
            }

            return true;
        }

        if (!$this->site_definition_id) {
            return true;
        }

        if (!isset($page->attributes["viewBag"]["localeSeoOptionsEnabledInSitemap"][$siteCode])) {
            return false;
        }

        if (!$page->attributes["viewBag"]["localeSeoOptionsEnabledInSitemap"][$siteCode] ?? true) {
            return false;
        }

        return true;
    }

    public static function checkHash($page)
    {
        $hash = md5($page['content']);
        if ($hash === Cache::get(self::HASH_PAGE_CACHE_KEY . $page['base_file_name'])) {
            return null;
        }

        return !!Cache::rememberForever(self::HASH_PAGE_CACHE_KEY . $page['base_file_name'], function () use ($hash) {
            return $hash;
        });
    }
}
