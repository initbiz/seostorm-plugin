<?php

namespace Initbiz\SeoStorm\SitemapGenerators;

use DOMAttr;
use DOMElement;
use DOMDocument;
use October\Rain\Support\Facades\Site;
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

        $items->sortByDesc(function($item) {
            return $item->priority ?? 0;
        });

        foreach ($items as $sitemapItem) {
            if ($this->getUrlsCount() >= self::MAX_URLS) {
                break;
            }

            $emptyUrlElement = $this->xml->createElement('url');
            $urlElement = $sitemapItem->toDomElement($emptyUrlElement, $this);

            if ($urlElement) {
                $this->urlSet->appendChild($urlElement);
                $this->urlCount++;
            }
        }

        return $this->xml->saveXML();
    }

    /**
     * Method that creates DOMElement using our DOMDocument instance
     *
     * @param string $name
     * @param string $value
     * @return DOMElement|false
     */
    public function createElement(string $name, string $value = ""): DOMElement|false
    {
        return $this->xml->createElement($name, $value);
    }

    /**
     * Method to create attribute that can be then appended to any DOMElement
     *
     * @param string $localName
     * @return DOMAttr|false
     */
    public function createAttribute(string $localName): DOMAttr|false
    {
        return $this->xml->createAttribute($localName);
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
