<?php

namespace Share;

use Dynart\Micro\Form;
use Dynart\Micro\Config;
use Dynart\Micro\Request;
use Dynart\Micro\Session;

class UserService {

    private $current;

    /** @var Config */
    private $config;

    /** @var Request */
    private $request;

    /** @var Session */
    private $session;

    /** @var UserRepository */
    private $repository;
    
    public function __construct(Config $config, Request $request, Session $session, UserRepository $repository) {
        $this->session = $session;
        $this->repository = $repository;
        $this->initCurrent();
    }

    private function initCurrent() {
        if (!$this->loggedIn()) {
            return;
        }
        $id = $this->session->get('user.id');
        $this->current = $this->repository->findById($id);
        if (!$this->current) {
            $this->logout();
        }
    }

    public function current(string $name, $default = null) {
        return $this->current && array_key_exists($name, $this->current)
            ? $this->current[$name]
            : $default;
    }

    public function loggedIn() {
        return $this->hashLogin() == $this->session->get('user.hash');
    }

    public function login(int $id) {
        $this->session->set('user.id', $id);
        $this->session->set('user.hash', $this->hashLogin());
    }

    private function hashLogin() {
        return md5(
            $this->config->get('app.salt')
            .$this->request->ip()
            .$this->request->header('User-Agent')
        );
    }

    public function logout() {
        $this->session->set('user.id', null);
        $this->session->set('user.hash', null);
    }

    public function signUp(Form $form) {
        $this->repository->signUp($form);
    }

    public function findIdByLoginForm(Form $form) {
        return $this->repository->findIdByLoginForm($form);
    }

    public function saveSettings(Form $form) {
        $this->repository->saveSettings($form);
    }

    // admin

    public function deleteByIds(array $ids) {
        $this->repository->deleteByIds($ids);
    }

    public function add(Form $form) {
        $this->repository->add($form);
    }

    public function findById(\int $id) {
        return $this->repository->findById($id);
    }

    public function findAll(array $params) {
        return $this->repository->findAll(null, $params);
    }

    public function findAllCount(array $params) {
        return $this->repository->findAllCount($params);
    }

    public function edit(\int $id, Form $form) {
        $this->repository->edit($id, $form);
    }

}