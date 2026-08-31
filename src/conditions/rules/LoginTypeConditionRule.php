<?php

namespace amici\LoginAttempts\conditions\rules;

use Craft;
use craft\base\conditions\BaseSelectConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;

use amici\LoginAttempts\elements\LoginAttempts;
use amici\LoginAttempts\elements\db\LoginAttemptsLogsQuery;

class LoginTypeConditionRule extends BaseSelectConditionRule implements ElementConditionRuleInterface
{
    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return Craft::t('login-attempts', 'Login Type');
    }

    /**
     * @inheritdoc
     */
    public function getExclusiveQueryParams(): array
    {
        return ['loginType'];
    }

    protected function options(): array
    {
        return [
            ['label' => Craft::t('login-attempts', 'Login'), 'value' => LoginAttempts::TYPE_LOGIN],
            ['label' => Craft::t('login-attempts', 'Forgot Password'), 'value' => LoginAttempts::TYPE_FORGOT_PASSWORD],
            ['label' => Craft::t('login-attempts', 'Reset Password'), 'value' => LoginAttempts::TYPE_RESET_PASSWORD],
        ];
    }

    /**
     * @inheritdoc
     */
    public function modifyQuery(ElementQueryInterface $query): void
    {
        /** @var LoginAttemptsLogsQuery $query */
        $query->loginType($this->value);
    }

    /**
     * @inheritdoc
     */
    public function matchElement(ElementInterface $element): bool
    {
        /** @var LoginAttempts $element */
        return $this->matchValue($element->loginType);
    }
}
