<?php namespace Arcane\Seo\Classes;

use Request;
use Arcane\Seo\Models\Settings;
use Cms\Classes\Controller;
use Cms\Classes\Theme;
use System\Classes\PluginManager;
use Carbon\Carbon;
use Cms\Classes\Page;

class  Sitemap
{
    /**
     * Maximum URLs allowed (Protocol limit is 50k)
     */
    const MAX_URLS = 50000;

    /**
     * Maximum generated URLs per type
     */
    const MAX_GENERATED = 10000;
    
    /**
     * @var integer A tally of URLs added to the sitemap
     */
    protected $urlCount = 0;
    
    private $xml;
    private $urlSet;
    
    function generate() {

        // get all pages of the current theme
        $pages = Page::listInTheme(Theme::getEditTheme());
        $models = [];

        foreach( $pages as $page) {
            if (! $page->enabled_in_sitemap ) continue;

            $modelClass = $page->model_class;

            // if page has model class
            if ( class_exists($modelClass)) { 
                $models = $modelClass::all();

                foreach ($models as $model) {
                    if ($page->hasComponent('blogPost')) 
                    {
                        if (! (integer)$model->arcane_seo_options['enabled_in_sitemap']) continue;
                        $this->addItemToSet(Item::asPost($page, $model));
                    }
                    else $this->addItemToSet(Item::asCmsPage($page, $model));
                }
            } else {

                $this->addItemToSet(Item::asCmsPage($page));
            }
        }

        // if RainLab.Pages is installed
        if (Helper::rainlabPagesExists())
        {
            $staticPages = \RainLab\Pages\Classes\Page::listInTheme(Theme::getActiveTheme());
            foreach ($staticPages as $staticPage) {
                if (! $staticPage->getViewBag()->property('enabled_in_sitemap')) continue;
                $this->addItemToSet(Item::asStaticPage($staticPage));
            }
        }
        
        return $this->make();

    }
    
    protected function makeRoot()
    {
        if ($this->xml !== null) {
            return $this->xml;
        }

        $xml = new \DOMDocument;
        $xml->encoding = 'UTF-8';

        return $this->xml = $xml;
    }

    protected function makeUrlSet()
    {
        if ($this->urlSet !== null) {
            return $this->urlSet;
        }

        $xml = $this->makeRoot();
        $urlSet = $xml->createElement('urlset');
        $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlSet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $urlSet->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
        $xml->appendChild($urlSet);
        return $this->urlSet = $urlSet;
    }

    protected function addItemToSet(Item $item, $url = null, $mtime = null)
    {
        $xml = $this->makeRoot();
        $urlSet = $this->makeUrlSet();

        $urlElement = $this->makeUrlElement(
            $xml,
            url( $item->loc ), // make sure output is a valid url
            Helper::w3cDatetime( $item->lastmod ), // make sure output is  a valid datetime
            $item->changefreq,
            $item->priority
        );

        if ($urlElement) {
            $urlSet->appendChild($urlElement);
        }

        return $urlSet;
    }

    protected function makeUrlElement($xml, $pageUrl, $lastModified, $frequency, $priority)
    {
        if ($this->urlCount >= self::MAX_URLS) {
            return false;
        }

        $this->urlCount++;

        $url = $xml->createElement('url');
        $pageUrl && $url->appendChild($xml->createElement('loc', $pageUrl));
        $lastModified && $url->appendChild($xml->createElement('lastmod', $lastModified));
        $frequency && $url->appendChild($xml->createElement('changefreq', $frequency));
        $priority && $url->appendChild($xml->createElement('priority', $priority));

        return $url;
    }

    protected function make() 
    {
        $this->makeUrlSet();
        return $this->xml->saveXML();
    }

}


class Item 
{
    public $loc, $lastmod, $priority, $changefreq;
    
    function __construct($url=null, $lastmod=null, $priority=null, $changefreq=null) {
        $this->loc = $url;
        $this->lastmod = $lastmod;
        $this->priority = $priority;
        $this->changefreq = $changefreq;
    }

    public static function asStaticPage($staticPage) {
        return new self(
             url($staticPage->url),
             $staticPage->getViewBag()->property('lastmod') ?: $staticPage->updated_at,
             $staticPage->getViewBag()->property('priority'),
             $staticPage->getViewBag()->property('changefreq')
        );
    }
    
    public static function asCmsPage($page, $model = null) {
        if ($model)
            return new self(
                url( Helper::replaceUrlPlaceholders($page->url, $model) ),
                $model->updated_at,
                $page->priority,
                $page->changefreq
            );
        return new Self(
            $page->url, 
            $page->lastmod ?: \Carbon\Carbon::createFromTimestamp($page->mtime),
            $page->priority,
            $page->changefreq
        );
    }

    public static function asPost($page, $post) {
        $item = new self;
        $use_updated = $page->use_updated_at;
        $item->loc = url( Helper::replaceUrlPlaceholders($page->url, $post) )  ;
        $item->lastmod = $use_updated ? $post->updated_at->format('c') : $page->lastmod ;
        $item->priority = $page->priority ;
        $item->changefreq = $page->changefreq;

        return $item;
    }
}


