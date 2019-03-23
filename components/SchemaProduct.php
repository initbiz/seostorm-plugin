<?php namespace Arcane\Seo\Components;

use Cms\Classes\ComponentBase;
use Spatie\SchemaOrg\Schema;

class SchemaProduct extends ComponentBase
{
    use \Arcane\Seo\Classes\SchemaComponentTrait;

    public function componentDetails()
    {
        return [
            'name'        => 'Product (schema.org)',
            'description' => 'Defines a schema.org product'
        ];
    }

    public function onRun() {
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
            'title' => 'Name',
            'group' => 'Product',
        ],
        'description' => [
            'title' => 'Description',
            'group' => 'Product',
        ],
        'image' => [
            'title' => 'Image',
            'group' => 'Product',
        ],

        'sku' => [
            'title' => 'SKU',
            'group' => 'Product',
        ],
        'brand' => [
            'title' => 'Brand',
            'group' => 'Product',
        ],
        'priceCurrency' => [
            'title' => 'Price currency',
            'description' => 'The currency used to describe the product price, in three-letter ISO 4217 format. ',
            'group' => 'Offer',
        ],
        'price' => [
            'title' => 'Price',
            'group' => 'Offer',
            'description' => 'The price of the product. Follow schema.org/price usage guidelines. ',
        ],
        'availability' => [
            'title' => 'Availability',
            'group' => 'Offer',
            'description' => 'Value is taken from a constrained list of options, expressed in markup using URL links. Google also understands their short names (for example InStock or OutOfStock, without the full URL scope.) Example: http://schema.org/InStock'
        ],
        'offerUrl' => [
            'title' => 'URL',
            'group' => 'Offer',
        ],
        'ratingValue' => [
            'title' => 'Rating value',
            'description' => 'Rating of the product: 0-5, can accept decimals',
            'group' => 'Reviews',
        ],
        'reviewCount' => [
            'title' => 'Review count',
            'description' => 'Indicate how many people have voted for the product',
            'group' => 'Reviews',
        ],
    ];

    public function defineProperties()
    {
        return array_merge($this->myProperties);
    }
}