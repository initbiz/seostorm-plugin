<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Generators;

use Site;
use Cache;
use DOMElement;
use Carbon\Carbon;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Traits\EventEmitter;
use October\Rain\Database\Model;
use System\Classes\PluginManager;
use System\Models\SiteDefinition;
use October\Rain\Database\Collection;
use Initbiz\Seostorm\Models\SitemapItem;
use RainLab\Translate\Classes\Translator;
use RainLab\Pages\Classes\Page as StaticPage;
use Initbiz\SeoStorm\Sitemap\Resources\PageItem;
use October\Rain\Support\Collection as SupportCollection;
use Initbiz\SeoStorm\Sitemap\Generators\AbstractGenerator;
use Initbiz\SeoStorm\Sitemap\Resources\SitemapItemsCollection;

/**
 * This generator provides sitemaps for CMS pages as well as added by RainLab.Pages
 */
class PagesGenerator extends AbstractGenerator
{
    use EventEmitter;

    const HASH_PAGE_CACHE_KEY = 'initbiz.seostorm.pages_content_hashes';

    /**
     * Collection of pages to parse, if not set, will be taken from the current theme
     *
     * @var Collection
     */
    protected $pages;

    public function fillUrlSet(DOMElement $urlSet): DOMElement
    {
        $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlSet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $value = 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd';
        $urlSet->setAttribute('xsi:schemaLocation', $value);

        return $urlSet;
    }

    public function makeItems(?SiteDefinition $site = null): SitemapItemsCollection
    {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        $pages = $this->getEnabledCmsPages($this->getPages(), $site);

        $baseFilenamesToLeave = [];
        foreach ($pages as $page) {
            $baseFilenamesToLeave[] = $page->base_file_name;

            if (!$this->isPageContentChanged($page->base_file_name, $page['content'])) {
                continue;
            }

            $items = $this->makeItemsForCmsPage($page, $site);
            SitemapItem::refreshForCmsPage($page, $site, $items);

            $this->fireSystemEvent('initbiz.seostorm.cmsPageChanged', [$page]);
        }

        if (PluginManager::instance()->hasPlugin('RainLab.Pages')) {
            $staticPages = $this->getEnabledStaticPages();
            foreach ($staticPages as $staticPage) {
                $baseFilenamesToLeave[] = $staticPage->fileName;

                if (!$this->isPageContentChanged($staticPage->fileName, $staticPage->getContent())) {
                    continue;
                }

                $item = $this->makeItemForStaticPage($staticPage);
                SitemapItem::refreshForStaticPage($staticPage, $site, $item);
            }
        }

        $this->fireSystemEvent('initbiz.seostorm.beforeClearingSitemapItems', [&$baseFilenamesToLeave]);

        // Remove all unused SitemapItems
        $sitemapItemsToDelete = SitemapItem::whereNotIn('base_file_name', $baseFilenamesToLeave)->get();
        foreach ($sitemapItemsToDelete as $sitemapItemToDelete) {
            $sitemapItemToDelete->delete();
        }

        $sitemapItemsModels = SitemapItem::enabled()->withSite($site)->get();

        $this->fireSystemEvent('initbiz.seostorm.sitemapItemsModels', [&$sitemapItemsModels]);

        $sitemapItemsCollection = new SitemapItemsCollection();
        foreach ($sitemapItemsModels as $sitemapItemModel) {
            $sitemapItemsCollection->push($sitemapItemModel->toSitemapPageItem());
        }

        return $sitemapItemsCollection;
    }

    // CMS pages

    /**
     * Get CMS pages that have sitemap enabled
     *
     * @param array|Collection $pages
     * @param SiteDefinition|null $site
     * @return array<Page>
     */
    public function getEnabledCmsPages($pages = null, ?SiteDefinition $site = null): array
    {
        if (empty($pages)) {
            $pages = $this->getPages();
        }

        if (empty($site)) {
            $site = Site::getActiveSite();
        }

        $pages = $pages->filter(function ($page) {
            return $page->seoOptionsEnabledInSitemap;
        })->sortByDesc('seoOptionsPriority');

        $enabledPages = [];
        foreach ($pages as $page) {
            if ($this->isCmsPageEnabledInSitemap($page, $site)) {
                $enabledPages[] = $page;
            }
        }

        return $enabledPages;
    }

