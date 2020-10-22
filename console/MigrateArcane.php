<?php

namespace Initbiz\SeoStorm\Console;

use Illuminate\Console\Command;
use Initbiz\SeoStorm\Classes\Migrator;

class MigrateArcane extends Command
{
    protected $name = 'migrate:arcane';
    protected $description = 'Migrate the configuration from Arcane.SEO to SEO Storm';

    public function handle()
    {
        Migrator::migrate();
    }
}
