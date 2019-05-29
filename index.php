<?php
/*
Plugin Name: Geetest
Plugin URI: https://mousin.cn/plugins/wordpress-plugin-geetest.html
Description: 帮助你的 WordPress 抵御恶性骚扰。
Version: 1.0.1
Author: Mousin
Author URI: https://mousin.cn
License: GPLv2 or later
*/

if (!function_exists('add_action')) {
    exit;
}

define('GEETEST_VERSION', '1.0.1');
define('GEETEST_PATH', dirname(__FILE__));
define('GEETEST_URL', plugins_url('', __FILE__));
define('GEETEST_BASE', __FILE__);

require_once GEETEST_PATH . '/core/lib/class.geetestlib.php';
require_once GEETEST_PATH . '/core/GeeTest.php';

GeeTest::instance('GeetestLib');
register_activation_hook(GEETEST_BASE, array('GeeTest', 'geetestActivation'));
register_uninstall_hook(GEETEST_BASE, array('GeeTest', 'geetestUninstall'));