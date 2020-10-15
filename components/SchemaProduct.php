<?php

namespace Arcane\Seo\Components;

use Spatie\SchemaOrg\Schema;
use Cms\Classes\ComponentBase;

class SchemaProduct extends ComponentBase
{
    use \Arcane\Seo\Classes\SchemaComponentTrait;

    public function componentDetails()
    {
        return [
            'name'        => 'arcane.seo::lang.components.schema_product.name',
            'description' => 'arcane.seo::lang.components.schema_product.description'
        ];
    }

    public function onRun()
    {
        $this->setScript(
            Schema::Product()
                ->name($this->property('name'))
                ->description($this->property('description'))
                ->image($this->property('image'))
                ->sku($this->property('sku'))
                ->brand($this->property('brand'))
                ->offers(
                    Schema::Offer()
                        ->priceCurrency($this->property('priceCurrency'))
                        ->price($this->property('price'))
                        ->itemCondition($this->property('itemCondition'))
                        ->availability($this->property('availability'))
                        ->url($this->property('offerUrl'))
                )
                ->toScript()
        );
    }

    public $myProperties = [
        'name' => [
            'title' => 'arcane.seo::lang.components.schema_product.properties.name.title',
            'description' => 'arcane.seo::lang.components.schema_product.properties.name.description',
            'group' => 'Product',
        ],
        'description' => [
            'title' => 'arcane.seo::lang.components.schema_product.properties.description.title',
            'description' => 'arcane.seo::lang.components.schema_product.properties.description.description',
            'group' => 'Product',
        ],
        'image' => [
            'title' => 'arcane.seo::lang.components.schema_product.properties.image.title',
            'description' => 'arcane.seo::lang.components.schema_product.properties.image.description',
            'group' => 'Product',
        ],

        'sku' => [
            'title' => 'arcane.seo::lang.components.schema_product.properties.sku.title',
            'description' => 'arcane.seo::lang.components.schema_product.properties.sku.description',
            'group' => 'Product',
        ],
        'brand' => [
            'title' => 'arcane.seo::lang.components.schema_product.properties.brand.title',
            'description' => 'arcane.seo::lang.components.schema_product.properties.brand.description',
            'group' => 'Product',
        ],
        'priceCurrency' => [
            'title' => 'arcane.seo::lang.components.schema_product.properties.price_currency.title',
            'description' => 'arcane.seo::lang.components.schema_product.properties.price_currency.description',
            'group' => 'Offer',
        ],
        'price' => [
            'title' => 'arcane.seo::lang.components.schema_product.properties.price.title',
            'group' => 'Offer',
            'description' => 'arcane.seo::lang.components.schema_product.properties.price.description',
        ],
        'availability' => [
            'title' => 'arcane.seo::lang.components.schema_product.properties.availability.title',
            'group' => 'Offer',
            'description' => 'arcane.seo::lang.components.schema_product.properties.availability.description'
        ],
        'offerUrl' => [
            'title' => 'arcane.seo::lang.components.schema_product.properties.offer_url.title',
            'description' => 'arcane.seo::lang.components.schema_product.properties.offer_url.description',
            'group' => 'Offer',
        ],
        'ratingValue' => [
            'title' => 'arcane.seo::lang.components.schema_product.properties.rating_value.title',
            'description' => 'arcane.seo::lang.components.schema_product.properties.rating_value.description',
            'group' => 'Reviews',
        ],
        'reviewCount' => [
            'title' => 'arcane.seo::lang.components.schema_product.properties.review_count.title',
            'description' => 'arcane.seo::lang.components.schema_product.properties.review_count.description',
            'group' => 'Reviews',
        ],
    ];

    public function defineProperties()
    {
        return array_merge($this->myProperties);
    }
}
