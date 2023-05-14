<?php
/**
 * Login Attempts plugin for Craft CMS 4.x
 *
 * Log all login attempts
 *
 * @link      https://amiciinfotech.com
 * @copyright Copyright (c) 2023 Amici Infotech
 */

namespace amici\LoginAttempts\variables;

use Craft;

use amici\LoginAttempts\Plugin;
use amici\LoginAttempts\elements\LoginAttempts;
use amici\LoginAttempts\elements\db\LoginAttemptsLogsQuery;

/**
 * Helper Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.loginAttempts }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Amici Infotech
 * @package   LoginAttempts
 * @since     1.0.0
 */
class HelperVariable
{
    function activity($criteria = null): LoginAttemptsLogsQuery
    {
        $query = LoginAttempts::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query;
    }
}
