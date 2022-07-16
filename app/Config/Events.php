<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function () {
    // 요청 시작 시점 로깅
    log_message('error', '[INFO] start: ' . $_SERVER['REQUEST_URI'] . ' > IP: ' . $_SERVER['REMOTE_ADDR']);
});
Events::on('post_system', static function () {
    // 요청 종료 시점 로깅
    log_message('error', '[INFO] end: ' . $_SERVER['REQUEST_URI'] . ' > IP: ' . $_SERVER['REMOTE_ADDR']);
});
