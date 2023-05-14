<?php
namespace amici\LoginAttempts\migrations;

use Craft;
use craft\db\Migration;

class Install extends Migration
{
    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    protected function createTables()
    {
        $this->createTable('{{%login_attempts}}', [
            'id'          => $this->primaryKey(),
            'userId'      => $this->integer(),
            'loginName'   => $this->string(),
            'loginStatus' => $this->string(),
            'ipAddress'   => $this->string(),
            'error'       => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid'         => $this->uid(),
        ]);
    }

    public function addForeignKeys(): void
    {
        $this->addForeignKey(null, '{{%login_attempts}}', 'id', '{{%elements}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%login_attempts}}', 'userId', '{{%users}}', 'id', 'CASCADE');
    }

    public function createIndexes(): void
    {

    }

    public function safeDown()
    {
        $this->dropTable('{{%login_attempts}}');
        $this->_deleteContentData("LoginAttempts");
    }

    function _deleteContentData($element)
    {
        $rows = Craft::$app->getDb()->createCommand("SELECT *  FROM `elements` WHERE `type` LIKE '%" . $element . "%'")->queryAll();
        if(count($rows))
        {
            foreach ($rows as $key => $value)
            {
                Craft::$app->getDb()->createCommand("DELETE FROM `content` WHERE `content`.`elementId` = " . $value["id"])->execute();
            }

            Craft::$app->getDb()->createCommand("DELETE FROM `elements` WHERE `type` LIKE '%" . $element . "%'")->execute();
        }
    }
}