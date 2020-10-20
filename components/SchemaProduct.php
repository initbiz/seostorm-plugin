<?php

namespace Initbiz\Seo\Components;

use Spatie\SchemaOrg\Schema;
use Cms\Classes\ComponentBase;

class SchemaProduct extends ComponentBase
{
    use \Initbiz\Seo\Classes\SchemaComponentTrait;

    public function componentDetails()
    {
        return [
            'name'        => 'initbiz.seo::lang.components.schema_product.name',
            'description' => 'initbiz.seo::lang.components.schema_product.description'
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
            'title' => 'initbiz.seo::lang.components.schema_product.properties.name.title',
            'description' => 'initbiz.seo::lang.components.schema_product.properties.name.description',
            'group' => 'initbiz.seo::lang.components.group.product',
        ],
        'description' => [
            'title' => 'initbiz.seo::lang.components.schema_product.properties.description.title',
            'description' => 'initbiz.seo::lang.components.schema_product.properties.description.description',
            'group' => 'initbiz.seo::lang.components.group.product',
        ],
        'image' => [
            'title' => 'initbiz.seo::lang.components.schema_product.properties.image.title',
            'description' => 'initbiz.seo::lang.components.schema_product.properties.image.description',
            'group' => 'initbiz.seo::lang.components.group.product',
        ],
        'sku' => [
            'title' => 'initbiz.seo::lang.components.schema_product.properties.sku.title',
            'description' => 'initbiz.seo::lang.components.schema_product.properties.sku.description',
            'group' => 'initbiz.seo::lang.components.group.product',
        ],
        'brand' => [
            'title' => 'initbiz.seo::lang.components.schema_product.properties.brand.title',
            'description' => 'initbiz.seo::lang.components.schema_product.properties.brand.description',
            'group' => 'initbiz.seo::lang.components.group.product',
        ],
        'priceCurrency' => [
            'title' => 'initbiz.seo::lang.components.schema_product.properties.price_currency.title',
            'description' => 'initbiz.seo::lang.components.schema_product.properties.price_currency.description',
            'group' => 'initbiz.seo::lang.components.group.offer',
        ],
        'price' => [
            'title' => 'initbiz.seo::lang.components.schema_product.properties.price.title',
            'group' => 'initbiz.seo::lang.components.group.offer',
            'description' => 'initbiz.seo::lang.components.schema_product.properties.price.description',
        ],
        'availability' => [
            'title' => 'initbiz.seo::lang.components.schema_product.properties.availability.title',
            'group' => 'initbiz.seo::lang.components.group.offer',
            'description' => 'initbiz.seo::lang.components.schema_product.properties.availability.description'
        ],
        'offerUrl' => [
            'title' => 'initbiz.seo::lang.components.schema_product.properties.offer_url.title',
            'description' => 'initbiz.seo::lang.components.schema_product.properties.offer_url.description',
            'group' => 'initbiz.seo::lang.components.group.offer',
        ],
        'ratingValue' => [
            'title' => 'initbiz.seo::lang.components.schema_product.properties.rating_value.title',
            'description' => 'initbiz.seo::lang.components.schema_product.properties.rating_value.description',
            'group' => 'initbiz.seo::lang.components.group.reviews',
        ],
        'reviewCount' => [
            'title' => 'initbiz.seo::lang.components.schema_product.properties.review_count.title',
            'description' => 'initbiz.seo::lang.components.schema_product.properties.review_count.description',
            'group' => 'initbiz.seo::lang.components.group.reviews',
        ],
    ];

    public function defineProperties()
    {
        return array_merge($this->myProperties);
    }
}
