<?php
namespace amici\LoginAttempts\migrations;

use craft\db\Migration;

/**
 * m260825_120000_add_login_type_column migration.
 */
class m260825_120000_add_login_type_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%login_attempts}}', 'loginType')) {
            $this->addColumn(
                '{{%login_attempts}}',
                'loginType',
                $this->string()->defaultValue('login')->after('loginName')
            );

            // Existing rows are login attempts
            $this->update('{{%login_attempts}}', ['loginType' => 'login'], ['loginType' => null]);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        if ($this->db->columnExists('{{%login_attempts}}', 'loginType')) {
            $this->dropColumn('{{%login_attempts}}', 'loginType');
        }

        return true;
    }
}
