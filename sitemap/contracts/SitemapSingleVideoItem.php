<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Contracts;

use DOMElement;
use Carbon\Carbon;
use Initbiz\SeoStorm\Sitemap\Generators\DOMCreator;

/**
 * Classes of this type can be parsed by Sitemap Pages generator
 */
interface SitemapSingleVideoItem
{
    /**
     * Get thumbnail loc attribute
     *
     * @return string
     */
    public function getThumbnailLoc(): string;

    /**
     * Get title attribute
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Get description attribute
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get content loc attribute
     *
     * @return string
     */
    public function getContentLoc(): string;

    /**
     * Get player loc attribute
     *
     * @return string
     */
    public function getPlayerLoc(): string;

    /**
     * Get duration attribute
     *
     * @return int|null
     */
    public function getDuration(): ?int;

    /**
     * Get expiration date attribute
     *
     * @return Carbon|null
     */
    public function getExpirationDate(): ?Carbon;

    /**
     * Get rating attribute
     *
     * @return float|null
     */
    public function getRating(): ?float;

    /**
     * Get view count attribute
     *
     * @return int|null
     */
    public function getViewCount(): ?int;

    /**
     * Get publication date attribute
     *
     * @return Carbon|null
     */
    public function getPublicationDate(): ?Carbon;

    /**
     * Get family friendly attribute
     *
     * @return string|null
     */
    public function getFamilyFriendly(): ?string;

    /**
     * Get restriction attribute
     *
     * @return array|null
     */
    public function getRestriction(): ?array;

    /**
     * Get price attribute
     *
     * @return array|null
     */
    public function getPrice(): ?array;

    /**
     * Get requires subscription attribute
     *
     * @return string|null
     */
    public function getRequiresSubscription(): ?string;

    /**
     * Get uploader attribute
     *
     * @return string|null
     */
    public function getUploader(): ?string;

    /**
     * Get live attribute
     *
     * @return string|null
     */
    public function getLive(): ?string;

    /**
     * Set thumbnail loc attribute
     *
     * @param string
     * @return SitemapSingleVideoItem
     */
    public function setThumbnailLoc(string $thumbnailLoc): SitemapSingleVideoItem;

    /**
     * Set title attribute
     *
     * @param string
     * @return SitemapSingleVideoItem
     */
    public function setTitle(string $title): SitemapSingleVideoItem;

    /**
     * Set description attribute
     *
     * @param string
     * @return SitemapSingleVideoItem
     */
    public function setDescription(string $description): SitemapSingleVideoItem;

    /**
     * Set content loc attribute
     *
     * @param string
     * @return SitemapSingleVideoItem
     */
    public function setContentLoc(string $contentLoc): SitemapSingleVideoItem;

    /**
     * Set player loc attribute
     *
     * @param string
     * @return SitemapSingleVideoItem
     */
    public function setPlayerLoc(string $playerLoc): SitemapSingleVideoItem;

    /**
     * Set duration attribute
     *
     * @param string
     * @return SitemapSingleVideoItem
     */
    public function setDuration(int|string $duration): SitemapSingleVideoItem;

    /**
     * Set expiration date attribute
     *
     * @param string|Carbon
     * @return SitemapSingleVideoItem
     */
    public function setExpirationDate(string|Carbon $expirationDate): SitemapSingleVideoItem;

    /**
     * Set rating attribute
     *
     * @param float|string
     * @return SitemapSingleVideoItem
     */
    public function setRating(float|string $rating): SitemapSingleVideoItem;

    /**
     * Set view count attribute
     *
     * @param int|string
     * @return SitemapSingleVideoItem
     */
    public function setViewCount(int|string $viewCount): SitemapSingleVideoItem;

    /**
     * Set publication date attribute
     *
     * @param string|Carbon
     * @return SitemapSingleVideoItem
     */
    public function setPublicationDate(string|Carbon $publicationDate): SitemapSingleVideoItem;

    /**
     * Set family friendly attribute
     *
     * @param string
     * @return SitemapSingleVideoItem
     */
    public function setFamilyFriendly(string $familyFriendly): SitemapSingleVideoItem;

    /**
     * Set restriction attribute
     *
     * @param string
     * @return SitemapSingleVideoItem
     */
    public function setRestriction(array $restriction): SitemapSingleVideoItem;

    /**
     * Set price attribute
     *
     * @param array
     * @return SitemapSingleVideoItem
     */
    public function setPrice(array $price): SitemapSingleVideoItem;

    /**
     * Set requires subscription attribute
     *
     * @param string
     * @return SitemapSingleVideoItem
     */
    public function setRequiresSubscription(string $requiresSubscription): SitemapSingleVideoItem;

    /**
     * Set uploder attribute
     *
     * @param string
     * @return SitemapSingleVideoItem
     */
    public function setUploader(string $uploader): SitemapSingleVideoItem;

    /**
     * Set live attribute
     *
     * @param string
     * @return SitemapSingleVideoItem
     */
    public function setLive(string $live): SitemapSingleVideoItem;

    /**
     * Fill from array - it should accept strings as keys and values to parse the item
     *
     * @param array $data
     * @return SitemapSingleVideoItem
     */
    public function fillFromArray(array $data): SitemapSingleVideoItem;

    /**
     * Method that should convert this item to XML DOMElement
     *
     * @param DOMCreator $creator
     * @return DOMElement
     */
    public function toDomElement(DOMCreator $creator): DOMElement;
}
