<?php namespace Initbiz\Seostorm\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('initbiz_seostorm_sitemap_media', function(Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->string('type');
            $table->text('values');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('initbiz_seostorm_sitemap_media');
    }
};
