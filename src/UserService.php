<?php

namespace Share;

class UserService {

    private $app;
    private $current;
    private $repository;
    /** @var UserForms */
    private $forms;
    
    public function __construct(App $app) {
        $this->app = $app;
        $this->repository = new UserRepository($app);
        $this->forms = new UserForms($app);
        $this->initCurrent();
    }

    public function repository() {
        return $this->repository;
    }

    public function forms() {
        return $this->forms;
    }

    private function initCurrent() {
        if (!$this->loggedIn()) {
            return;
        }
        $id = $this->app->session('user.id');
        $this->current = $this->repository->findById($id);
        if (!$this->current) {
            $this->logout();
        }
    }

    public function current(string $name, $default=null) {
        return $this->current && array_key_exists($name, $this->current)
            ? $this->current[$name]
            : $default;
    }

    public function loggedIn() {
        return $this->hashLogin() == $this->app->session('user.hash');
    }

    public function login(int $id) {
        $this->app->setSession('user.id', $id);
        $this->app->setSession('user.hash', $this->hashLogin());
    }

    private function hashLogin() {
        return md5(
            $this->app->config(App::CONFIG_SALT)
            .$this->app->requestIp()
            .$this->app->requestHeader('User-Agent')
        );
    }

    public function logout() {
        $this->app->setSession('user.id', null);
        $this->app->setSession('user.hash', null);
    }

}