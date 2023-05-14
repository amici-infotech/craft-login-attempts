<?php
/**
 * Login Attempts plugin for Craft CMS 4.x
 *
 * Log all login attempts
 *
 * @link      https://amiciinfotech.com
 * @copyright Copyright (c) 2023 Amici Infotech
 */

namespace amici\LoginAttempts;

use Craft;
use yii\base\Event;
use yii\web\User;
use yii\web\UserEvent;

use amici\LoginAttempts\base\PluginTrait;
use amici\LoginAttempts\elements\LoginAttempts as LoginAttemptsElement;
use amici\LoginAttempts\models\Settings;
use amici\LoginAttempts\services\App;
use amici\LoginAttempts\variables\HelperVariable;

use craft\base\Model;
use craft\base\Plugin as CraftPlugin;
use craft\controllers\UsersController;
use craft\console\Application as ConsoleApplication;
use craft\events\LoginFailureEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Amici Infotech
 * @package   LoginAttempts
 * @since     1.0.0
 *
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Plugin extends CraftPlugin
{
    use PluginTrait;

    // Static Properties
    // =========================================================================

    public static $app;

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Plugin::$plugin
     *
     * @var LoginAttempts
     */
    public static CraftPlugin $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSection = true;

    public static string $pluginHandle = 'login-attempts';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Plugin::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;
        self::$app = new App();

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'amici\LoginAttempts\console\controllers';
        }

        $request = Craft::$app->getRequest();

        $this->_registerRoutes();
        $this->_registerEvents();
        $this->_registerVariables();
        $this->_setPluginComponents();

        if ($request->getIsCpRequest()) {
            $this->_registerTemplateHooks();
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    /*protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'login-attempts/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }*/

    public function getSettingsResponse(): mixed
    {
        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('login-attempts/settings'));
    }

    public function getCpNavItem(): ?array
    {

        $parent = parent::getCpNavItem();
        $parent['label'] = $this->getSettings()->pluginName;

        if(Craft::$app->getConfig()->getGeneral()->allowAdminChanges)
        {
            $parent['url'] = 'login-attempts';

            $parent['subnav']['settings'] = [
                'label' => Craft::t('login-attempts', 'Settings'),
                'url' => 'login-attempts',
            ];

            $parent['subnav']['logs'] = [
                'label' => Craft::t('login-attempts', 'Logs'),
                'url' => 'login-attempts/logs',
            ];
        }
        else
        {
            $parent['url'] = 'login-attempts/logs';
        }

        if($parent['label'] == "")
        {
            $parent['label'] = "Login Attempts";
        }

        return $parent;

    }

    private function _registerRoutes(): void
    {
        // Register our CP routes
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'login-attempts'               => 'login-attempts/settings/general',
                'login-attempts/settings'      => 'login-attempts/settings/general',
                'login-attempts/logs' => 'login-attempts/settings/logs',
            ]);
        });
    }

    private function _registerVariables(): void
    {
        // Register our variables
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('loginAttempts', HelperVariable::class);
        });
    }

    private function _registerEvents(): void
    {
        Event::on(UsersController::class, UsersController::EVENT_LOGIN_FAILURE, function (LoginFailureEvent $event) {
            $log = new LoginAttemptsElement();

            $log->userId = $event->user ? $event->user->id : null;
            $log->title = Craft::$app->getRequest()->getBodyParam('loginName');
            $log->loginName = $log->title;
            $log->loginStatus = "failed";
            $log->error = $event->message;

            if (Craft::$app->getConfig()->getGeneral()->storeUserIps) {
                $log->ipAddress = Craft::$app->getRequest()->getUserIP();
            }

            Craft::$app->getElements()->saveElement($log);
        });

        Event::on(User::class, User::EVENT_AFTER_LOGIN, function (UserEvent $event) {
            $log = new LoginAttemptsElement();
            $user = Craft::$app->getUser()->getIdentity();

            $log->userId = $user->id;
            $log->title = Craft::$app->getRequest()->getBodyParam('loginName');
            $log->loginName = $log->title;
            $log->loginStatus = "success";
            $log->error = "";

            if (Craft::$app->getConfig()->getGeneral()->storeUserIps) {
                $log->ipAddress = Craft::$app->getRequest()->getUserIP();
            }

            Craft::$app->getElements()->saveElement($log);
        });
    }

    private function _registerTemplateHooks(): void
    {
        if ($this->getSettings()->showEditUserLoginActivityTab) {
            Craft::$app->getView()->hook('cp.users.edit', [$this->getLoginAttempts(), 'addEditUserLoginActivityTab']);
            Craft::$app->getView()->hook('cp.users.edit.content', [$this->getLoginAttempts(), 'addEditUserLoginActivityTabContent']);
        }
    }
}
