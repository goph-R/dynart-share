<?php

namespace Share;

use Dynart\Micro\Pager;

class UserController {

    private $messages = [
        'add' => [
            'type' => 'success',
            'text' => 'User added succcessfully.'
        ],
        'edit' => [
            'type' => 'success',
            'text' => 'User modify was succcessful.'
        ]
    ];
    
    public function __construct(App $app) {
        $app->route('/login', [$this, 'login'], 'BOTH');
        $app->route('/logout', [$this, 'logout']);
        $app->route('/sign-up', [$this, 'signUp'], 'BOTH');
        $app->route('/sign-up/success', [$this, 'signUpSuccess']);
        $app->route('/captcha', [$this, 'captcha']);
        $app->route('/settings', [$this, 'settings'], 'BOTH');
        $app->route('/users', [$this, 'list'], 'BOTH');
        $app->route('/user-add', [$this, 'add'], 'BOTH');
        $app->route('/user-edit/?', [$this, 'edit'], 'BOTH');
    }

    public function signUp(App $app) {
        $user = $app->user();
        if ($user->loggedIn()) {
            $app->redirect('/');
        }
        $form = $user->forms()->signUp();
        if ($form->process()) {
            $user->repository()->signUp($form);
            $app->redirect('/sign-up/success');
        }
        return $app->view()->layout('sign-up', [
            'form' => $form
        ]);
    }

    public function signUpSuccess(App $app) {
        return $app->view()->layout('sign-up-success');
    }

    public function captcha(App $app) {
        $content = $app->captcha()->createImage();
        $app->setHeader('Content-Type', 'image/png');        
        $app->send($content);
        $app->finish();
    }

    public function login(App $app) {
        $user = $app->user();
        if ($user->loggedIn()) {
            $app->redirect('/');
        }
        $form = $user->forms()->login();
        if ($form->process()) {
            $id = $user->repository()->findIdByLoginForm($form);
            if ($id) {
                $user->login($id);
                $app->redirect('/');
            }
            $form->addError('The username or the password is wrong.');
        }
        return $app->view()->layout('login', [
            'form' => $form
        ]);
    }

    public function logout(App $app) {
        $app->user()->logout();
        $app->redirect('/');
    }

    public function settings(App $app) {
        $app->requireLogin();
        $form = $app->user()->forms()->settings();
        if ($form->process()) {
            $app->user()->repository()->saveSettings($form);
            $app->redirect('/settings', ['success' => 1]);
        }
        return $app->view()->layout('settings', [
            'form' => $form,
            'success' => $app->request('success')
        ]);
    }

    public function list(App $app) {
        $app->requireAdmin();

        $message = $this->processGroupAction($app);
        if (!$message) {
            $this->createMessageFromId($app->request('message_id'));
        }

        $form = $app->user()->forms()->filter();
        $form->bind();
        $params = $form->values();
        unset($params['submit']);

        $tableView = new TableView($app->view(), '/users', $params);
        $columns = [
            'id' => [
                'label' => 'ID',
                'width' => '6%'
            ],
            'username' => [
                'label' => 'Username',
                'width' => '82%'
            ],
            'admin' => [
                'label' => 'Admin',
                'view' => [$tableView, 'checkView'],
                'width' => '6%'
            ],
            'active' => [
                'label' => 'Active',
                'view' => [$tableView, 'checkView'],
                'width' => '6%'
            ],
        ];
        $tableView->setColumns($columns);
        $tableView->addGroupAction('delete', 'Delete selected');
        $tableView->addAction('/user-edit', 'Edit');

        $fields = array_keys($columns);
        $users = $app->user()->repository()->findAll($fields, $params);
        $count = $app->user()->repository()->findAllCount($params);

        $pager = new Pager('/users', $params, $count);
        $tableView->setItems($users);

        return $app->view()->layout('users', [
            'form' => $form,
            'users' => $users,
            'pager' => $pager,
            'tableView' => $tableView,
            'message' => $message
        ]);
    }

    private function createMessageFromId($messageId) {
        $message = '';
        if ($messageId && isset($this->messages[$messageId])) {
            $message = $this->messages[$messageId];
        }
        return $message;
    }

    private function processGroupAction(App $app) {
        if ($app->requestMethod() === 'POST') {
            $groupAction = $app->request('group_action');
            $selected = $app->request('selected');
            if ($groupAction == 'delete') {
                if (in_array($app->user()->current('id'), $selected)) {
                    return ['type' => 'error', 'text' => 'Can not delete yourself!'];
                }
                $app->user()->repository()->deleteByIds($selected);
                return ['type' => 'success', 'text' => 'Deleted '.count($selected).' users successfully'];
            }
        }
        return null;
    }

    public function add(App $app) {
        $app->requireAdmin();
        $user = $app->user();
        $form = $user->forms()->add();
        if ($form->process()) {
            $user->repository()->add($form);
            $app->redirect('/users', ['message_id' => 'add']);
        }
        return $app->view()->layout('user-add', [
            'form' => $form
        ]);
    }

    public function edit(App $app, int $id) {
        $app->requireAdmin();
        $data = $app->user()->repository()->findById($id);
        if (!$data) {
            $app->sendError(404, 'User not found.');
        }
        $form = $app->user()->forms()->edit($id, $data);
        if ($form->process()) {
            $app->user()->repository()->edit($id, $form);
            $app->redirect('/users', ['message_id' => 'edit']);
        }
        return $app->view()->layout('user-edit', [
            'id' => $id,
            'username' => $data['username'],
            'form' => $form
        ]);
    }

}