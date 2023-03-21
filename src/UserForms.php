<?php

namespace Share;

use Dynart\Micro\Form;
use Dynart\Micro\Router;

use Share\Validator\Password;
use Share\Validator\MatchOldPassword;
use Share\Validator\MatchValidator;
use Share\Validator\UsernameNotInUse;
use Share\Validator\Captcha;

class UserForms {

    /** @var UserService */
    private $userService;

    /** @var Router */
    private $router;

    /** @var App */
    private $app;

    public function __construct(Router $router, UserService $userService) {
        $this->app = App::instance();
        $this->router = $router;
        $this->userService = $userService;
    }

    public function login() {
        /** @var Form $form */
        $form = $this->app->create(Form::class);
        $form->addFields([
            'username' => [
                'type' => 'text',
                'label' => 'Username'
            ],
            'password' => [
                'type' => 'password',
                'label' => 'Password'
            ],
            'captcha' => [
                'type' => 'captcha',
                'label' => 'Captcha',
                'url' => $this->router->url('/captcha')
            ],
            'submit' => [
                'label' => '',
                'type' => 'submit',
                'text' => 'Login'
            ]
        ]);
        $form->addValidator('captcha', $this->app->create(Captcha::class, ['user.captcha']));
        return $form;
    }

    public function signUp() {
        /** @var Form $form */
        $form = $this->app->create(Form::class);
        $form->addFields([
            'username' => [
                'type' => 'text',
                'label' => 'Username'
            ],
            'password' => [
                'type' => 'password',
                'label' => 'Password'
            ],
            'password_again' => [
                'type' => 'password',
                'label' => 'Password again'
            ],
            'captcha' => [
                'type' => 'captcha',
                'label' => 'Captcha',
                'url' => $this->router->url('/captcha')
            ],
            'submit' => [
                'label' => '',
                'type' => 'submit',
                'text' => 'Sign up'
            ]
        ]);
        $form->addValidator('password', new Password());
        $form->addValidator('password_again', new MatchValidator('password'));
        $form->addValidator('username', $this->app->create(UsernameNotInUse::class));
        $form->addValidator('captcha', $this->app->create(Captcha::class, ['user.captcha']));
        return $form;
    }

    public function settings() {
        /** @var Form $form */
        $form = $this->app->create(Form::class);
        $form->addFields([
            'old_password' => [
                'type' => 'password',
                'label' => 'Current password'
            ],
            'password' => [
                'type' => 'password',
                'label' => 'New password'
            ],
            'password_again' => [
                'type' => 'password',
                'label' => 'New password again'
            ],
            'submit' => [
                'label' => '',
                'type' => 'submit',
                'text' => 'Save'
            ]
        ]);
        $form->setRequired('password', false);
        $form->setRequired('password_again', false);
        $form->addValidator('old_password', new MatchOldPassword($this->app->user()));
        $form->addValidator('password', new Password());
        $form->addValidator('password_again', new MatchValidator('password'));
        return $form;
    }

    public function filter() {
        /** @var Form $form */
        $form = $this->app->create(Form::class, ['', false]);
        $form->addFields([
            'text' => [
                'label' => 'Search for',
                'type' => 'text'
            ],
            'page' => [
                'type' => 'hidden'
            ],
            'page_size' => [
                'label' => 'Show',
                'type' => 'select',
                'options' => [
                    '5' => '5',
                    '10' => '10',
                    '25' => '25',
                    '50' => '50'
                ]
            ],
            'order_by' => [
                'type' => 'hidden'
            ],
            'order_dir' => [
                'type' => 'hidden'
            ],
            'submit' => [
                'type' => 'submit',
                'text' => 'Search'
            ]
        ], false);
        $form->setValues([
            'text' => '',
            'page' => 0,
            'page_size' => 25,
            'order_by' => 'id',
            'order_dir' => 'asc'
        ]);
        return $form;
    }

    public function edit(int $id, array $data) {
        /** @var Form $form */
        $form = $this->app->create(Form::class);
        $form->addFields([
            'password' => [
                'type' => 'text',
                'label' => 'New password'
            ],
            'admin' => [
                'type' => 'checkbox',
                'label' => '',
                'text' => 'Admin'
            ],
            'active' => [
                'type' => 'checkbox',
                'label' => '',
                'text' => 'Active'
            ],
            'submit' => [
                'label' => '',
                'type' => 'submit',
                'text' => 'Save'
            ]
        ], false);
        $form->addValidator('password', new Password());
        if ($id == $this->userService->current('id')) {
            $form->setRequired('active', true);
            $form->setRequired('admin', true);
        }
        $form->setValues([
            'active' => $data['active'],
            'admin' => $data['admin']
        ]);
        return $form;
    }

    public function add() {
        /** @var Form $form */
        $form = $this->app->create(Form::class);
        $form->addFields([
            'username' => [
                'type' => 'text',
                'label' => 'Username'
            ],
            'password' => [
                'type' => 'text',
                'label' => 'Password'
            ],
            'admin' => [
                'type' => 'checkbox',
                'label' => '',
                'text' => 'Admin'
            ],
            'active' => [
                'type' => 'checkbox',
                'label' => '',
                'text' => 'Active'
            ],
            'submit' => [
                'label' => '',
                'type' => 'submit',
                'text' => 'Save'
            ]
        ]);
        $form->addValidator('password', new Password());
        $form->setRequired('admin', false);
        $form->setRequired('active', false);
        return $form;
    }
}