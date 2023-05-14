<?php
namespace amici\LoginAttempts\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

use yii\db\Connection;

class LoginAttemptsLogsQuery extends ElementQuery
{

    // public $id;
    public $userId;
    public $loginName;
    public $loginStatus;
    public $ipAddress;
    public $error;

    /*public function id($value)
    {
        $this->id = $value;
        return $this;
    }*/

    public function init(): void
    {
        $this->withStructure = true;

        parent::init();
    }

    public function userId($value)
    {
        $this->userId = $value;
        return $this;
    }

    public function loginName($value)
    {
        $this->loginName = $value;
        return $this;
    }

    public function loginStatus($value)
    {
        $this->loginStatus = $value;
        return $this;
    }

    public function ipAddress($value)
    {
        $this->ipAddress = $value;
        return $this;
    }

    public function error($value)
    {
        $this->error = $value;
        return $this;
    }

    /*public function populate($rows)
    {

        $rows = parent::populate($rows);

        echo "<pre>";
        print_r($rows);
        exit();

    }*/

    protected function beforePrepare(): bool
    {

        $this->joinElementTable('login_attempts');

        $this->query->select([
            'login_attempts.userId',
            'login_attempts.loginName',
            'login_attempts.loginStatus',
            'login_attempts.ipAddress',
            'login_attempts.error',
        ]);

        if ($this->userId)
        {
            $this->subQuery->andWhere(Db::parseParam('login_attempts.userId', $this->userId));
        }

        if ($this->loginName)
        {
            $this->subQuery->andWhere(Db::parseParam('login_attempts.loginName', $this->loginName));
        }

        if ($this->loginStatus)
        {
            $this->subQuery->andWhere(Db::parseParam('login_attempts.loginStatus', $this->loginStatus));
        }

        if ($this->ipAddress)
        {
            $this->subQuery->andWhere(Db::parseParam('login_attempts.ipAddress', $this->ipAddress));
        }

        if ($this->error)
        {
            $this->subQuery->andWhere(Db::parseParam('login_attempts.error', $this->error));
        }

        return parent::beforePrepare();

    }
}