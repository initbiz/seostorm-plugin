<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Contracts;

use DOMElement;
use Initbiz\SeoStorm\Sitemap\Generators\DOMCreator;

/**
 * Classes of this type can be parsed by Sitemap Pages generator
 */
interface SitemapVideoItem
{
    /**
     * Get Loc attribute
     *
     * @return string
     */
    public function getLoc(): string;

    /**
     * Get list of video definitions
     *
     * @return array<SitemapSingleVideoItem>
     */
    public function getVideos(): array;

    /**
     * Set loc attribute
     *
     * @param string $loc
     * @return SitemapVideoItem
     */
    public function setLoc(string $loc): SitemapVideoItem;

    /**
     * Set videos attribute
     *
     * @param array<SitemapSingleVideoItem> $videos
     * @return SitemapVideoItem
     */
    public function setVideos(array $videos): SitemapVideoItem;

    /**
     * Fill from array - it should accept strings as keys and values to parse the item
     *
     * @param array $data
     * @return SitemapVideoItem
     */
    public function fillFromArray(array $data): SitemapVideoItem;

    /**
     * Method that should convert this item to XML DOMElement
     *
     * @param DOMCreator $creator
     * @return DOMElement
     */
    public function toDomElement(DOMCreator $creator): DOMElement;
}
