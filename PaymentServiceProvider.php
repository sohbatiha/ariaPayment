<?php


namespace Sohbatiha\AriaPayment;


use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('aria_payment', function ($app) {
            return new PaymentManager($app);
        });

    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');

    }

}
