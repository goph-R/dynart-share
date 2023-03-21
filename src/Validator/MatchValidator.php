<?php

namespace Share\Validator;

use Dynart\Micro\Validator;

class MatchValidator extends Validator {

    protected $message = 'Mismatch.';

    private $otherName;

    public function __construct(\string $otherName) {
        $this->otherName = $otherName;
    }

    public function validate($value) {
        return $value === $this->form->value($this->otherName);
    }

}