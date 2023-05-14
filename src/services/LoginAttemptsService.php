<?php
namespace amici\LoginAttempts\services;

use Craft;

use craft\base\Component;

class LoginAttemptsService extends Component
{
    /**
     * Add User login activity tab to the Edit User page in the control panel.
     */
    public function addEditUserLoginActivityTab(array &$context): void
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        if (!$context['isNewUser'] && ($currentUser->can('accessCp'))) {
            $context['tabs']['userLoginActivity'] = [
                'label' => Craft::t('login-attempts', 'Login Activity'),
                'url' => '#user-login-activity',
            ];
        }
    }

    /**
     * Add user login activity info to the Edit User page in the control panel.
     */
    public function addEditUserLoginActivityTabContent(array $context): string
    {
        if (!$context['user'] || $context['isNewUser']) {
            return '';
        }

        // Craft::$app->getView()->registerAssetBundle(CommerceCpAsset::class);
        return Craft::$app->getView()->renderTemplate('login-attempts/_templates/includes/users/_editUserTab', [
            'user' => $context['user'],
        ]);
    }
}