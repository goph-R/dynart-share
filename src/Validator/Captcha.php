<?php

namespace Share\Validator;

use Dynart\Micro\Validator;
use Dynart\Micro\App;

class Captcha extends Validator {

    private $app;
    private $name;

    public function __construct(App $app, string $name) {
        $this->app = $app;
        $this->name = $name;
        $this->message = 'Mismatch.';
    }

    public function validate($value) {
        $storedValue = $this->app->session($this->name);    
        return $storedValue && $storedValue == $value;
    }

}