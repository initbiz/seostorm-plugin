<?php

namespace Initbiz\Seostorm\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSitemapMediaTable extends Migration
{
    public function up()
    {
        Schema::create('initbiz_seostorm_sitemap_media', function (Blueprint $table) {
            $table->id();
            $table->string('loc')->unique();
            $table->string('type', 10);
            $table->text('additional_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('initbiz_seostorm_sitemap_media');
    }
};
