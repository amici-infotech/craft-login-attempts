<?php
namespace amici\LoginAttempts\migrations;

use Craft;
use craft\db\Migration;
use amici\LoginAttempts\elements\LoginAttempts;
use amici\LoginAttempts\jobs\UpdateLoginAttemptsTitle;

/**
 * m240408_162530_update_login_attempts_title migration.
 */
class m240408_162530_update_login_attempts_title extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Count total number of login attempts that need updating
        $totalCount = LoginAttempts::find()
            ->title(':empty:')
            ->loginName(':notempty:')
            ->count();

        echo "Found {$totalCount} Login Attempts records with empty title and non-empty loginName to process.\n";

        // Skip adding job to queue if no entries need processing
        if ($totalCount == 0) {
            echo "No records need updating. Migration completed successfully without adding to queue.\n";
            return true;
        }

        // Add the job to the queue to process login attempts in batches
        Craft::$app->getQueue()->push(new UpdateLoginAttemptsTitle([
            'offset' => 0,
            'batchSize' => 100,
            'totalCount' => $totalCount,
        ]));

        echo "Added UpdateLoginAttemptsTitle job to the queue.\n";
        echo "This job will process login attempts in batches of 100 and update their titles based on loginName.\n";
        echo "Each job will show the progress like '1 - 100 out of {$totalCount}'.\n";
        echo "You can monitor progress in the Craft CP utility section under Queue Manager.\n";

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m240408_162530_update_login_attempts_title cannot be reverted.\n";
        return false;
    }
}