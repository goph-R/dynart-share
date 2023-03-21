<?php

namespace Share;

use Dynart\Micro\View;

require_once 'vendor/dynart/micro/views/functions.php';
require_once 'views/functions.php';

class App extends \Dynart\Micro\WebApp {

    /** @var UserService */
    private $userService;

    public function __construct($configPaths = []) {
        parent::__construct($configPaths);
        $this->add(TableView::class);
        $this->add(UserRepository::class);
        $this->add(UserForms::class);
        $this->add(UserService::class);
        $this->add(UserController::class);
        $this->add(CaptchaService::class);
    }

    public function init() {
        parent::init();

        $this->userService = $this->get(UserService::class);

        $this->router->add('/', [$this, 'index']);

        $this->router->add('/login', [UserController::class, 'login'], 'BOTH');
        $this->router->add('/logout', [UserController::class, 'logout']);
        $this->router->add('/sign-up', [UserController::class, 'signUp'], 'BOTH');
        $this->router->add('/sign-up/success', [UserController::class, 'signUpSuccess']);
        $this->router->add('/captcha', [UserController::class, 'captcha']);
        $this->router->add('/settings', [UserController::class, 'settings'], 'BOTH');
        $this->router->add('/users', [UserController::class, 'list'], 'BOTH'); // BOTH? for filtering use GET!
        $this->router->add('/user-add', [UserController::class, 'add'], 'BOTH');
        $this->router->add('/user-edit/?', [UserController::class, 'edit'], 'BOTH');
    }

    public function index() {
        return $this->get(View::class)->layout('index');
    }

    public function requireAdmin() {
        $this->requireLogin();
        if (!$this->userService->current('admin')) {
            $this->sendError(403, "Forbidden access");
        }
    }

    public function requireLogin() {
        if (!$this->userService->loggedIn()) {
            $this->redirect('/login');
        }
    }

}