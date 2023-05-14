<?php
/**
 * Login Attempts plugin for Craft CMS 4.x
 *
 * Log all login attempts
 *
 * @link      https://amiciinfotech.com
 * @copyright Copyright (c) 2023 Amici Infotech
 */

namespace amici\LoginAttempts\controllers;

use amici\LoginAttempts\Plugin;

use Craft;
use craft\elements\User;
use craft\web\Controller;
use craft\helpers\StringHelper;
use craft\helpers\App;

class SettingsController extends Controller
{
    public $settingsSection;
    public $selectedSidebarItem;
    public $plugin;
    public $settingsNav;
    public $selectedNav;

    public function init(): void
    {
        parent::init();
        $this->plugin = Plugin::$plugin;
        $this->settingsNav = $this->plugin->getSettings()->getSettingsNavItems();
        $this->selectedNav = Craft::$app->getRequest()->getSegment(2);
    }

    function actionGeneral()
    {
        $this->_builtGeneralForm();
    }

    public function actionLogs()
    {
        $this->_builtGeneralForm("table");
    }

    public function actionSaveGeneralSettings()
    {

        $this->requirePostRequest();
        $postSettings = Craft::$app->getRequest()->getBodyParam('settings');

        $settings = $this->plugin->getSettings();
        $settings = Plugin::$app->settings->saveSettings($this->plugin, $postSettings);

        if ($settings->hasErrors())
        {
            Craft::$app->getSession()->setError(Craft::t('login-attempts', 'Couldn’t save settings.'));
            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settings
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('login-attempts', 'Settings saved.'));
        return $this->redirectToPostedUrl();

    }

    private function _builtGeneralForm($type = "form", $variables = [])
    {
        $navigation    = $this->settingsNav;
        $settings      = $this->plugin->getSettings();
        $meta['type']        = $type;
        $meta['selectedNav'] = ($this->selectedNav == '' || $this->selectedNav == 'settings') ? 'local' : $this->selectedNav;
        $meta['action']      = $this->settingsNav[$meta['selectedNav']]['action'];
        $meta['redirect']    = $this->settingsNav[$meta['selectedNav']]['redirect'];

        $this->renderTemplate($this->settingsNav[$meta['selectedNav']]['template'], array_merge([
            'settings'  => $settings,
            'meta'      => $meta,
            'navigation'=> $navigation
        ], $variables));
    }
}