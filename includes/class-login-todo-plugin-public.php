<?php

if (!defined('ABSPATH')) {
    exit;
}

class login_todo_Plugin_Public {
    private $version = '1.0.0';

    public function __construct() {
        // Initialize your class or setup hooks if needed
    }

    public function enqueue_assets() {
        wp_enqueue_style('custom-authentication-css', plugin_dir_url(__FILE__) . 'css/styles.css');
        wp_enqueue_script('custom-authentication-js', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), null, true);
    
        wp_localize_script('custom-authentication-js', 'myPluginData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'todoListNonce' => wp_create_nonce('todo-list-nonce')
        ));
    }

    public function display_registration_form() {
        ob_start();
        ?>
         <div class="container">
            <form action="register.php" method="POST" id="register-form">
                <h1>Sign up!</h1>
                <label for="user-name"> User Name</label><br>
                <input type="text" id="uname" name="uname" size="50" placeholder="Enter your name" title="User-Name"><br>
                <label for="register-email"> Email</label><br>
                <input type="email" id="register-email" name="register-email" size="50" placeholder="abc@gmail.com" title="Enter valid email" required><br>
                <label for="password"> Password</label><br>
                <input type="password" id="register-password" name="register-password" size="50" placeholder="password must be 8 characters" required><br>
                <label for="confirm-password"> Confirm password</label><br>
                <input type="password" id="register-confirm-password" name="password" size="50" placeholder="Re-enter password" required><br>
                <button type="submit" title="click to Register" onclick="index.php">Register</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public function register_user() {
        check_ajax_referer('todo-list-nonce', 'nonce');
    
        $uname = sanitize_text_field($_POST['uname']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
    
        if (empty($uname) || empty($email) || empty($password)) {
            wp_send_json_error(array('message' => 'All fields are required.'));
        }
    
        if (username_exists($uname) || email_exists($email)) {
            wp_send_json_error(array('message' => 'Username or email already exists.'));
        }
    
        $user_id = wp_create_user($uname, $password, $email);
    
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()));
        }
    
        wp_send_json_success();
    }
    
    public function display_login_form() {
        ob_start();
        ?>
        <div class="container">
            <form action="login.php" method="POST" id="login-form">
                <h1>Sign in!</h1>
                <label for="email">Email:</label><br>
                <input type="email" name="email" id="login-email" placeholder="Enter your email here!" size="50" required ><br>
                <label for="password">Password:</label><br>
                <input type="password" name="password" id="login-password" placeholder="Enter password" size="50" required><br>
                <button type="submit" title="click to submit">Submit</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public function login_user() {
        check_ajax_referer('todo-list-nonce', 'nonce');
    
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
    
        $user = wp_authenticate($email, $password);
    
        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => 'Invalid email or password.'));
        } else {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            wp_send_json_success();
        }
    }

    public function render_todo_list() {
        if (!is_user_logged_in()) {
            return '<p>You must be logged in to view the to-do list.</p>';
        }

        ob_start();
        ?>
        <div class="todo-list-container">
            <h2>My To-Do List</h2>
            <form id="todo-form">
                <input type="text" id="todo-item" placeholder="Add a new item" required>
                <button type="submit">Add</button>
            </form>
            <ul id="todo-list">
                <!-- JavaScript will populate this list -->
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle_add_todo_task() {
        check_ajax_referer('todo-list-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in to add a task.'));
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $task = sanitize_text_field($_POST['task']);
        
        if (empty($task)) {
            wp_send_json_error(array('message' => 'Task cannot be empty.'));
        }

        $table_name = $wpdb->prefix . 'to_do_list';

        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'task' => $task,
                'status' => 'pending'
            ),
            array(
                '%d',
                '%s',
                '%s'
            )
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to add task.'));
        }

        wp_send_json_success(array('message' => 'Task added successfully.'));
    }

    public function handle_fetch_todo_tasks() {
        check_ajax_referer('todo-list-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in to view tasks.'));
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'to_do_list';

        $tasks = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d",
                $user_id
            ),
            ARRAY_A
        );

        if ($tasks === false) {
            wp_send_json_error(array('message' => 'Failed to fetch tasks.'));
        }

        wp_send_json_success(array('tasks' => $tasks));
    }

    public function handle_update_todo_task() {
        check_ajax_referer('todo-list-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in to update a task.'));
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $task_id = sanitize_text_field($_POST['task_id']);
        $status = sanitize_text_field($_POST['status']);

        $valid_statuses = ['pending', 'completed'];
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error(array('message' => 'Invalid status.'));
        }

        $table_name = $wpdb->prefix . 'to_do_list';

        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array(
                'id' => $task_id,
                'user_id' => $user_id
            ),
            array('%s'),
            array('%d', '%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to update task.'));
        }

        wp_send_json_success(array('message' => 'Task updated successfully.'));
    }

    public function handle_delete_todo_task() {
        check_ajax_referer('todo-list-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in to delete a task.'));
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $task_id = sanitize_text_field($_POST['task_id']);

        $table_name = $wpdb->prefix . 'to_do_list';

        $result = $wpdb->delete(
            $table_name,
            array(
                'id' => $task_id,
                'user_id' => $user_id
            ),
            array('%d', '%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to delete task.'));
        }

        wp_send_json_success(array('message' => 'Task deleted successfully.'));
    }
}
