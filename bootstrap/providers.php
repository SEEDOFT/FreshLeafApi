<?php

use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;
use L5Swagger\L5SwaggerServiceProvider;

return [
    AppServiceProvider::class,
    TelescopeServiceProvider::class,
    L5SwaggerServiceProvider::class,
];
