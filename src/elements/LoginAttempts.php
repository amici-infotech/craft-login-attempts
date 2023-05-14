<?php
namespace amici\LoginAttempts\elements;

use Craft;
use craft\base\Element;
use craft\elements\actions\Delete;
use craft\elements\actions\DeleteForSite;
use craft\elements\actions\Restore;
use craft\elements\actions\SetStatus;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\UrlHelper;

use amici\LoginAttempts\conditions\LoginAttemptsCondition;
use amici\LoginAttempts\elements\db\LoginAttemptsLogsQuery;
use amici\LoginAttempts\records\LoginAttemptsLogsRecord;

class LoginAttempts extends Element
{
    const STATUS_LIVE = 'live';
    const STATUS_DISABLED = 'disabled';

    // public $id;
    public $userId;
    public $loginName;
    public $loginStatus;
    public $ipAddress;
    public $error;

    private $_user;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('login-attempts', 'Login Name');
    }

    /**
     * @inheritdoc
     */
    public static function lowerDisplayName(): string
    {
        return Craft::t('login-attempts', 'login name');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('login-attempts', 'Login Names');
    }

    /**
     * @inheritdoc
     */
    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('login-attempts', 'login names');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle(): ?string
    {
        return 'logs';
    }

    /**
     * @inheritdoc
     */
    public static function trackChanges(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    public static function hasUris(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_LIVE => Craft::t('login-attempts', 'Live'),
            self::STATUS_DISABLED => Craft::t('login-attempts', 'Disabled'),
        ];
    }

    public static function find(): ElementQueryInterface
    {
        return new LoginAttemptsLogsQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            [
                'label' => Craft::t('login-attempts', 'Date Created'),
                'orderBy' => 'elements.dateCreated',
                'attribute' => 'dateCreated',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('login-attempts', 'Date Updated'),
                'orderBy' => 'elements.dateUpdated',
                'attribute' => 'dateUpdated',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('login-attempts', 'ID'),
                'orderBy' => 'elements.id',
                'attribute' => 'id',
            ],
            [
                'label' => Craft::t('login-attempts', 'User ID'),
                'orderBy' => 'login_attempts.userId',
                'attribute' => 'userId',
            ],
            [
                'label' => Craft::t('login-attempts', 'Login Name'),
                'orderBy' => 'login_attempts.loginName',
                'attribute' => 'loginName',
            ],
            [
                'label' => Craft::t('login-attempts', 'Login Status'),
                'orderBy' => 'login_attempts.loginStatus',
                'attribute' => 'loginStatus',
            ],
            [
                'label' => Craft::t('login-attempts', 'IP Address'),
                'orderBy' => 'login_attempts.ipAddress',
                'attribute' => 'ipAddress',
            ],
            [
                'label' => Craft::t('login-attempts', 'Error'),
                'orderBy' => 'login_attempts.error',
                'attribute' => 'error',
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('login-attempts', 'Logs'),
                'default' => true
            ]
        ];

        return $sources;
    }

    protected static function defineActions(string $source = null): array
    {
        $actions = [];
        $elementsService = Craft::$app->getElements();

        // $actions[] = DeleteForSite::class;
        $actions[] = Delete::class;

        $actions[] = SetStatus::class;

        // Restore
        $actions[] = $elementsService->createAction([
            'type'                  => Restore::class,
            'successMessage'        => Craft::t('login-attempts', 'Logs restored.'),
            'partialSuccessMessage' => Craft::t('login-attempts', 'Some logs restored.'),
            'failMessage'           => Craft::t('login-attempts', 'Logs not restored.'),
        ]);
        return $actions;
    }

    public function canSave(User $user): bool
    {
        return true;
    }

    public function canView(User $user): bool
    {
        return true;
    }

    public function canDelete(User $user): bool
    {
        return true;
    }

    public function canDeleteForSite(User $user): bool
    {
        return true;
    }

    protected static function includeSetStatusAction(): bool
    {
        return true;
    }
    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'loginName'   => ['label' => Craft::t('login-attempts', 'Login Name')],
            'id'          => ['label' => Craft::t('login-attempts', 'ID')],
            'loginStatus' => ['label' => Craft::t('login-attempts', 'Login Status')],
            'ipAddress'   => ['label' => Craft::t('login-attempts', 'IP Address')],
            'error'       => ['label' => Craft::t('login-attempts', 'Error')],
            'userId'      => ['label' => Craft::t('login-attempts', 'User ID')],
            'dateCreated' => ['label' => Craft::t('login-attempts', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('login-attempts', 'Date Updated')],
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = [
            'ipAddress',
            'error',
            'dateCreated',
            'loginStatus',
            'userId',
        ];

        return $attributes;
    }

    protected static function defineSearchableAttributes(): array
    {
        $attributes = [
            'loginName',
            'id',
            'loginStatus',
            'ipAddress',
            'error',
            'userId',
            'dateCreated',
            'dateUpdated',
        ];

        return $attributes;
    }

    public static function createCondition(): ElementConditionInterface
    {
        return Craft::createObject(LoginAttemptsCondition::class, [static::class]);
    }

    public function getCpEditUrl(): ?string
    {
        return null;
    }

    /**
     * Returns the element’s edit URL in the control panel.
     *
     * @return string|null
     * @since 3.7.0
     */
    protected function cpEditUrl(): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getPostEditUrl(): ?string
    {
        return null;
    }

    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {

            case 'userId':
                $userId = $this->getUser();
                return $userId ? Cp::elementHtml($userId) : '';

            case 'loginStatus':
                return "<span class='" . ($this->loginStatus == "success" ? "success" : "error") . "'>" . ucfirst($this->loginStatus) . "</span>";

        }

        return parent::tableAttributeHtml($attribute);
    }

    public function getIsDeletable(): bool
    {
        return true;
    }

    public function beforeSave(bool $isNew): bool
    {
        return parent::beforeSave($isNew);
    }

    public function afterSave(bool $isNew): void
    {
        if ($isNew)
        {
            $record = new LoginAttemptsLogsRecord();
            $record->id = $this->id;
        }
        else
        {
            $record = LoginAttemptsLogsRecord::findOne($this->id);

            if (! $record)
            {
                throw new Exception('Invalid node ID: ' . $this->id);
            }
        }

        $record->userId = $this->userId;
        $record->loginName = $this->loginName;
        $record->loginStatus = $this->loginStatus;
        $record->ipAddress = $this->ipAddress;
        $record->error = $this->error;

        $record->save(false);

        parent::afterSave($isNew);

    }

    public function getUser()
    {
        if ($this->_user === null) {
            if ($this->userId === null) {
                return null;
            }

            if (($this->_user = User::find()->id($this->userId)->one()) === null) {
                // The author is probably soft-deleted. Just no author is set
                $this->_user = false;
            }
        }

        return $this->_user ?: null;
    }
}