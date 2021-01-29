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
        $this->loadViewsFrom(__DIR__.'/views', 'ariaPayment');
        $this->mergeConfigFrom(__DIR__ . '/config/aria_payment.php', 'aria_payment');
        $this->publishes([
            __DIR__.'/config/aria_payment.php' => config_path('aria_payment.php'),
        ]);
    }

}
