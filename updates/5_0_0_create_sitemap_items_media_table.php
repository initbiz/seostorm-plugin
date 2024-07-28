<?php

namespace Initbiz\Seostorm\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSitemapItemsMediaTable extends Migration
{
    public function up()
    {
        Schema::create('initbiz_seostorm_sitemap_items_media', function (Blueprint $table) {
            $table->unsignedBigInteger('sitemap_item_id');
            $table->unsignedBigInteger('sitemap_media_id');
            $table->primary(['sitemap_item_id', 'sitemap_media_id']);
            $table->foreign('sitemap_item_id', 'initbiz_seostorm_sitemap_items_media_sitemap_item_id')
                ->references('id')->on('initbiz_seostorm_sitemap_items');
            $table->foreign('sitemap_media_id', 'initbiz_seostorm_sitemap_items_media_sitemap_media_id')
                ->references('id')->on('initbiz_seostorm_sitemap_media');
        });
    }

    public function down()
    {
        if (Schema::hasTable('initbiz_seostorm_sitemap_items_media')) {
            Schema::table('initbiz_seostorm_sitemap_items_media', function ($table) {
                $table->dropForeign('initbiz_seostorm_sitemap_items_media_sitemap_item_id');
                $table->dropForeign('initbiz_seostorm_sitemap_items_media_sitemap_media_id');
            });
        }

        Schema::dropIfExists('initbiz_seostorm_sitemap_items_media');
    }
};
