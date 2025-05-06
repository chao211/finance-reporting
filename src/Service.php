<?php

namespace finance;

use think\Service as ThinkService;

class Service extends ThinkService
{
    public function register()
    {
        // 注册命令
    }

    public function boot()
    {

        $this->commands([
            commands\ReportCommand::class
        ]);
    }
}
