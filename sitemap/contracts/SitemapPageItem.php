<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Contracts;

use DOMElement;
use Carbon\Carbon;
use Initbiz\SeoStorm\Sitemap\Contracts\Changefreq;
use Initbiz\SeoStorm\Sitemap\Generators\AbstractGenerator;

/**
 * Classes of this type can be parsed by Sitemap Pages generator
 */
interface SitemapPageItem
{
    /**
     * Get Loc attribute
     *
     * @return string
     */
    public function getLoc(): string;

    /**
     * Get lastmod attribute
     *
     * @return Carbon|null
     */
    public function getLastmod(): ?Carbon;

    /**
     * Get changefreq attribute
     *
     * @return Changefreq|null
     */
    public function getChangefreq(): ?Changefreq;

    /**
     * Get priority attribute
     *
     * @return float|null
     */
    public function getPriority(): ?float;

    /**
     * Set loc attribute
     *
     * @param string $loc
     * @return SitemapPageItem
     */
    public function setLoc(string $loc): SitemapPageItem;

    /**
     * Set Lastmod attribute
     *
     * @param string|Carbon $lastmod
     * @return SitemapPageItem
     */
    public function setLastmod(string|Carbon $lastmod): SitemapPageItem;

    /**
     * Set Changefreq attribute
     *
     * @param string|Changefreq $changefreq
     * @return SitemapPageItem
     */
    public function setChangefreq(string|Changefreq $changefreq): SitemapPageItem;

    /**
     * Set priority attribute
     *
     * @param string|float $priority
     * @return SitemapPageItem
     */
    public function setPriority(string|float $priority): SitemapPageItem;

    /**
     * Fill from array - it should accept strings as keys and values to parse the item
     *
     * @param array $data
     * @return SitemapPageItem
     */
    public function fillFromArray(array $data): SitemapPageItem;

    /**
     * Method that should convert this item to XML DOMElement
     *
     * @param DOMCreator $creator
     * @return DOMElement
     */
    public function toDomElement(DOMCreator $creator): DOMElement;

}
