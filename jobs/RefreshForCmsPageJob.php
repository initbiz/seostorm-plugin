<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Jobs;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Illuminate\Queue\Jobs\Job;
use Initbiz\SeoStorm\Models\Settings;
use Initbiz\SeoStorm\Sitemap\Generators\PagesGenerator;

class RefreshForCmsPageJob
{
    /**
     * Fires the job
     *
     * @param Job $job
     * @param array $data ['base_file_name' => 'filename']
     * @return void
     */
    public function fire(Job $job, array $data)
    {
        $jobDispatcher = UniqueQueueJobDispatcher::instance();
        $jobDispatcher->unmarkAsPending(get_class($this), $data);

        $baseFileName = $data['base_file_name'];
        $theme = Theme::getEditTheme();

        $page = Page::load($theme, $baseFileName);

        $this->refreshForCmsPage($page);

        $job->delete();
    }

    public function refreshForCmsPage(Page $page): void
    {
        $settings = Settings::instance();

        foreach ($settings->getSitesEnabledInSitemap() as $site) {
            $pagesGenerator = new PagesGenerator($site);
            $pagesGenerator->refreshForCmsPage($page);
        }
    }
}
