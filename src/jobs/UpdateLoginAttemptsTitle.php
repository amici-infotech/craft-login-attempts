<?php
namespace amici\LoginAttempts\jobs;

use Craft;
use craft\queue\BaseJob;
use amici\LoginAttempts\elements\LoginAttempts;

class UpdateLoginAttemptsTitle extends BaseJob
{
    // Properties
    public $offset = 0;
    public $batchSize = 100;
    public $totalCount = null;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        // Get total count of entries that need updating if not provided
        if ($this->totalCount === null) {
            $this->totalCount = LoginAttempts::find()
                ->title(':empty:')
                ->loginName(':notempty:')
                ->count();
        }

        // Find a batch of login attempts that need updating
        $elements = LoginAttempts::find()
            ->title(':empty:')
            ->loginName(':notempty:')
            ->offset($this->offset)
            ->limit($this->batchSize)
            ->all();

        $totalElements = count($elements);

        // If no elements were found, we're done
        if ($totalElements === 0) {
            $this->setProgress($queue, 1);
            return;
        }

        $count = 0;

        foreach ($elements as $index => $element) {
            // Update progress bar
            $this->setProgress($queue, $index / $totalElements);

            // Set the title to the loginName
            $element->title = $element->loginName;

            // Save the element
            if (Craft::$app->elements->saveElement($element)) {
                $count++;
                Craft::info(
                    "Successfully updated LoginAttempts element #{$element->id} - Set title to '{$element->loginName}'",
                    __METHOD__
                );
            } else {
                Craft::error(
                    "Could not update LoginAttempts element #{$element->id}",
                    __METHOD__
                );
            }
        }

        // If we processed a full batch, there might be more - queue up the next batch
        if ($totalElements == $this->batchSize) {
            Craft::$app->getQueue()->push(new UpdateLoginAttemptsTitle([
                'offset' => $this->offset + $this->batchSize,
                'batchSize' => $this->batchSize,
                'totalCount' => $this->totalCount,
            ]));

            $this->setProgress($queue, 1);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        $start = $this->offset + 1;
        $end = $this->offset + $this->batchSize;

        // Get total count if not provided
        if ($this->totalCount === null) {
            $this->totalCount = LoginAttempts::find()
                ->title(':empty:')
                ->loginName(':notempty:')
                ->count();
        }

        // Make sure end doesn't exceed total
        if ($end > $this->totalCount) {
            $end = $this->totalCount;
        }

        // If no elements need processing
        if ($this->totalCount == 0) {
            return Craft::t('login-attempts', 'No Login Attempts records need title updates');
        }

        return Craft::t('login-attempts', 'Updating Login Attempts titles ({start} - {end} out of {total})', [
            'start' => $start,
            'end' => $end,
            'total' => $this->totalCount,
        ]);
    }
}