<?php

namespace Initbiz\SeoStorm\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSeoOptionsTable extends Migration
{
    public function up()
    {
        Schema::create('initbiz_seostorm_seo_options', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('options');
            $table->integer('stormed_id')->nullable();
            $table->string('stormed_type')->nullable();
            $table->index(['stormed_id', 'stormed_type'], 'initbiz_seostorm_options_stormed_index');
        });
    }

    public function down()
    {
        if (Schema::hasTable('initbiz_seostorm_seo_options')) {
            Schema::table('initbiz_seostorm_seo_options', function ($table) {
                $table->dropIndex('initbiz_seostorm_options_stormed_index');
            });

            Schema::drop('initbiz_seostorm_seo_options');
        }
    }
}
