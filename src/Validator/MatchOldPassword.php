<?php

namespace Share\Validator;

use Dynart\Micro\Validator;
use Share\UserRepository;
use Share\UserService;

class MatchOldPassword extends Validator {

    protected $message = 'Mismatch.';

    /** @var UserService */
    private $user;

    /** @var UserRepository */
    private $repository;

    public function __construct(UserService $user, UserRepository $repository) {
        $this->user = $user;
        $this->repository = $repository;
    }

    public function validate($value) {
        return $this->repository->hashPassword($value) == $this->user->current('password');
    }

}