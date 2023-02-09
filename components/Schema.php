<?php

namespace Initbiz\SeoStorm\Components;

use Initbiz\SeoStorm\Components\Seo;
use Initbiz\SeoStorm\Models\Settings;

class Schema extends Seo
{
    public $publisher;

    public $schemaImage;

    public function componentDetails()
    {
        return [
            'name'        => 'initbiz.seostorm::lang.components.schema.name',
            'description' => 'initbiz.seostorm::lang.components.schema.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'type' => [
                'title'             => 'initbiz.seostorm::lang.components.schema.type.title',
                'description'       => 'initbiz.seostorm::lang.components.schema.type.description',
                'type'              => 'dropdown',
            ],
            'image' => [
                'title'             => 'initbiz.seostorm::lang.components.schema.image.title',
                'description'       => 'initbiz.seostorm::lang.components.schema.image.description',
            ]
        ];
    }

    public function getMainEntityTypeOptions()
    {
        return $this->getTypeOptions();
    }

    public function getTypeOptions()
    {
        return [
            '' => '- Select @type -',
            'WebPage' => 'Web Page',
            'Article' => 'Article',
            'Book' => 'Book',
            'BreadcrumbList' => 'Breadcrumb List',
            'ItemList' => 'Item List',
            'Course' => 'Course',
            'SpecialAnnouncement' => 'Covid 19',
            'Dataset' => 'Dataset',
            'Quiz' => 'Quiz',
            'EmployerAggregateRating' => 'Employer Aggregate Rating',
            'Occupation' => 'Occupation',
            'Event' => 'Event',
            'ClaimReview' => 'ClaimReview',
            'FAQPage' => 'FAQ Page',
            'HowTo' => 'How To',
            'ImageObject' => 'Image Object',
            'JobPosting' => 'Job Posting',
            'LearningResource' => 'Learning Resource',
            'VideoObject' => 'Video Object',
            'LocalBusiness' => 'Local Business',
            'Organization' => 'Logo(Organization)',
            'MathSolver' => 'Math Solver',
            'Movie' => 'Movie',
            'Product' => 'Product',
            'Review' => 'Review',
            'Offer' => 'Offer',
            'QAPage' => 'QA Page',
            'Recipe' => 'Recipe',
            'AggregateRating' => 'Aggregate Rating',
            'SoftwareApplication' => 'Software Application',
            'CreativeWork' => 'Creative Work',
            'Clip' => 'Clip',
            'BroadcastEvent' => 'Broadcast Event',
        ];
    }

    public function onRun()
    {
        $this->prepareVars();
    }

    public function prepareVars()
    {
        $this->type = $this->page['schema_type'] = $this->property('type');
        $this->schemaImage = $this->page['schema_image'] = $this->property('image');
        $this->getPublisher();
    }

    public function getPublisher()
    {
        $settings = $this->getSettings();
        $this->publisher = [
            'type' => $settings['publisher_type'],
            'name' => $settings['publisher_name'],
            'url' => $settings['publisher_url'],
            'logo_url' => $settings['publisher_logo_url'],
            'same_as' => $settings['publisher_same_as'],
        ];
    }
}
