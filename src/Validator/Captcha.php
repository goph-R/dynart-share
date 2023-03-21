<?php

namespace Share\Validator;

use Dynart\Micro\Session;
use Dynart\Micro\Validator;

class Captcha extends Validator {

    /** @var Session */
    private $session;
    private $name;

    public function __construct(Session $session, string $name) {
        $this->session = $session;
        $this->name = $name;
        $this->message = 'Mismatch.';
    }

    public function validate($value) {
        $storedValue = $this->session->get($this->name);
        return $storedValue && $storedValue == $value;
    }

}