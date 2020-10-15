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
        ]
    ]
];
