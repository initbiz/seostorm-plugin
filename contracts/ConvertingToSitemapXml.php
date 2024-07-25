<?php

namespace Initbiz\SeoStorm\Contracts;

use DOMElement;
use Carbon\Carbon;
use Initbiz\SeoStorm\Contracts\Changefreq;
use Initbiz\SeoStorm\Classes\AbstractGenerator;

/**
 * Classes of this type can be parsed by Sitemap Pages generator
 */
interface ConvertingToSitemapXml
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
     * @return ConvertingToSitemapXml
     */
    public function setLoc(string $loc): ConvertingToSitemapXml;

    /**
     * Set Lastmod attribute
     *
     * @param string|Carbon $lastmod
     * @return ConvertingToSitemapXml
     */
    public function setLastmod(string|Carbon $lastmod): ConvertingToSitemapXml;

    /**
     * Set Changefreq attribute
     *
     * @param string|Changefreq $changefreq
     * @return ConvertingToSitemapXml
     */
    public function setChangefreq(string|Changefreq $changefreq): ConvertingToSitemapXml;

    /**
     * Set priority attribute
     *
     * @param string|float $priority
     * @return ConvertingToSitemapXml
     */
    public function setPriority(string|float $priority): ConvertingToSitemapXml;

    /**
     * Fill from array - it should accept strings as keys and values to parse the item
     *
     * @param array $data
     * @return ConvertingToSitemapXml
     */
    public function fillFromArray(array $data): ConvertingToSitemapXml;

    /**
     * Method that should convert this item to XML DOMElement
     * In the first parameter you get already created URL element to work on
     *
     * @param DOMElement $urlElement
     * @param AbstractGenerator $generator
     * @return DOMElement
     */
    public function toDomElement(DOMElement $urlElement, AbstractGenerator $generator): DOMElement;

}
