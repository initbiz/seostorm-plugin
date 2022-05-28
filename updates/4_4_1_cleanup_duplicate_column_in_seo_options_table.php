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
        $seoOptions = SeoOptions::select("id", DB::raw("CONCAT(initbiz_seostorm_seo_options.stormed_type,'-',initbiz_seostorm_seo_options.stormed_id) AS unique_id"))
            ->get()->groupBy('unique_id');

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
