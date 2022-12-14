<?php

namespace Share;

use Dynart\Micro\Form;
use Dynart\Micro\Repository;

class UserRepository extends Repository {
    
    protected $table = 'user';

    public function findIdByLoginForm(Form $form) {
        $sql = "select `id` from `user`";
        $sql .= " where `username` = :username and `password` = :password and active = 1";
        $sql .= " limit 1";
        return $this->db->fetchColumn($sql, [
            ':username' => $form->value('username'),
            ':password' => $this->hashPassword($form->value('password'))
        ]);
    }

    public function usernameExists(string $username) {
        $sql = "select 1 from `user` where lower(`username`) = :username limit 1";
        $result = $this->db->fetchColumn($sql, [
            ':username' => strtolower($username)
        ]);
        return $result ? true : false;
    }

    public function signUp(Form $form) {
        $this->db->insert('user', [
            'username' => $form->value('username'),
            'password' => $this->hashPassword($form->value('password')),
            'active' => 1
        ]);
    }    

    public function saveSettings(Form $form) {
        $values = [
            'password' => $this->hashPassword($form->value('password'))
        ];
        $this->db->update('user', $values,'id = :id', [
            ':id' => $this->app->session('user.id')
        ]);
    }

    public function edit(int $id, Form $form) {
        $values = [
            'admin' => $form->value('admin') ? 1 : 0,
            'active' => $form->value('active') ? 1 : 0
        ];
        if ($form->value('password')) {
            $values['password'] = $this->hashPassword($form->value('password'));
        }
        $this->db->update('user', $values, 'id = :id', [
            ':id' => $id
        ]);        
    }

    public function add(Form $form) {
        $this->db->insert('user', [
            'username' => $form->value('username'),
            'password' =>  $this->hashPassword($form->value('password')),
            'admin' => $form->value('admin') ? 1 : 0,
            'active' => $form->value('active') ? 1 : 0
        ]);
    }

    public function hashPassword(string $password) {
        return md5($this->app->config(App::CONFIG_SALT).$password);
    }

    protected function getWhere(array $params) {
        if (isset($params['text']) && $params['text']) {
            $text = '%'.str_replace('%', '\\%', $params['text']).'%';
            $this->sqlParams[':text'] = $text;
            return ' where username like :text';
        }
        return '';
    }

}