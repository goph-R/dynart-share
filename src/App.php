<?php

namespace Share;

class App extends \Dynart\Micro\App {

    /** @var UserService */
    private $user;

    /** @var CaptchaService */
    private $captcha;

    public function run() {        

        $this->route('/', [$this, 'index']);

        $this->user = new UserService($this);
        $this->captcha = new CaptchaService($this);

        new UserController($this);

        parent::run();
    }

    public function user() {
        return $this->user;
    }

    public function captcha() {
        return $this->captcha;
    }

    public function requireLogin() {
        if (!$this->user->loggedIn()) {
            $this->redirect('/login');            
        }
    }

    public function requireAdmin() {
        if (!$this->user->current('admin')) {
            $this->sendError(403, 'Forbidden access.');
        }
    }

    public function index(App $app) {
        return $app->view()->layout('index');
    }

}