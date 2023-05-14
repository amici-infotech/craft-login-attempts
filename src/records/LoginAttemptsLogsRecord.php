<?php
namespace amici\LoginAttempts\records;

use craft\commerce\records\Subscription;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

class LoginAttemptsLogsRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%login_attempts}}';
    }
}
