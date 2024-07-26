<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Resources;

use DOMElement;
use Initbiz\SeoStorm\Sitemap\Generators\DOMCreator;
use Initbiz\SeoStorm\Sitemap\Contracts\SitemapImageItem;

/**
 * Page Sitemap item
 */
class PageItem implements SitemapImageItem
{
    /**
     * Loc
     *
     * @var string
     */
    protected string $loc;

    /**
     * Base file name of the page
     *
     * @var string
     */
    protected string $baseFileName;

    /**
     * Get Loc attribute
     *
     * @return string
     */
    public function getLoc(): string
    {
        return $this->loc;
    }

    /**
     * Get base file name attribute
     *
     * @return string
     */
    public function getBaseFileName(): string
    {
        return $this->baseFileName;
    }

    /**
     * Set loc attribute
     *
     * @param string $loc
     * @return SitemapImageItem
     */
    public function setLoc(string $loc): SitemapImageItem
    {
        if (!str_starts_with($loc, 'http')) {
            $loc = url($loc);
        }

        $this->loc = $loc;
        return $this;
    }

    /**
     * Set baseFileName attribute
     *
     * @param string $baseFileName
     * @return SitemapImageItem
     */
    public function setBaseFileName(string $baseFileName): SitemapImageItem
    {
        // trim extension if exists
        if (str_contains($baseFileName, '.')) {
            $baseFileName = substr($baseFileName, 0 , (strrpos($baseFileName, ".")));
        }

        $this->baseFileName = $baseFileName;
        return $this;
    }

    /**
     * Fill from array - it should accept strings as keys and values to parse the item
     *
     * @param array $data
     * @return SitemapImageItem
     */
    public function fillFromArray(array $data): SitemapImageItem
    {
        $attributes = [
            'loc',
            'lastmod',
            'changefreq',
            'priority',
            'baseFileName',
        ];

        foreach ($attributes as $attribute) {
            if (isset($data[$attribute])) {
                $methodName = 'set' . studly_case($attribute);
                $this->{$methodName}($data[$attribute]);
            }
        }

        return $this;
    }

    /**
     * Method that should convert this item to XML DOMElement
     *
     * @param DOMCreator $creator
     * @return DOMElement
     */
    public function toDomElement(DOMCreator $creator): DOMElement
    {
        $urlElement = $creator->createElement('url');

        $element = $creator->createElement('loc', $this->getLoc());
        $urlElement->appendChild($element);

        $lastmod = $this->getLastmod();
        if (!empty($lastmod)) {
            $element = $creator->createElement('lastmod', $lastmod->format('c'));
            $urlElement->appendChild($element);
        }

        $changefreq = $this->getChangefreq();
        if (!empty($changefreq)) {
            $element = $creator->createElement('changefreq', $changefreq->value);
            $urlElement->appendChild($element);
        }

        $priority = $this->getPriority();
        if (!empty($priority)) {
            $element = $creator->createElement('priority', number_format($priority, 2));
            $urlElement->appendChild($element);
        }

        return $urlElement;
    }
}
