<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Generators;

use Site;
use Cache;
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
use Initbiz\Sitemap\DOMElements\UrlsetDOMElement;
use Initbiz\Sitemap\Generators\AbstractGenerator;
use October\Rain\Support\Collection as SupportCollection;

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

    public function __construct(?SiteDefinition $activeSite = null)
    {
        parent::__construct();

        if (is_null($activeSite)) {
            $request = \Request::instance();
            $activeSite = Site::getSiteFromRequest($request->getSchemeAndHttpHost(), $request->getPathInfo());
        }

        Site::applyActiveSite($activeSite);
    }

    /**
     * Make DOMElements listed in the sitemap
     *
     * @param SiteDefinition|null $site
     * @return array
     */
    public function makeDOMElements(?SiteDefinition $site = null): array
    {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        $pages = $this->getEnabledCmsPages($this->getPages(), $site);

        $baseFilenamesToLeave = [];
        foreach ($pages as $page) {
            $baseFilenamesToLeave[] = $page->base_file_name;

            if (!$this->isPageContentChanged($page, $site)) {
                continue;
            }

            $urls = $this->makeItemsForCmsPage($page, $site);
            SitemapItem::refreshForCmsPage($page, $site, $urls);
        }

        if (PluginManager::instance()->hasPlugin('RainLab.Pages')) {
            $staticPages = $this->getEnabledStaticPages();
            foreach ($staticPages as $staticPage) {
                $baseFilenamesToLeave[] = $staticPage->fileName;

                if (!$this->isPageContentChanged($staticPage, $site)) {
                    continue;
                }

                $item = $this->makeItemForStaticPage($staticPage, $site);
                SitemapItem::refreshForStaticPage($staticPage, $site, $item);
            }
        }

        $this->fireSystemEvent('initbiz.seostorm.beforeClearingSitemapItems', [&$baseFilenamesToLeave]);

        // Remove all unused SitemapUrls
        $sitemapItemsToDelete = SitemapItem::whereNotIn('base_file_name', $baseFilenamesToLeave)->withSite($site)->get();
        foreach ($sitemapItemsToDelete as $sitemapItemToDelete) {
            $sitemapItemToDelete->delete();
        }

        $sitemapItemsModels = SitemapItem::enabled()->withSite($site)->get();

        $this->fireSystemEvent('initbiz.seostorm.sitemapItemsModels', [&$sitemapItemsModels]);

        $urls = [];
        foreach ($sitemapItemsModels as $sitemapItemModel) {
            $urls[] = $sitemapItemModel->toUrlDOMElement();
        }

        $urlSetDOMElement = new UrlsetDOMElement();
        $urlSetDOMElement->setUrls($urls);

        return [$urlSetDOMElement];
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
     * @return array<SitemapItem>
     */
    public function makeItemsForCmsPage(Page $page, ?SiteDefinition $site = null): array
    {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        $loc = $page->url;

        // We're restoring ending / if the page is a "root" page
        $restoreSlash = false;
        if ($loc === '/') {
            $restoreSlash = true;
        }

        if (PluginManager::instance()->hasPlugin('RainLab.Translate')) {
            $translator = Translator::instance();
            $loc = $translator->getPageInLocale($page->base_file_name, $site) ?? $loc;
        }

        if ($restoreSlash && !str_ends_with('/', $loc)) {
            $loc .= '/';
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

                $lastmod = $this->getLastmodForCmsPage($page);

                if ($page->seoOptionsUseUpdatedAt && isset($model->updated_at)) {
                    $lastmod = $model->updated_at;
                }

                $sitemapItem = SitemapItem::where('loc', $loc)->withSite($site)->first();
                if (!$sitemapItem) {
                    $sitemapItem = new SitemapItem();
                    $sitemapItem->loc = $loc;
                }

                $sitemapItem->lastmod = $lastmod;
                $sitemapItem->priority = $page->seoOptionsPriority;
                $sitemapItem->changefreq = $page->seoOptionsChangefreq;
                $sitemapItem->base_file_name = $page->base_file_name;
                $sitemapItem->site_definition_id = $site->id;

                $sitemapItems[] = $sitemapItem;
            }
        } else {
            $loc = $this->trimOptionalParameters($loc);

            $sitemapItem = SitemapItem::where('loc', $loc)->withSite($site)->first();
            if (!$sitemapItem) {
                $sitemapItem = new SitemapItem();
                $sitemapItem->loc = $loc;
            }

            $lastmod = $this->getLastmodForCmsPage($page);

            $sitemapItem->base_file_name = $page->base_file_name;
            $sitemapItem->site_definition_id = $site->id;
            $sitemapItem->priority = $page->seoOptionsPriority;
            $sitemapItem->changefreq = $page->seoOptionsChangefreq;
            $sitemapItem->lastmod = $lastmod;

            $sitemapItems[] = $sitemapItem;
        }

        return $sitemapItems;
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

    public function getLastmodForCmsPage(Page $page): Carbon
    {
        if (!is_null($page->lastmod)) {
            return Carbon::parse($page->lastmod);
        }

        if (!is_null($page->mtime)) {
            return Carbon::createFromTimestamp($page->mtime);
        }

        return Carbon::now();
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
     * Get SitemapItem object for this static page
     *
     * @param StaticPage $staticPage
     * @param SiteDefinition|null $site
     * @return SitemapItem
     */
    public function makeItemForStaticPage(StaticPage $staticPage, ?SiteDefinition $site = null): SitemapItem
    {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        $viewBag = $staticPage->getViewBag();

        $loc = StaticPage::url($staticPage->fileName);
        $sitemapItem = SitemapItem::where('loc', $loc)->withSite($site)->first();

        if (!$sitemapItem) {
            $sitemapItem = new SitemapItem();
            $sitemapItem->loc = $loc;
        }

        $sitemapItem->lastmod = $viewBag->property('lastmod') ?: $staticPage->mtime;
        $sitemapItem->priority = $viewBag->property('priority');
        $sitemapItem->changefreq = $viewBag->property('changefreq');
        $sitemapItem->base_file_name = $staticPage->fileName;
        $sitemapItem->site_definition_id = $site->id;

        return $sitemapItem;
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

    public function isPageContentChanged(Page|StaticPage $page, ?SiteDefinition $site = null): bool
    {
        if (is_null($site)) {
            $site = Site::getActiveSite();
        }

        $key = $site->code . '-';

        if ($page instanceof StaticPage) {
            $baseFileName = $page->fileName;
            $content = $page->getContent();
        } else {
            $baseFileName = $page->base_file_name;
            $content = $page['content'];
        }

        $key .= $baseFileName;

        $cacheArray = [];
        if (Cache::has(self::HASH_PAGE_CACHE_KEY)) {
            $cacheArray = json_decode(Cache::get(self::HASH_PAGE_CACHE_KEY), true);
        }

        $md5 = md5($content);
        if (
            !isset($cacheArray[$key]) ||
            $cacheArray[$key] !== $md5
        ) {
            $cacheArray[$key] = $md5;
            Cache::put(self::HASH_PAGE_CACHE_KEY, json_encode($cacheArray));

            return true;
        }

        return false;
    }

    /**
     * Generate array key to save in cache the information about update
     *
     * @param Page|StaticPage $page
     * @param SiteDefinition|null $site
     * @return string
     */
    public static function getCacheKeyForPage(Page|StaticPage $page,): string
    {
    }

    public static function resetCache(): void
    {
        Cache::forget(self::HASH_PAGE_CACHE_KEY);
    }
}