    /**
     * Generate the XML
     *
     * @return string|false
     */
    public function generate(?SupportCollection $pages = null): string|false
    {
        if (!is_null($pages)) {
            $this->pages = $pages;
        }

        return parent::generate();
    }

    /**
     * Get Pages attribute
     *
     * @return Collection
     */
    public function getPages()
    {
        if (isset($this->pages)) {
            return $this->pages;
        }

        $this->pages = Page::listInTheme(Theme::getEditTheme());

        return $this->pages;
    }

    /**
     * Checks if the page has sitemap enabled
     *
     * @param Page $page
     * @param SiteDefinition|null $site
     * @return boolean
     */
    public function isCmsPageEnabledInSitemap(Page $page, ?SiteDefinition $site = null): bool
    {
        if (empty($site)) {
            $site = Site::getActiveSite();
        }

        if (isset($page->attributes["viewBag"]["localeSeoOptionsEnabledInSitemap"][$site->code])) {
            return (bool) $page->attributes["viewBag"]["localeSeoOptionsEnabledInSitemap"][$site->code];
        }

        return (bool) ($page->seoOptionsEnabledInSitemap ?? false);
    }

    /**
     * Make SitemapItems for provided CMS page
     *
     * @param Page $page
     * @param SiteDefinition|null $site
     * @return SitemapItemsCollection<PageItem>
     */
    public function makeItemsForCmsPage(Page $page, ?SiteDefinition $site = null): SitemapItemsCollection
    {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        $loc = $page->url;
        if (PluginManager::instance()->hasPlugin('RainLab.Translate')) {
            $translator = Translator::instance();
            $loc = $translator->getPageInLocale($page->base_file_name, $site) ?? $loc;
        }

        $sitemapItems = [];
        $modelClass = $page->seoOptionsModelClass ?? "";
        if (class_exists($modelClass)) {
            $scope = $page->seoOptionsModelScope;
            $models = $this->getModelObjects($modelClass, $scope);

            foreach ($models as $model) {
                if (($model->seo_options['enabled_in_sitemap'] ?? null) === "0") {
                    continue;
                }

                $loc = $this->generateLocForModelAndCmsPage($model, $page);
                $loc = $this->trimOptionalParameters($loc);

                $lastmod = $page->lastmod ?: Carbon::createFromTimestamp($page->mtime);
                if ($page->seoOptionsUseUpdatedAt && isset($model->updated_at)) {
                    $lastmod = $model->updated_at;
                }

                $pageItem = new PageItem();
                $pageItem->fillFromArray([
                    'baseFileName' => $page->base_file_name,
                    'loc' => $loc,
                    'lastmod' => $lastmod,
                    'priority' => $page->seoOptionsPriority,
                    'changefreq' => $page->seoOptionsChangefreq,
                ]);

                $sitemapItems[] = $pageItem;
            }
        } else {
            $pageItem = new PageItem();
            $pageItem->fillFromArray([
                'baseFileName' => $page->getFileName(),
                'priority' => $page->seoOptionsPriority,
                'changefreq' => $page->seoOptionsChangefreq,
                'lastmod' => $page->lastmod ?: Carbon::createFromTimestamp($page->mtime),
                'loc' => $this->trimOptionalParameters($loc),
            ]);

            $sitemapItems[] = $pageItem;
        }

        return new SitemapItemsCollection($sitemapItems);
    }

    /**
     * Get Objects for provided model class, using scope definition
     *
     * @param string $modelClass
     * @param string|null $scopeDef
     * @return Collection
     */
    public function getModelObjects(string $modelClass, ?string $scopeDef = null): Collection
    {
        if (empty($scopeDef)) {
            return $modelClass::all();
        }

        $params = explode(':', $scopeDef);
        $scopeName = $params[0];
        $scopeParameter = $params[1] ?? null;
        $query = $modelClass::with(['seostorm_options'])->{$scopeName}($scopeParameter);

        return $query->get();
    }

