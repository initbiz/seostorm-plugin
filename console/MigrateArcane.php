<?php

namespace Initbiz\SeoStorm\Console;

use Backend\Models\User;
use Backend\Models\UserRole;
use Illuminate\Console\Command;
use System\Classes\PluginManager;
use Initbiz\SeoStorm\Models\Settings;
use October\Rain\Exception\ApplicationException;
use Arcane\Seo\Models\Settings as ArcaneSettings;

class MigrateArcane extends Command
{
    protected $name = 'migrate:arcane';
    protected $description = 'Migrate the configuration from Arcane.SEO to SEO Storm';

    public function handle()
    {
        if (!PluginManager::instance()->hasPlugin('Arcane.SEO')) {
            throw new ApplicationException("Arcane.SEO is not installed", 1);
        }

        $this->settings();
        $this->rolesPermissions();
        $this->userPermissions();
        $this->rainlabBlog();
    }

    protected function settings()
    {
        $arcaneSettings = ArcaneSettings::instance();
        $stormSettings = Settings::instance();
        foreach ($arcaneSettings->attributes as $key => $value) {
            if ($key === 'id' || $key === 'item') {
                continue;
            }
            $stormSettings->$key = $value;
        }
        $stormSettings->save();
    }

    protected function rolesPermissions()
    {
        $roles = UserRole::all();
        foreach ($roles as $role) {
            $permissions = $role->permissions;
            foreach ($permissions as $key => $value) {
                if (preg_match("/^arcane.seo/", $key)) {
                    $newPermissionCode = preg_replace("/^arcane.seo/", "initbiz.seostorm", $key);
                    $permissions[$newPermissionCode] = $value;
                }
            }
            $role->permissions = $permissions;
            $role->save();
        }
    }

    protected function usersPermissions()
    {
        $users = User::all();
        foreach ($users as $user) {
            $permissions = $user->permissions;
            foreach ($permissions as $key => $value) {
                if (preg_match("/^arcane.seo/", $key)) {
                    $newPermissionCode = preg_replace("/^arcane.seo/", "initbiz.seostorm", $key);
                    $permissions[$newPermissionCode] = $value;
                }
            }
            $user->permissions = $permissions;
            $user->save();
        }
    }

    protected function rainlabBlog()
    {
        if (!PluginManager::instance()->hasPlugin('RainLab.Blog')) {
            return;
        }

        $posts = \RainLab\Blog\Models\Post::all();
        foreach ($posts as $post) {
            if (isset($post->arcane_seo_options)) {
                $post->seo_options = $post->arcane_seo_options;
                $post->save();
            }
        }
    }
}
