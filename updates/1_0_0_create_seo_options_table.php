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
            $table->integer('stormed_id');
            $table->string('stormed_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('initbiz_seostorm_seo_options');
    }
}
