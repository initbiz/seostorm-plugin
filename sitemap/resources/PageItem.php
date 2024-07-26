<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Resources;

use DOMElement;
use Carbon\Carbon;
use Initbiz\SeoStorm\Sitemap\Resources\Changefreq;
use Initbiz\SeoStorm\Sitemap\Generators\DOMCreator;
use Initbiz\SeoStorm\Sitemap\Contracts\SitemapPageItem;

/**
 * Page Sitemap item
 */
class PageItem implements SitemapPageItem
{
    /**
     * Loc
     *
     * @var string
     */
    protected string $loc;

    /**
     * Lastmod
     *
     * @var Carbon
     */
    protected Carbon $lastmod;

    /**
     * Changefreq
     *
     * @var Changefreq
     */
    protected Changefreq $changefreq;

    /**
     * Priority
     *
     * @var float
     */
    protected float $priority;

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
     * Get lastmod attribute
     *
     * @return Carbon|null
     */
    public function getLastmod(): ?Carbon
    {
        return $this->lastmod ?? null;
    }

    /**
     * Get changefreq attribute
     *
     * @return Changefreq|null
     */
    public function getChangefreq(): ?Changefreq
    {
        return $this->changefreq ?? null;
    }

    /**
     * Get priority attribute
     *
     * @return float|null
     */
    public function getPriority(): ?float
    {
        return $this->priority ?? null;
    }

    /**
     * Set loc attribute
     *
     * @param string $loc
     * @return SitemapPageItem
     */
    public function setLoc(string $loc): SitemapPageItem
    {
        if (!str_starts_with($loc, 'http')) {
            $loc = url($loc);
        }

        $this->loc = $loc;
        return $this;
    }

    /**
     * Set Lastmod attribute
     *
     * @param string|Carbon $lastmod
     * @return SitemapPageItem
     */
    public function setLastmod(string|Carbon $lastmod): SitemapPageItem
    {
        if (is_string($lastmod)) {
            $lastmod = Carbon::parse($lastmod);
        }

        $this->lastmod = $lastmod;
        return $this;
    }

    /**
     * Set Changefreq attribute
     *
     * @param string|Changefreq $changefreq
     * @return SitemapPageItem
     */
    public function setChangefreq(string|Changefreq $changefreq): SitemapPageItem
    {
        if (is_string($changefreq)) {
            $changefreq = Changefreq::tryFrom($changefreq);
        }

        $this->changefreq = $changefreq;

        return $this;
    }

    /**
     * Set priority attribute
     *
     * @param string|float $priority
     * @return SitemapPageItem
     */
    public function setPriority(string|float $priority): SitemapPageItem
    {
        $this->priority = (float) $priority;
        return $this;
    }

    /**
     * Set baseFileName attribute
     *
     * @param string $baseFileName
     * @return SitemapPageItem
     */
    public function setBaseFileName(string $baseFileName): SitemapPageItem
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
     * @return SitemapPageItem
     */
    public function fillFromArray(array $data): SitemapPageItem
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
            $element = $creator->createElement('priority', $priority);
            $urlElement->appendChild($element);
        }

        return $urlElement;
    }
}