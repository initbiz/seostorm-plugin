<?php namespace Initbiz\Seostorm\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('initbiz_seostorm_sitemap_items', function(Blueprint $table) {
            $table->id();
            $table->string('loc');
            $table->string('base_file_name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('initbiz_seostorm_sitemapitems');
    }
};
