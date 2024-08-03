<?php

namespace Initbiz\Seostorm\Models;

use Model;
use October\Rain\Database\Builder;
use Initbiz\Sitemap\DOMElements\ImageDOMElement;
use Initbiz\Sitemap\DOMElements\VideoDOMElement;
use Initbiz\Sitemap\Contracts\ConvertingToDOMElement;

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

    public function beforeSave(): void
    {
        if (str_contains($this->loc, 'http://:/')) {
            if (false) {
                # code...
            }
        }
    }
    /**
     * Create SitemapMedia using image DOM Element
     *
     * @param ImageDOMElement $imageDOMElement
     * @return SitemapMedia
     */
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

    /**
     * Create Sitemap Media from Video DOM Element
     *
     * @param VideoDOMElement $videoDOMElement
     * @return SitemapMedia
     */
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
     * Convert this Sitemap Media item to DOM Element
     * It will return either Image DOM Element or Video DOM Element - depending on the type
     *
     * @return ConvertingToDOMElement
     */
    public function toDOMElement(): ConvertingToDOMElement
    {
        if ($this->type === 'image') {
            return $this->toImageDOMElement();
        } elseif ($this->type === 'video') {
            return $this->toVideoDOMElement();
        }

        throw new \Exception("Unsupported media type");
    }

    /**
     * Convert this Sitemap Media item to Image DOM Element
     *
     * @return ImageDOMElement
     */
    public function toImageDOMElement(): ImageDOMElement
    {
        $imageDOMElement = new ImageDOMElement();

        $imageDOMElement->setLoc($this->loc);

        return $imageDOMElement;
    }

    /**
     * Convert this Sitemap Media item to Video DOM Element
     *
     * @return VideoDOMElement
     */
    public function toVideoDOMElement(): VideoDOMElement
    {
        $videoDOMElement = new VideoDOMElement();

        $videoDOMElement->setPlayerLoc($this->loc);

        $thumbnailLoc = $this->additional_data['thumbnail_loc'] ?? null;
        if (!is_null($thumbnailLoc)) {
            $videoDOMElement->setThumbnailLoc($thumbnailLoc);
        }

        $title = $this->additional_data['title'] ?? null;
        if (!is_null($title)) {
            $videoDOMElement->setTitle($title);
        }

        $description = $this->additional_data['description'] ?? null;
        if (!is_null($description)) {
            $videoDOMElement->setDescription($description);
        }

        $contentLoc = $this->additional_data['content_loc'] ?? null;
        if (!is_null($contentLoc)) {
            $videoDOMElement->setContentLoc($contentLoc);
        }

        $duration = $this->additional_data['duration'] ?? null;
        if (!is_null($duration)) {
            $videoDOMElement->setDuration($duration);
        }

        $expirationDate = $this->additional_data['expiration_date'] ?? null;
        if (!is_null($expirationDate)) {
            $videoDOMElement->setExpirationDate(new \DateTime($expirationDate));
        }

        $rating = $this->additional_data['rating'] ?? null;
        if (!is_null($rating)) {
            $videoDOMElement->setRating((float) $rating);
        }

        $viewCount = $this->additional_data['view_count'] ?? null;
        if (!is_null($viewCount)) {
            $videoDOMElement->setViewCount((int) $viewCount);
        }

        $publicationDate = $this->additional_data['publication_date'] ?? null;
        if (!is_null($publicationDate)) {
            $videoDOMElement->setPublicationDate(new \DateTime($publicationDate));
        }

        $familyFriendly = $this->additional_data['family_friendly'] ?? null;
        if (!is_null($familyFriendly)) {
            $videoDOMElement->setFamilyFriendly((bool) $familyFriendly);
        }

        $requiresSubscription = $this->additional_data['requires_subscription'] ?? null;
        if (!is_null($requiresSubscription)) {
            $videoDOMElement->setRequiresSubscription((bool) $requiresSubscription);
        }

        $live = $this->additional_data['live'] ?? null;
        if (!is_null($live)) {
            $videoDOMElement->setLive((bool) $live);
        }

        return $videoDOMElement;
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
