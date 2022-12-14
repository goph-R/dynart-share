<?php

namespace Share\Validator;

use Dynart\Micro\Validator;

class Password extends Validator {

    public function validate($value) {
        if (strlen($value) < 8) {
            $this->message = 'Should be at least 8 characters.';
            return false;
        }
        return true;
    }

}