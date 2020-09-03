<?php

namespace Arcane\Seo\Updates;

use Schema;
use System\Classes\PluginManager;
use October\Rain\Database\Updates\Migration;

class ExtendRainlabBlogPostsTable extends Migration
{
    public function up()
    {
        if (PluginManager::instance()->hasPlugin('RainLab.Blog')) {
            Schema::table('rainlab_blog_posts', function ($table) {
                $table->text('arcane_seo_options')->nullable();
            });
        }
    }

    public function down()
    {
        if (PluginManager::instance()->hasPlugin('RainLab.Blog')) {
            if (Schema::hasColumn('rainlab_blog_posts', 'arcane_seo_options')) {
                Schema::table('rainlab_blog_posts', function ($table) {
                    $table->dropColumn('arcane_seo_options');
                });
            }
        }
    }
}
