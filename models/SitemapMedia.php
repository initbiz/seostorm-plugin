<?php

namespace Initbiz\Seostorm\Models;

use Model;
use October\Rain\Database\Builder;
use Initbiz\Sitemap\DOMElements\ImageDOMElement;
use Initbiz\Sitemap\DOMElements\VideoDOMElement;

/**
 * SitemapMedia Model
 *
 * @link https://docs.octobercms.com/3.x/extend/system/models.html
 */
class SitemapMedia extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table name
     */
    public $table = 'initbiz_seostorm_sitemap_media';

    /**
     * @var array rules for validation
     */
    public $rules = [
        'loc' => 'required',
        'type' => 'required|in:video,image',
    ];

    public $jsonable = [
        'additional_data'
    ];

    public $belongsToMany = [
        'items' => [
            SitemapItem::class,
            'table' => 'initbiz_seostorm_sitemap_items_media'
        ]
    ];

    public function scopeOnlyImages(Builder $query): Builder
    {
        return $query->where('type', 'image');
    }

    public function scopeOnlyVideos(Builder $query): Builder
    {
        return $query->where('type', 'video');
    }

    public static function fromImageDOMElement(ImageDOMElement $imageDOMElement): SitemapMedia
    {
        $sitemapMedia = SitemapMedia::where('loc', $imageDOMElement->getLoc())->first();
        if (!$sitemapMedia) {
            $sitemapMedia = new SitemapMedia();
            $sitemapMedia->type = 'image';
            $sitemapMedia->loc = $imageDOMElement->getLoc();
        }

        return $sitemapMedia;
    }

    public static function fromVideoDOMElement(VideoDOMElement $videoDOMElement): SitemapMedia
    {
        $sitemapMedia = SitemapMedia::where('loc', $videoDOMElement->getPlayerLoc())->first();
        if (!$sitemapMedia) {
            $sitemapMedia = new SitemapMedia();
            $sitemapMedia->type = 'video';
            $sitemapMedia->loc = $videoDOMElement->getPlayerLoc();
        }

        $additionalData = [
            'thumbnail_loc' => $videoDOMElement->getThumbnailLoc(),
            'title' => $videoDOMElement->getTitle(),
            'description' => $videoDOMElement->getDescription(),
        ];

        $contentLoc = $videoDOMElement->getContentLoc();
        if (!is_null($contentLoc)) {
            $additionalData['content_loc'] = $contentLoc;
        }

        $duration = $videoDOMElement->getDuration();
        if (!is_null($duration)) {
            $additionalData['duration'] = $duration;
        }

        $expirationDate = $videoDOMElement->getExpirationDate();
        if (!is_null($expirationDate)) {
            $additionalData['expiration_date'] = $expirationDate;
        }

        $rating = $videoDOMElement->getRating();
        if (!is_null($rating)) {
            $additionalData['rating'] = $rating;
        }

        $viewCount = $videoDOMElement->getViewCount();
        if (!is_null($viewCount)) {
            $additionalData['view_count'] = $viewCount;
        }

        $publicationDate = $videoDOMElement->getPublicationDate();
        if (!is_null($publicationDate)) {
            $additionalData['publication_date'] = $publicationDate->format('c');
        }

        $familyFriendly = $videoDOMElement->getFamilyFriendly();
        if (!is_null($familyFriendly)) {
            $additionalData['family_friendly'] = $familyFriendly;
        }

        $requiresSubscription = $videoDOMElement->getRequiresSubscription();
        if (!is_null($requiresSubscription)) {
            $additionalData['requires_subscription'] = $requiresSubscription;
        }

        $live = $videoDOMElement->getLive();
        if (!is_null($live)) {
            $additionalData['live'] = $live;
        }

        $sitemapMedia->additional_data = $additionalData;

        return $sitemapMedia;
    }

    /**
     * Delete all media references that doesn't have any items
     *
     * @return void
     */
    public static function deleteGhosts(): void
    {
        SitemapMedia::doesntHave('items')->delete();
    }
}
