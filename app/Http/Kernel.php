<?php

namespace App\Http;

use App\Http\Middleware\CheckData;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'trusted.proxies',
            'throttle:60,1',
            'bindings',
        ],

        'data' => [
            \App\Http\Middleware\Check\CheckData::class,
            \App\Http\Middleware\Check\CheckToken::class,
            \App\Http\Middleware\Check\CheckUrl::class,
            \App\Http\Middleware\Check\CheckTime::class,
            \App\Http\Middleware\Check\CheckSign::class,
			\App\Http\Middleware\Check\CheckAuth::class,
        ],

        'admin' => [
            \App\Http\Middleware\Check\CheckAdmin::class,
        ],

        'game' => [
            \App\Http\Middleware\Check\CheckData::class,
            \App\Http\Middleware\Check\CheckTokenForGame::class,
            \App\Http\Middleware\Check\CheckUrl::class,
            \App\Http\Middleware\Check\CheckTime::class,
            \App\Http\Middleware\Check\CheckSignForGame::class,
        ],
		
		'agent_admin' => [
            \App\Http\Middleware\CheckIdentity::class,
        ],

        'agent_lose' => [
            \App\Http\Middleware\LoseEfficacy::class,
        ],
		
		'web_api' => [
            \App\Http\Middleware\CheckWeb::class,
        ],
		'web_other' => [
			\App\Http\Middleware\CheckOther::class,
		],
		'ele_admin_check' => [
            \App\Http\Middleware\EleAdmin\Check::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'trusted.proxies' => \App\Http\Middleware\TrustedProxies::class,
    ];
}
