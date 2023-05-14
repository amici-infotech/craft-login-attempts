<?php
/**
 * Login Attempts plugin for Craft CMS 4.x
 *
 * Log all login attempts
 *
 * @link      https://amiciinfotech.com
 * @copyright Copyright (c) 2023 Amici Infotech
 */

namespace amici\LoginAttempts\models;

use amici\LoginAttempts\Plugin;

use Craft;
use craft\base\Model;

/**
 * loginAttempts Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Amici Infotech
 * @package   loginAttempts
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public $pluginName = 'Login Attempts';
    public $showEditUserLoginActivityTab = true;

    // Public Methods
    // =========================================================================

    public function getSettingsNavItems(): array
    {
        $ret = [];
        if(Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $ret +=  [
                'local' => [
                    'label'     => Craft::t('login-attempts', 'General Settings'),
                    'url'       => 'login-attempts',
                    'action'    => 'login-attempts/settings/save-general-settings',
                    'redirect'  => 'login-attempts/settings',
                    'selected'  => 'local',
                    'template'  => 'login-attempts/_templates/general',
                    'hidden'    => false,
                ]
            ];
        }

        $ret +=  [
            'logs' => [
                'label'     => Craft::t('login-attempts', 'Logs'),
                'url'       => 'login-attempts/logs',
                'action'    => '',
                'redirect'  => 'login-attempts/logs',
                'selected'  => 'logs',
                'template'  => 'login-attempts/_templates/logs',
                'hidden'    => true,
            ],
        ];

        return $ret;

    }

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['pluginName'], 'required'],
        ];
    }
}
