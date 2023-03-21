<?php

namespace Share;

use Dynart\Micro\Pager;
use Dynart\Micro\Request;
use Dynart\Micro\Response;
use Dynart\Micro\View;

class UserController {

    /** @var App */
    private $app;

    /** @var Request */
    private $request;

    /** @var View */
    private $view;

    /** @var Response */
    private $response;

    /** @var UserService */
    private $userService;

    /** @var UserForms */
    private $userForms;

    /** @var CaptchaService */
    private $captchaService;

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

    public function __construct(Request $request, Response $response, View $view, UserService $userService, UserForms $userForms, CaptchaService $captchaService) {
        $this->app = App::instance();
        $this->view = $view;
        $this->userService = $userService;
        $this->userForms = $userForms;
        $this->captchaService = $captchaService;
    }

    public function signUp() {
        if ($this->userService->loggedIn()) {
            $this->app->redirect('/');
        }
        $form = $this->userForms->signUp();
        if ($form->process()) {
            $this->userService->signUp($form);
            $this->app->redirect('/sign-up/success');
        }
        return $this->view->layout('sign-up', [
            'form' => $form
        ]);
    }

    public function signUpSuccess() {
        return $this->view->layout('sign-up-success');
    }

    public function captcha() {
        $content = $this->captchaService->createImage();
        $this->response->setHeader('Content-Type', 'image/png');
        $this->response->send($content);
        $this->app->finish();
    }

    public function login() {
        if ($this->userService->loggedIn()) {
            $this->app->redirect('/');
        }
        $form = $this->userForms->login();
        if ($form->process()) {
            $id = $this->userService->findIdByLoginForm($form);
            if ($id) {
                $this->userService->login($id);
                $this->app->redirect('/');
            }
            $form->addError('The username or the password is wrong.');
        }
        return $this->view->layout('login', [
            'form' => $form
        ]);
    }

    public function logout() {
        $this->userService->logout();
        $this->app->redirect('/');
    }

    public function settings() {
        $this->app->requireLogin();
        $form = $this->userForms->settings();
        if ($form->process()) {
            $this->userService->saveSettings($form);
            $this->app->redirect('/settings', ['success' => 1]);
        }
        return $this->view->layout('settings', [
            'form' => $form,
            'success' => $this->request->get('success')
        ]);
    }

    public function list() {
        $this->app->requireAdmin();

        $message = $this->processGroupAction();
        if (!$message) {
            $this->createMessageFromId($this->app->request('message_id'));
        }

        $form = $this->userForms->filter();
        $form->bind();
        $params = $form->values();
        unset($params['submit']);

        $tableView = $this->app->get(TableView::class, ['/users', $params]);
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
        $users = $this->app->user()->repository()->findAll($fields, $params);
        $count = $this->app->user()->repository()->findAllCount($params);

        $pager = new Pager('/users', $params, $count);
        $tableView->setItems($users);

        return $this->view->layout('users', [
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

    private function processGroupAction() {
        if ($this->request->method() === 'POST') {
            $groupAction = $this->request->get('group_action');
            $selected = $this->request->get('selected');
            if ($groupAction == 'delete') {
                if (in_array($this->userService->current('id'), $selected)) {
                    return ['type' => 'error', 'text' => 'Can not delete yourself!'];
                }
                $this->userService->deleteByIds($selected);
                return ['type' => 'success', 'text' => 'Deleted '.count($selected).' users successfully'];
            }
        }
        return null;
    }

    public function add() {
        $this->app->requireAdmin();
        $form = $this->userForms->add();
        if ($form->process()) {
            $this->userService->add($form);
            $this->app->redirect('/users', ['message_id' => 'add']);
        }
        return $this->view->layout('user-add', [
            'form' => $form
        ]);
    }

    public function edit(int $id) {
        $this->app->requireAdmin();
        $data = $this->userService->findById($id);
        if (!$data) {
            $this->app->sendError(404, 'User not found.');
        }
        $form = $this->userForms->edit($id, $data);
        if ($form->process()) {
            $this->userService->edit($id, $form);
            $this->app->redirect('/users', ['message_id' => 'edit']);
        }
        return $this->view->layout('user-edit', [
            'id' => $id,
            'username' => $data['username'],
            'form' => $form
        ]);
    }

}