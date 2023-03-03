<?php

namespace Initbiz\SeoStorm\Components;

use Initbiz\SeoStorm\Components\Seo;

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
     * @return string schema image url
     */
    public function getSchemaImageFromSettings()
    {
        $settings = $this->getSettings();
        $schemaImage = $settings->schema_image;

        if ($schemaImage === 'media' && $settings->schema_image) {
            return MediaLibrary::instance()->getPathUrl($settings->schema_image);
        } elseif ($schemaImage === "fileupload") {
            return $settings->site_image_fileupload()->getSimpleValue();
        } elseif ($schemaImage === "url") {
            return $settings->schema_image_url;
        }
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
