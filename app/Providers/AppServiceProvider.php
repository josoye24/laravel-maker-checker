<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('api', function ($data = null, $isSuccessful = true, $responseMessage = '', $status = 200) {
            $responseData = [
                'data' => $data,
                'isSuccessful' => $isSuccessful,
                'responseMessage' => $responseMessage,
            ];
        
            return Response::json($responseData, $status);
        });
    }
}
