<?php

namespace Avaztek\PaymentCore;

use Illuminate\Support\ServiceProvider;

class PaymentCoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/payment-core.php', 'payment-core');
        $this->app->singleton(PaymentClient::class, function ($app) {
            return new PaymentClient((array) $app['config']->get('payment-core', array()));
        });
        $this->app->alias(PaymentClient::class, 'payment-core');
    }

    public function boot()
    {
        if (method_exists($this, 'publishes')) {
            $this->publishes(array(__DIR__.'/../config/payment-core.php' => config_path('payment-core.php')), 'payment-core-config');
        }
    }
}
