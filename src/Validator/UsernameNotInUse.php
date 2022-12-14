<?php

namespace Share\Validator;

use Dynart\Micro\Validator;
use Share\UserRepository;

class UsernameNotInUse extends Validator {

    protected $message = 'Username exists.';

    private $userQuery;

    public function __construct(UserRepository $userQuery) {
        $this->userQuery = $userQuery;
    }

    public function validate($value) {
        return !$this->userQuery->usernameExists($value);
    }

}
