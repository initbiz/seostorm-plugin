<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Generators;

use DOMElement;
use DOMDocument;
use System\Models\SiteDefinition;
use October\Rain\Support\Facades\Site;
use Initbiz\SeoStorm\Sitemap\Generators\DOMCreator;
use Initbiz\SeoStorm\Sitemap\Resources\SitemapItemsCollection;

abstract class AbstractGenerator
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
     * Count elements
     *
     * @var integer
     */
    protected $urlCount = 0;

    /**
     * DOMCreator instance that we use externally to create elements on our document
     *
     * @var DOMCreator
     */
    protected $creator;

    /**
     * Whole XML instance
     *
     * @var DOMDocument
     */
    private $xml;

    /**
     * URL set instance
     *
     * @var DOMElement
     */
    private $urlSet;

    public function __construct(?SiteDefinition $activeSite = null)
    {
        if (is_null($activeSite)) {
            $request = \Request::instance();
            $activeSite = Site::getSiteFromRequest($request->getSchemeAndHttpHost(), $request->getPathInfo());
        }

        Site::applyActiveSite($activeSite);

        $xml = new DOMDocument();
        $xml->encoding = 'UTF-8';

        $urlSet = $xml->createElement('urlset');
        $urlSet = $this->fillUrlSet($urlSet);
        $xml->appendChild($urlSet);

        // We need both, because we add all items to urlSet while generating uses whole xml instance
        $this->urlSet = $urlSet;
        $this->xml = $xml;
        $this->setCreator(new DOMCreator($this->xml));
    }

    /**
     * Get urls count value
     *
     * @return integer
     */
    public function getUrlsCount(): int
    {
        return $this->urlCount;
    }

    /**
     * Get dOMCreator instance that we use externally to create elements on our document
     */
    public function getCreator(): DOMCreator
    {
        return $this->creator;
    }

    /**
     * Set dOMCreator instance that we use externally to create elements on our document
     */
    public function setCreator(DOMCreator $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Generate the XML
     *
     * @return string|false
     */
    public function generate(): string|false
    {
        $items = $this->makeItems();

        if (false) {
            # code...
        }
        $items->sortByDesc(function ($item) {
            return $item->getPriority() ?? 0;
        });

        foreach ($items as $sitemapItem) {
            if ($this->getUrlsCount() >= self::MAX_URLS) {
                break;
            }

            $creator = $this->getCreator();
            $urlElement = $sitemapItem->toDomElement($creator);

            if ($urlElement) {
                $this->urlSet->appendChild($urlElement);
                $this->urlCount++;
            }
        }

        return $this->xml->saveXML();
    }


    /**
     * Fill initial URL Set with proper attributes
     *
     * @param DOMElement $urlSet
     * @return DOMElement
     */
    abstract public function fillUrlSet(DOMElement $urlSet): DOMElement;

    /**
     * Make items that are added to the XML
     *
     * @param DOMElement $urlSet
     * @return SitemapItemsCollection
     */
    abstract public function makeItems(): SitemapItemsCollection;
}
