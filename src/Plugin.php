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
use yii\base\ActionEvent;
use yii\base\Controller;
use yii\base\Event;
use yii\web\Response;
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
use craft\elements\User as UserElement;
use craft\events\LoginFailureEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use yii\web\Request;

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
    public string $schemaVersion = '5.0.3';

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
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('login-attempts/settings'));
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
            $request = Craft::$app->getRequest();
            $loginName = $request->getBodyParam('loginName');

            $this->_saveLog([
                'userId' => $event->user ? $event->user->id : null,
                'loginName' => $loginName,
                'loginType' => LoginAttemptsElement::TYPE_LOGIN,
                'loginStatus' => 'failed',
                'error' => $event->message,
                'ipAddress' => $request instanceof Request ? $request->getUserIP() : '',
            ]);
        });

        Event::on(User::class, User::EVENT_AFTER_LOGIN, function (UserEvent $event) {
            $request = Craft::$app->getRequest();
            $user = Craft::$app->getUser()->getIdentity();
            $loginName = $request->getBodyParam('loginName');

            $this->_saveLog([
                'userId' => $user->id,
                'loginName' => $loginName,
                'loginType' => LoginAttemptsElement::TYPE_LOGIN,
                'loginStatus' => 'success',
                'error' => '',
                'ipAddress' => $request instanceof Request ? $request->getUserIP() : '',
            ]);
        });

        // Forgot password + reset password (set-password) actions
        Event::on(UsersController::class, Controller::EVENT_AFTER_ACTION, function (ActionEvent $event) {
            $request = Craft::$app->getRequest();

            if (!$request->getIsPost()) {
                return;
            }

            $actionId = $event->action->id;

            if ($actionId === 'send-password-reset-email') {
                $this->_logForgotPassword($event, $request);
                return;
            }

            if ($actionId === 'set-password') {
                $this->_logResetPassword($event, $request);
            }
        });
    }

    private function _logForgotPassword(ActionEvent $event, Request $request): void
    {
        $loginName = $request->getBodyParam('loginName');
        $userId = $request->getBodyParam('userId');
        $user = null;

        if ($userId) {
            $user = Craft::$app->getUsers()->getUserById((int) $userId);
            $loginName = $loginName ?: ($user?->email ?? $user?->username);
        } elseif ($loginName) {
            $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($loginName);
        }

        // Craft may hide failures when preventUserEnumeration is on, so base status on whether a valid user was targeted.
        if ($user) {
            [$loginStatus, $error] = $this->_resolveActionOutcome($event);
            // If Craft returned a generic success while mail failed, still keep failed from outcome.
            if ($loginStatus !== 'failed') {
                $loginStatus = 'success';
                $error = '';
            }
        } else {
            $loginStatus = 'failed';
            $error = Craft::t('app', 'Invalid username or email.');
        }

        $this->_saveLog([
            'userId' => $user?->id,
            'loginName' => $loginName,
            'loginType' => LoginAttemptsElement::TYPE_FORGOT_PASSWORD,
            'loginStatus' => $loginStatus,
            'error' => $error,
            'ipAddress' => $request->getUserIP() ?: '',
        ]);
    }

    private function _logResetPassword(ActionEvent $event, Request $request): void
    {
        $uid = $request->getBodyParam('id') ?: $request->getParam('id');
        $user = null;

        if ($uid) {
            $user = UserElement::find()
                ->uid($uid)
                ->status(null)
                ->one();
        }

        [$loginStatus, $error] = $this->_resolveActionOutcome(
            $event,
            Craft::t('app', 'Couldn’t update password.')
        );

        $this->_saveLog([
            'userId' => $user?->id,
            'loginName' => $user?->email ?? $user?->username,
            'loginType' => LoginAttemptsElement::TYPE_RESET_PASSWORD,
            'loginStatus' => $loginStatus,
            'error' => $error,
            'ipAddress' => $request->getUserIP() ?: '',
        ]);
    }

    /**
     * Best-effort success/failure detection from a controller action result.
     *
     * @return array{0: string, 1: string} [loginStatus, error]
     */
    private function _resolveActionOutcome(ActionEvent $event, string $defaultError = ''): array
    {
        $result = $event->result;

        if ($result instanceof Response) {
            if ($result->getIsClientError() || $result->getIsServerError()) {
                $message = '';
                if (is_array($result->data) && !empty($result->data['message'])) {
                    $message = (string) $result->data['message'];
                }

                return ['failed', $message ?: $defaultError];
            }

            if ($result->getIsRedirection()) {
                return ['success', ''];
            }

            if (is_array($result->data)) {
                if (isset($result->data['errors']) || isset($result->data['error'])) {
                    $message = $result->data['message'] ?? $defaultError;
                    return ['failed', is_string($message) ? $message : $defaultError];
                }

                // JSON success payloads from Craft typically include a message / status
                if (array_key_exists('message', $result->data) || array_key_exists('status', $result->data)) {
                    return ['success', ''];
                }
            }
        }

        $errorFlash = Craft::$app->getSession()->getFlash('error', null, false);
        if ($errorFlash) {
            return ['failed', is_array($errorFlash) ? implode(', ', $errorFlash) : (string) $errorFlash];
        }

        $noticeFlash = Craft::$app->getSession()->getFlash('notice', null, false);
        if ($noticeFlash) {
            return ['success', ''];
        }

        // HTML set-password failure re-renders the form (200) without a clear signal;
        // treat non-redirect HTML responses without a success flash as failed when default error is set.
        if ($result instanceof Response && !$result->getIsRedirection() && $defaultError !== '') {
            // Only mark failed when route params carry errors (Craft asFailure for HTML)
            $routeParams = Craft::$app->getUrlManager()->getRouteParams();
            if (!empty($routeParams['errors'])) {
                $errors = $routeParams['errors'];
                $message = is_array($errors) ? implode(', ', $errors) : (string) $errors;
                return ['failed', $message ?: $defaultError];
            }
        }

        return ['success', ''];
    }

    private function _saveLog(array $attributes): void
    {
        $log = new LoginAttemptsElement();
        $log->userId = $attributes['userId'] ?? null;
        $log->loginName = $attributes['loginName'] ?? null;
        $log->title = $log->loginName;
        $log->loginType = $attributes['loginType'] ?? LoginAttemptsElement::TYPE_LOGIN;
        $log->loginStatus = $attributes['loginStatus'] ?? 'failed';
        $log->error = $attributes['error'] ?? '';
        $log->ipAddress = $attributes['ipAddress'] ?? '';

        Craft::$app->getElements()->saveElement($log);
    }

    private function _registerTemplateHooks(): void
    {
        if ($this->getSettings()->showEditUserLoginActivityTab) {
            Craft::$app->getView()->hook('cp.users.edit', [$this->getLoginAttempts(), 'addEditUserLoginActivityTab']);
            Craft::$app->getView()->hook('cp.users.edit.content', [$this->getLoginAttempts(), 'addEditUserLoginActivityTabContent']);
        }
    }
}
