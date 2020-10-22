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
            try {
                $model = new $modelClass;
            } catch (\Throwable $th) {
                continue;
            }

            $model->extend(function ($model) {
                if (!$model->isClassExtendedWith('Initbiz.SeoStorm.Behaviors.SeoStormed')) {
                    $model->extendClassWith('Initbiz.SeoStorm.Behaviors.SeoStormed');
                }
            });

            SeoOptions::extend(function ($model) use ($modelClass) {
                $model->morphTo['seostorm_options'][] = [
                    $modelClass,
                    'name' => 'stormed',
                    'table' => 'initbiz_seostorm_seo_options'
                ];
            });
        }
    }

    public function addModelClass(string $className)
    {
        $this->modelClasses[] = $className;
    }
}
