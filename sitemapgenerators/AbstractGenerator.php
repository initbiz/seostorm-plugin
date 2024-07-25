<?php

namespace Initbiz\SeoStorm\Classes;

use DOMElement;
use DOMDocument;
use October\Rain\Support\Facades\Site;
use RainLab\Translate\Classes\Translator;
use Initbiz\SeoStorm\Classes\SitemapItemsCollection;

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

    public function __construct(?Site $activeSite = null)
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
     * Generate the XML
     *
     * @return string|false
     */
    public function generate(): string|false
    {
        $items = $this->makeItems();

        // TODO: sort by priority if the parameter exists in the items

        foreach ($items as $sitemapItem) {
            if ($this->getUrlsCount() >= self::MAX_URLS) {
                break;
            }

            $urlElement = $sitemapItem->makeUrlElement($this->xml);

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
