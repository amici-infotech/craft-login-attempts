<?php
namespace amici\LoginAttempts\base;

use Craft;
use amici\LoginAttempts\services\LoginAttemptsService;

trait PluginTrait
{
    private function _setPluginComponents()
    {
        $this->setComponents([
            'loginAttemptsService' => LoginAttemptsService::class,
        ]);
    }

    public function getLoginAttempts(): LoginAttemptsService
    {
        return $this->get('loginAttemptsService');
    }
}