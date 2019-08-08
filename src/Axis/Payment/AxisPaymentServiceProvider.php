<?php

namespace Axis\Payment;

use Illuminate\Support\ServiceProvider;

class AxisPaymentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
	{
		if (function_exists('config_path')) {
			$this->publishes([
				__DIR__.'../../config/config.php' => config_path('payment_gateway.php'),
			], 'config');
		}
	}
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->mergeConfigFrom(__DIR__.'/config/config.php', 'shopping_cart');

        $this->app->singleton(PaymentGateway::class, function () {
           
            $ecnKey = config('payment_gateway.ecn_key');
            $secureSecret = config('payment_gateway.secure_secret');
            $merchantCode = config('payment_gateway.merchant_access_code');
            $merchantid = config('payment_gateway.merchant_id');
            $url = config('payment_gateway.gateway_url');

            return new PaymentGateway(config('payment_gateway'));
        });
        $this->app->alias(PaymentGateway::class, 'Payment');
    }
}
