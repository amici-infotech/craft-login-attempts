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

use craft\commerce\base\Model;
use craft\elements\User;
use \DateTime;

class LoginAttemptsLogsModel extends Model
{
    /**
     * @var int|null Payment source ID
     */
    public ?int $id = null;

    /**
     * @var int|null The subscription ID
     */
    public ?int $userId = null;

    /**
     * @var string|null The loginName
     */
    public ?string $loginName = null;

    /**
     * @var string|null The loginStatus
     */
    public ?string $loginStatus = null;

    /**
     * @var string|null The ipAddress
     */
    public ?string $ipAddress = null;

    /**
     * @var string|null The error
     */
    public ?string $error = null;

    public ?DateTime $dateCreated = null;
    public ?DateTime $dateUpdated = null;
    public ?string $uid = null;

    /**
     * @var User|null
     */
    private ?User $_user = null;

    /**
     * Returns the customer identifier
     *
     * @return string
     */
    public function __toString()
    {
        return $this->loginName ?? '';
    }

    /**
     * Returns the user element associated with this log.
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        if (null === $this->_user && $this->userId != null) {
            $this->_user = User::find()->id($this->userId)->one();
        }

        return $this->_user;
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        return [
            [['loginName'], 'required'],
        ];
    }
}
