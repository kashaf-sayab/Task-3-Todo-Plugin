<?php
/*
Plugin Name: login-todo-plugin
Plugin URI:  https://example.com/
Description: A plugin to handle user registration, login, and to-do list management.
Version:     1.0
Author:      Kashaf Sayab
Author URI:  https://example.com/
License:     GPL2
*/

if (!defined('ABSPATH')) {
    exit;
}


include_once plugin_dir_path(__FILE__) . 'includes/class-login-todo-plugin.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/table.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/wp-cli-todo.php';
}


function run_login_todo_plugin() {
    $plugin = new login_todo_plugin();
    $plugin->run();
}
run_login_todo_plugin();

