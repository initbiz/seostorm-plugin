<?php

namespace Initbiz\SeoStorm\EventHandlers;

use Event;
use Initbiz\SeoStorm\Models\SeoOptions;

class SeoStormedModelsHandler
{
    /**
     * Array of model classes to extend
     *
     * @var array
     */
    protected $modelClasses = [];

    public function subscribe($event)
    {
        $this->extendFormWidgets($event);
        $this->addModelClass('\RainLab\Blog\Models\Post');

        /*
         * @event initbiz.seostorm.modelsHandler.listClasses
         * Gives the ability to manage the model classes attribute dynamically
         *
         * Example usage to add a custom class for the extension:
         *
         * Event::listen('initbiz.seostorm.modelsHandler.listClasses', function($handler) {
         *     $handler->addModelClass(Model::class);
         * });
         */
        Event::fire('initbiz.seostorm.modelsHandler.listClasses', [$this]);

        foreach ($this->modelClasses as $modelClass) {
            if (!class_exists($modelClass)) {
                continue;
            }

            $modelClass::extend(function ($model) {
                if (!$model->isClassExtendedWith('Initbiz.SeoStorm.Behaviors.SeoStormed')) {
                    $model->extendClassWith('Initbiz.SeoStorm.Behaviors.SeoStormed');
                }

                if (!isset($model->morphOne)) {
                    $model->addDynamicProperty('morphOne');
                }

                $model->morphOne['seostorm_options'] = [
                    SeoOptions::class,
                    'name' => 'stormed',
                    'table' => 'initbiz_seostorm_seo_options',
                ];
            });

            // Define reverse of the relation in the SeoOptions model
            SeoOptions::extend(function ($model) use ($modelClass) {
                $model->morphTo['stormed_models'][] = [
                    $modelClass,
                    'name' => 'stormed',
                    'table' => 'initbiz_seostorm_seo_options',
                ];
            });
        }
    }

    public function addModelClass(string $className)
    {
        $this->modelClasses[] = $className;
    }

    public function extendFormWidgets($event)
    {
        # code...
        // TODO: in registration: which model what fields
    }
}
