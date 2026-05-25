<?php

namespace craft\errors;

use craft\elements\User;
use Throwable;
use yii\base\Exception;
class UserLockedException extends Exception
{
    public $user;
    public function __construct(User $user, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $this->user = $user;
        parent::__construct($message, $code, $previous);
    }
    public function getName()
    {
        return 'User locked';
    }
}
