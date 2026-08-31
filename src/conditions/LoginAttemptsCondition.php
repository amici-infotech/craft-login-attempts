<?php

namespace amici\LoginAttempts\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

use amici\LoginAttempts\conditions\rules\LoginStatusConditionRule;
use amici\LoginAttempts\conditions\rules\LoginTypeConditionRule;
use amici\LoginAttempts\conditions\rules\IpAddressConditionRule;
use amici\LoginAttempts\conditions\rules\UserConditionRule;

class LoginAttemptsCondition extends ElementCondition
{
    /**
     * @inheritdoc
     */
    protected function selectableConditionRules(): array
    {
        $types = array_merge(parent::selectableConditionRules(), [
            LoginStatusConditionRule::class,
            LoginTypeConditionRule::class,
            IpAddressConditionRule::class,
            UserConditionRule::class,
        ]);

        return $types;
    }
}
