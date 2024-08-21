<?php

namespace Initbiz\Seostorm\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSitemapItemsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('initbiz_seostorm_sitemap_items')) {
            return;
        }

        Schema::create('initbiz_seostorm_sitemap_items', function (Blueprint $table) {
            $table->id();

            $table->string('loc')->unique();
            $table->timestamp('lastmod')->nullable();
            $table->string('changefreq', 8)->nullable();
            $table->float('priority', 3, 2)->nullable();

            $table->string('base_file_name')->index('initbiz_seostorm_sitemap_items_base_file_name_index');
            $table->boolean('is_enabled')->default(true);

            $table->unsignedInteger('site_definition_id')->nullable();
            $table->foreign('site_definition_id', 'initbiz_seostorm_sitemap_items_site_definition_id')
                ->references('id')->on('system_site_definitions');

            $table->timestamps();
        });
    }

    public function down()
    {
        if (Schema::hasTable('initbiz_seostorm_sitemap_items')) {
            Schema::table('initbiz_seostorm_sitemap_items', function ($table) {
                $table->dropIndex('initbiz_seostorm_sitemap_items_base_file_name_index');
                $table->dropForeign('initbiz_seostorm_sitemap_items_site_definition_id');
            });
        }
        Schema::dropIfExists('initbiz_seostorm_sitemap_items');
    }
};
