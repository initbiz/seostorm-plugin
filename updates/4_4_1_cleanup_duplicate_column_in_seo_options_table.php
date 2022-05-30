<?php

namespace Initbiz\SeoStorm\Updates;

use Schema;
use Illuminate\Support\Facades\DB;
use Initbiz\SeoStorm\Models\SeoOptions;
use October\Rain\Database\Updates\Migration;

class CleanupDuplicateColumnInSeoOptionsTable extends Migration
{
    public function up()
    {
        $seoOptions = SeoOptions::all()->groupBy(function ($option) {
            return $option->stormed_type . $option->stormed_id;
        });

        foreach ($seoOptions as $seoOptionGroup) {
            if ($seoOptionGroup->count() > 1) {
                $duplicatedSeoOptions = $seoOptionGroup->toArray();
                if ($duplicatedSeoOptions[0]['id'] > $duplicatedSeoOptions[1]['id']) {
                    SeoOptions::find($duplicatedSeoOptions[0]['id'])->delete();
                } else {
                    SeoOptions::find($duplicatedSeoOptions[1]['id'])->delete();
                }
            }
        }
    }

    public function down()
    {
        return;
    }
}
