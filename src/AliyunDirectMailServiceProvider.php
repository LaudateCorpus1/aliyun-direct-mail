<?php
namespace BTCCOM\DirectMail;

use Illuminate\Mail\TransportManager;
use Illuminate\Support\ServiceProvider;

class AliyunDirectMailServiceProvider extends ServiceProvider {
    public function boot() {
        $this->publishes([
            __DIR__ . '/config/directmail.php' => config_path('directmail.php')
        ]);
    }

    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/config/directmail.php', 'directmail');

        $this->app->resolving('swift.transport', function (TransportManager $transportManager) {
            $transportManager->extend('directmail', function () {
                $config = $this->config();

                $profile = \DefaultProfile::getProfile($config['region'], $config['app_key'], $config['app_secret']);
                $client = new \DefaultAcsClient($profile);

                return new DirectMailTransport($client, $config['account']['name'], $config['account']['alias']);
            });
        });
    }

    protected function config() {
        return $this->app['config']->get('directmail.directmail');
    }
}