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
    'form' => [
        'settings' => [
            'enable_site_meta' => 'Enable title and description meta tags',
            'enable_sitemap' => 'Enable sitemap.xml',
            'site_name' => 'Site name',
            'site_name_placeholder' => 'Your site name',
            'site_name_position' => 'Site name display',
            'site_name_position_prefix' => 'Prefix (at the start)',
            'site_name_position_suffix' => 'Suffix (at the end)',
            'site_name_position_nowhere' => 'Nowhere (does not appear)',
            'site_name_position_commentAbove' => 'Select how the site name should appear in the title',
            'site_name_separator' => 'Site name separator',
            'site_name_separator_placeholder' => '|',
            'site_name_separator_comment' => 'Character to separate site name from title, eg: Page Title|SiteName',
            'site_name_position' => 'Site name display',
            'site_description' => 'Default description',
            'site_description_comment' => '[data-counter]',
            'site_description_placeholder' => 'Your site description',
            'extra_meta' => 'Additional <head> content',
            'extra_meta_default' => '<!-- Additional meta tags -->',
            'enable_robots_txt' => 'Enable robots.txt',
            'enable_robots_meta' => 'Enable robots meta tags',
            'robots_txt' => 'robots.txt',
            'robots_txt_default' => 'User-agent: *\r\nAllow: /',
            'minify_html' => 'Minify HTML automatically',
            'minify_css' => 'Minify CSS',
            'minify_js' => 'Minify JS',
            'no_minify_for_dev' => 'Disable minification when ENV=dev',
            'favicon_enabled' => 'Enable favicon.ico',
            'favicon_16' => 'Resize favicon to 16x16',
            'favicon' => 'Select your favicon',
            'htaccess' => 'Edit your .htaccess',
            'enable_og' => 'Enable Open Graph',
            'site_image' => 'Site image',
            'fb_app_id' => 'Facebook App ID',
            'og_locale' => 'og:locale',
        ]
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
        ],
        'schema_video' =>[
            'name' => 'Video (schema.org)',
            'description' => 'Inserts an schema.org VideoObject',
            'properties' => [
                'name' => [
                    'name' => 'Name',
                    'description' => 'Name of the video ',
                ],
                'description' => [
                    'name' => 'Description',
                    'description' => 'Description of the video',
                ],
                'thumbnail_url' => [
                    'name' => 'Thumbnail URL',
                    'description' => 'Thumnail of the video',
                ],
                'upload_date' => [
                    'name' => 'Upload Date',
                    'description' => 'Upload date of the video',
                ],
                'duration' => [
                    'name' => 'Duration',
                    'description' => 'Duration of the video',
                ],
                'interaction_count' => [
                    'name' => 'Interaction count',
                    'description' => 'Number of times the video has been viewed ',
                ],
            ]
        ],
    ]
];
