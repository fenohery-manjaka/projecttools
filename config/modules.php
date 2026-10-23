<?php

use App\Modules\Core\CoreModule;
use App\Modules\Projects\ProjectsModule;

return [

    /*
    |--------------------------------------------------------------------------
    | Application modules
    |--------------------------------------------------------------------------
    |
    | Every module of the application, in sidebar order. A module is enabled
    | or disabled by the super admin in Settings › Modules; required modules
    | are always on. See docs/architecture.md before adding one.
    |
    */

    'modules' => [
        CoreModule::class,
        ProjectsModule::class,
    ],

];
