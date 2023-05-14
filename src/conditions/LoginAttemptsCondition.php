<?php

namespace amici\LoginAttempts\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

use amici\LoginAttempts\conditions\rules\LoginStatusConditionRule;
use amici\LoginAttempts\conditions\rules\IpAddressConditionRule;
use amici\LoginAttempts\conditions\rules\UserConditionRule;

class LoginAttemptsCondition extends ElementCondition
{
    /**
     * @inheritdoc
     */
    protected function conditionRuleTypes(): array
    {
        $types = array_merge(parent::conditionRuleTypes(), [
            LoginStatusConditionRule::class,
            IpAddressConditionRule::class,
            UserConditionRule::class,
        ]);

        return $types;
    }
}
