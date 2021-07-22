<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapOtherRoutes();

        $this->mapVueAdminRoutes();

        $this->mapCallbackRoutes();
        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "other" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapOtherRoutes()
    {
        Route::prefix('other')
            ->namespace($this->namespace)
            ->group(base_path('routes/other.php'));
    }


    /**
     * Define the "other" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapVueAdminRoutes()
    {
        Route::prefix('ele')
            ->namespace($this->namespace)
            ->group(base_path('routes/ele.php'));
    }

    protected function mapCallbackRoutes()
    {
        Route::prefix('callback')
            ->namespace($this->namespace)
            ->group(base_path('routes/callback.php'));
    }
}
