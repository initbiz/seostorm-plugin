<?php

namespace Initbiz\Seostorm\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSitemapMediaTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('initbiz_seostorm_sitemap_media')) {
            return;
        }

        Schema::create('initbiz_seostorm_sitemap_media', function (Blueprint $table) {
            $table->id();
            $table->string('loc')->unique('initbiz_seostorm_sitemap_media_loc_unique');
            $table->string('type', 10)->index('initbiz_seostorm_sitemap_media_type_index');
            $table->text('additional_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        if (Schema::hasTable('initbiz_seostorm_sitemap_media')) {
            Schema::table('initbiz_seostorm_sitemap_media', function ($table) {
                $table->dropIndex('initbiz_seostorm_sitemap_media_loc_unique');
                $table->dropIndex('initbiz_seostorm_sitemap_media_type_index');
            });
        }

        Schema::dropIfExists('initbiz_seostorm_sitemap_media');
    }
};
