<?php

namespace Wonbin\Miniprogram\Share;

use Illuminate\Support\ServiceProvider;

/*
 *
 */

class MpSharePosterServiceProvider extends ServiceProvider {

    public function boot() {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/config.php' => config_path('wonbin/mp-share-poster.php')], 'config');

            if (!class_exists('CreatePosterTables')) {
                $timestamp = date('Y_m_d_His', time());
                $this->publishes([
                    __DIR__ . '/../migrations/create_poster_tables.php.stub' => database_path('migrations/' . $timestamp . '_create_poster_tables.php'),
                ], 'migrations');
            }
        }

    }

    public function register() {

        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php','wonbin.mp-share-poster'
        );

        $filesystems = $this->app['config']->get('filesystems.disks', []);

        $this->app['config']->set('filesystems.disks', array_merge(config('wonbin.mp-share-poster.disks'), $filesystems));

        $this->app->register(\Overtrue\LaravelFilesystem\Qiniu\QiniuStorageServiceProvider::class);
        $this->app->register(\VerumConsilium\Browsershot\BrowsershotServiceProvider::class);

    }
}