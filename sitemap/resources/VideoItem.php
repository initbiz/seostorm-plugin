<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Resources;

use DOMElement;
use Initbiz\SeoStorm\Sitemap\Generators\DOMCreator;
use Initbiz\SeoStorm\Sitemap\Contracts\SitemapVideoItem;
use Initbiz\SeoStorm\Sitemap\Contracts\SitemapSingleVideoItem;

/**
 * Page Sitemap item
 */
class PageItem implements SitemapVideoItem
{
    /**
     * Loc
     *
     * @var string
     */
    protected string $loc;

    /**
     * Videos
     *
     * @var array<SitemapSingleVideoItem>
     */
    protected string $videos;

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
     * Get list of video definitions
     *
     * @return array<SitemapSingleVideoItem>
     */
    public function getVideos(): array
    {
        return $this->videos;
    }

    /**
     * Set loc attribute
     *
     * @param string $loc
     * @return SitemapVideoItem
     */
    public function setLoc(string $loc): SitemapVideoItem
    {
        $this->loc = $loc;
        return $this;
    }

    /**
     * Set videos attribute
     *
     * @param array<SitemapSingleVideoItem> $videos
     * @return SitemapVideoItem
     */
    public function setVideos(array $videos): SitemapVideoItem
    {
        $this->videos = $videos;
        return $this;
    }

    /**
     * Fill from array - it should accept strings as keys and values to parse the item
     *
     * @param array $data
     * @return SitemapVideoItem
     */
    public function fillFromArray(array $data): SitemapVideoItem
    {
        $attributes = [
            'loc',
            'videos',
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
