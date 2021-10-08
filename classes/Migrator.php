<?php

namespace Initbiz\SeoStorm\Classes;

use Backend\Models\User;
use Backend\Models\UserRole;
use System\Classes\PluginManager;
use Initbiz\SeoStorm\Models\Settings;
use October\Rain\Exception\ApplicationException;

class Migrator
{
    public static function migrate()
    {
        if (!PluginManager::instance()->hasPlugin('Arcane.SEO')) {
            throw new ApplicationException("Arcane.SEO is not installed", 1);
        }

        self::settings();
        self::rolesPermissions();
        self::usersPermissions();
        self::rainlabBlog();
    }

    public static function settings()
    {
        $arcaneSettings = \Arcane\Seo\Models\Settings\Settings::instance();
        $stormSettings = Settings::instance();
        foreach ($arcaneSettings->attributes as $key => $value) {
            if ($key === 'id' || $key === 'item') {
                continue;
            }
            $stormSettings->$key = $value;
        }
        $stormSettings->save();
    }

    public static function rolesPermissions()
    {
        $roles = UserRole::all();
        foreach ($roles as $role) {
            $permissions = $role->permissions;
            if ($permissions) {
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
    }

    public static function usersPermissions()
    {
        $users = User::all();
        foreach ($users as $user) {
            $permissions = $user->permissions;
            if ($permissions) {
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
    }

    public static function rainlabBlog()
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
