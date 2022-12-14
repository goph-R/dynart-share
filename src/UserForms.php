<?php

namespace Share;

use Dynart\Micro\Form;

use Share\Validator\Password;
use Share\Validator\MatchOldPassword;
use Share\Validator\MatchValidator;
use Share\Validator\UsernameNotInUse;
use Share\Validator\Captcha;

class UserForms {

    private $app;

    public function __construct(App $app) {
        $this->app = $app;
    }

    public function login() {
        $form = new Form($this->app);
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
                'url' => $this->app->view()->routeUrl('/captcha')
            ],
            'submit' => [
                'label' => '',
                'type' => 'submit',
                'text' => 'Login'
            ]
        ]);
        $form->addValidator('captcha', new Captcha($this->app, 'user.captcha'));        
        return $form;
    }

    public function signUp() {
        $form = new Form($this->app);
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
                'url' => $this->app->view()->routeUrl('/captcha')
            ],
            'submit' => [
                'label' => '',
                'type' => 'submit',
                'text' => 'Sign up'
            ]
        ]);
        $form->addValidator('password', new Password());
        $form->addValidator('password_again', new MatchValidator('password'));
        $form->addValidator('username', new UsernameNotInUse($this->app->user()->repository()));
        $form->addValidator('captcha', new Captcha($this->app, 'user.captcha'));
        return $form;
    }

    public function settings() {
        $form = new Form($this->app);
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
        $form = new Form($this->app, '', false);
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
        $form = new Form($this->app);
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
        if ($id == $this->app->user()->current('id')) {
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
        $form = new Form($this->app);
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