<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Components;

use Media\Classes\MediaLibrary;

class Schema extends Seo
{
    public function componentDetails()
    {
        return [
            'name'        => 'initbiz.seostorm::lang.components.schema.name',
            'description' => 'initbiz.seostorm::lang.components.schema.description'
        ];
    }

    /**
     * Return schema type
     *
     * @return string
     */
    public function getSchemaType()
    {
        return $this->getSeoAttribute('schemaType') ?? '';
    }

    /**
     * Returns schemaRefImage if set
     * else schemaImage if set
     * else fallback to getSchemaImageFromSettings()
     *
     * @return string
     */
    public function getSchemaImage()
    {
        if ($schemaImage = $this->getSeoAttribute('schemaRefImage')) {
            return $schemaImage;
        }

        if ($schemaImage = $this->getSeoAttribute('schemaImage')) {
            return $schemaImage;
        }

        return $this->getSchemaImageFromSettings();
    }

    /**
     * Returns the URL of the schema image
     *
     * @return string|null schema image url
     */
    public function getSchemaImageFromSettings()
    {
        $settings = $this->getSettings();
        $schemaImageFrom = $settings->schema_image_from;

        if ($schemaImageFrom === 'media' && $settings->schema_image) {
            return MediaLibrary::instance()->getPathUrl($settings->schema_image);
        } elseif ($schemaImageFrom === "fileupload") {
            return $settings->schema_image_fileupload?->getPath();
        } elseif ($schemaImageFrom === "url") {
            return $settings->schema_image_url;
        }

        return null;
    }

    /**
     * Return schema mainEntityPage
     *
     * @return array
     */
    public function getSchemaMainEntity()
    {
        $mainEntity = [];
        if ($this->getSeoAttribute('schemaMainEntity')) {
            if ($entityId = $this->getSeoAttribute('schemaMainEntityId')) {
                $mainEntity['id'] = $entityId;
            }

            if ($entityType = $this->getSeoAttribute('schemaMainEntityType')) {
                $mainEntity['type'] = $entityType;
            }
        }

        return $mainEntity;
    }

    /**
     * Return schema publisher
     *
     * @return array
     */
    public function getPublisher()
    {
        $settings = $this->getSettings();
        $publisher = [
            'type' => $settings['publisher_type'] ?? '',
            'name' => $settings['publisher_name'] ?? '',
            'url' => $settings['publisher_url'] ?? '',
            'logo_url' => $settings['publisher_logo_url'] ?? '',
            'same_as' => $settings['publisher_same_as'] ?? '',
        ];

        return $publisher;
    }
}