    /**
     * Generate URL (loc) for provided model and CMS page
     *
     * @param Model $model
     * @param Page $page
     * @return string
     */
    public function generateLocForModelAndCmsPage(Model $model, Page $page): string
    {
        $baseFileName = $page->base_file_name;

        $modelParams = $page->seoOptionsModelParams;
        if (empty($modelParams)) {
            return \Cms::pageUrl($baseFileName);
        }

        $params = [];
        $modelParams = explode('|', $modelParams);
        foreach ($modelParams as $modelParam) {
            list($urlParam, $modelParam) = explode(':', $modelParam);

            $replacement = '';
            if (strpos($modelParam, '.') === false) {
                $replacement = $model->$modelParam;
            } else {
                // parameter with dot -> try to find by relation
                list($relationMethod, $relatedAttribute) = explode('.', $modelParam);
                if ($relatedObject = $model->$relationMethod()->first()) {
                    $replacement = $relatedObject->$relatedAttribute ?? 'default';
                }
                $replacement = empty($replacement) ? 'default' : $replacement;
            }
            $params[$urlParam] = $replacement;
        }

        if (PluginManager::instance()->hasPlugin('RainLab.Translate')) {
            $translator = Translator::instance();
            $loc = $translator->getPageInLocale($baseFileName, null, $params);
        } else {
            $loc = \Cms::pageUrl($baseFileName, $params);
        }

        return $loc;
    }

    // RainLab.Pages

    public function getEnabledStaticPages(?Theme $theme = null): array
    {
        if (empty($theme)) {
            $theme = Theme::getActiveTheme();
        }

        $staticPages = StaticPage::listInTheme($theme);

        $enabledPages = [];

        foreach ($staticPages as $staticPage) {
            $viewBag = $staticPage->getViewBag();
            if ($viewBag->property('enabled_in_sitemap')) {
                $enabledPages[] = $staticPage;
            }
        }

        return $enabledPages;
    }

    /**
     * Get PageItem object for this static page
     *
     * @param StaticPage $staticPage
     * @return PageItem
     */
    public function makeItemForStaticPage(StaticPage $staticPage): PageItem
    {
        $viewBag = $staticPage->getViewBag();

        $loc = StaticPage::url($staticPage->fileName);
        $pageItem = new PageItem();
        $pageItem->setLoc($loc);
        $pageItem->setLastmod($viewBag->property('lastmod') ?: $staticPage->mtime);
        $pageItem->setPriority($viewBag->property('priority'));
        $pageItem->setChangefreq($viewBag->property('changefreq'));

        return $pageItem;
    }

    // Helpers

    /**
     * Remove optional parameters from URL - this method is used for last check
     * if the sitemap has an optional parameter left in the URL
     *
     * @param string $loc
     * @return string
     */
    public function trimOptionalParameters(string $loc): string
    {
        // Remove empty optional parameters that don't have any models
        $pattern = '/\:.+\?/i';
        $loc = preg_replace($pattern, '', $loc);

        return $loc;
    }

    public function isPageContentChanged(string $baseFileName, string $content): bool
    {
        $cacheArray = [];
        if (Cache::has(self::HASH_PAGE_CACHE_KEY)) {
            $cacheArray = json_decode(Cache::get(self::HASH_PAGE_CACHE_KEY), true);
        }

        $md5 = md5($content);
        if (
            !isset($cacheArray[$baseFileName]) ||
            $cacheArray[$baseFileName] !== $md5
        ) {
            $cacheArray[$baseFileName] = $md5;
            Cache::put(self::HASH_PAGE_CACHE_KEY, json_encode($cacheArray));

            return true;
        }

        return false;
    }

    public static function resetCache(): void
    {
        Cache::forget(self::HASH_PAGE_CACHE_KEY);
    }
}