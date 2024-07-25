<?php

namespace Initbiz\SeoStorm\Contracts;

/**
 * Classes of this type can be parsed by Images Sitemap generator
 * They are representation of a single image within URL
 */
interface ConvertingToImageSitemapXml
{
    /**
     * Get Loc attribute
     *
     * @return string
     */
    public function getLoc(): string;
}
