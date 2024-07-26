<?php

namespace Initbiz\Seostorm\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSitemapItemsTable extends Migration
{
    public function up()
    {
        Schema::create('initbiz_seostorm_sitemap_items', function (Blueprint $table) {
            $table->id();

            $table->string('loc')->unique();
            $table->timestamp('lastmod')->nullable();
            $table->string('changefreq', 8)->nullable();
            $table->float('priority', 3, 2)->nullable();

            $table->string('base_file_name')->index();
            $table->boolean('is_enabled')->default(true);

            $table->unsignedInteger('site_definition_id')->nullable();
            $table->foreign('site_definition_id')->references('id')->on('system_site_definitions');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('initbiz_seostorm_sitemap_items');
    }
};