<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Contracts;

use DOMElement;
use Initbiz\SeoStorm\Sitemap\Generators\DOMCreator;

/**
 * Classes of this type can be parsed by Sitemap Pages generator
 */
interface SitemapImageItem
{
    /**
     * Get Loc attribute
     *
     * @return string
     */
    public function getLoc(): string;

    /**
     * Get base file name attribute
     *
     * @return string
     */
    public function getBaseFileName(): string;

    /**
     * Set loc attribute
     *
     * @param string $loc
     * @return SitemapImageItem
     */
    public function setLoc(string $loc): SitemapImageItem;

    /**
     * Set baseFileName attribute
     *
     * @param string $baseFileName
     * @return SitemapImageItem
     */
    public function setBaseFileName(string $baseFileName): SitemapImageItem;

    /**
     * Fill from array - it should accept strings as keys and values to parse the item
     *
     * @param array $data
     * @return SitemapImageItem
     */
    public function fillFromArray(array $data): SitemapImageItem;

    /**
     * Method that should convert this item to XML DOMElement
     *
     * @param DOMCreator $creator
     * @return DOMElement
     */
    public function toDomElement(DOMCreator $creator): DOMElement;
}
