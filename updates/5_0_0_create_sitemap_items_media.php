<?php

namespace Initbiz\Seostorm\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('initbiz_seostorm_sitemap_items_media', function (Blueprint $table) {
            $table->unsignedBigInteger('sitemap_item_id');
            $table->unsignedBigInteger('sitemap_media_id');
            $table->primary(['sitemap_item_id', 'sitemap_media_id']);
            $table->foreign('sitemap_item_id')->references('id')->on('initbiz_seostorm_sitemap_items');
            $table->foreign('sitemap_media_id')->references('id')->on('initbiz_seostorm_sitemap_media');
        });
    }

    public function down()
    {
        Schema::dropIfExists('initbiz_seostorm_sitemap_items_media');
    }
};
