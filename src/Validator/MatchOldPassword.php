<?php

namespace Share\Validator;

use Dynart\Micro\Validator;
use Share\UserService;

class MatchOldPassword extends Validator {

    protected $message = 'Mismatch.';

    /** @var UserService */
    private $user;

    public function __construct(UserService $user) {
        $this->user = $user;
    }

    public function validate($value) {
        return $this->user->repository()->hashPassword($value) == $this->user->current('password');
    }

}