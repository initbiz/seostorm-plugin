<?php return [
    'plugin' => [
        'name' => 'Arcane SEO',
        'description' => 'Manage all SEO aspects of your site',
    ],
    'schemas' => [
        'id' => 'ID',
        'name' => 'Name',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'template' => 'Template',
    ],
    'components' => [
        'seo' => [
            'name' => 'Seo',
            'description' => 'Renders SEO meta tags in place',
            'properties' => [
                'disable_schema' => [
                    'name' => 'Disable schemas',
                    'description' => 'Enable this if you do not want to output schema scripts from the seo component',
                ]
            ]
        ],
        'schema_article' =>[
            'name' => 'Article (schema.org)',
            'description' => 'Interts a schema.org article in JSON-LD',
            'properties' => [
                'headline' => [
                    'name' => 'Headline',
                    'description' => '',
                ],
                'image' => [
                    'name' => 'Image',
                    'description' => '',
                ]
            ]
        ],
        'schema_product' =>[
            'name' => 'Product (schema.org)',
            'description' => 'Defines a schema.org product',
            'properties' => [
                'name' => [
                    'name' => 'Name',
                    'description' => '',
                ],
                'description' => [
                    'name' => 'Description',
                    'description' => '',
                ],
                'image' => [
                    'name' => 'Image',
                    'description' => '',
                ],
                'sku' => [
                    'name' => 'SKU',
                    'description' => '',
                ],
                'brand' => [
                    'name' => 'Brand',
                    'description' => '',
                ],
                'price_currency' => [
                    'name' => 'Price currency',
                    'description' => 'The currency used to describe the product price, in three-letter ISO 4217 format. ',
                ],
                'price' => [
                    'name' => 'Price',
                    'description' => 'The price of the product. Follow schema.org/price usage guidelines. ',
                ],
                'availability' => [
                    'name' => 'Availability',
                    'description' => 'Value is taken from a constrained list of options, expressed in markup using URL links. Google also understands their short names (for example InStock or OutOfStock, without the full URL scope.) Example: http://schema.org/InStock',
                ],
                'offerUrl' => [
                    'name' => 'URL',
                    'description' => '',
                ],
                'rating_value' => [
                    'name' => 'Rating value',
                    'description' => 'Rating of the product: 0-5, can accept decimals',
                ],
                'reviewCount' => [
                    'name' => 'Review count',
                    'description' => 'Indicate how many people have voted for the product',
                ],
            ]
        ]
    ]
];
