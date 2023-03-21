<?php

use Dynart\Micro\Config;

use Share\App;
use Share\UserService;

function logged_in() {
    return App::instance()->get(UserService::class)->loggedIn();
}

function is_admin() {
    return App::instance()->get(UserService::class)->current('admin', 0);
}

function use_rewrite() {
    return App::instance()->get(Config::class)->get('app.use_rewrite', false);
}