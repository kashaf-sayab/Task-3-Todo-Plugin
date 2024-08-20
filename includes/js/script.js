document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            if (email === '' || password === '') {
                alert('Please fill in all fields.');
                return;
            }
            else{
            alert('you successfuly login.');
            loginForm.submit();
            }
        });
    }
    jQuery(document).ready(function($) {
        $('#login-form').on('submit', function(e) {
            e.preventDefault();
    
            var email = $('#login-email').val();
            var password = $('#login-password').val();
    
            $.ajax({
                url: myPluginData.ajax_url,
                type: 'POST',
                data: {
                    action: 'login_user',
                    email: email,
                    password: password,
                    nonce: myPluginData.todoListNonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = 'index.php/to-do-list/';
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
    

    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const email = document.getElementById('register-email').value;
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-confirm-password').value;
            function checkEmailExists(email, callback) {
                // Basic email validation
                if (email.indexOf('@') <= 0 || email.lastIndexOf('.') <= email.indexOf('@') || email.lastIndexOf('.') >= email.length - 1) {
                    alert('Please enter a valid email.');
                    return;
                }
            
                // Simulate an asynchronous callback (you can replace this with actual logic if needed)
                setTimeout(function() {
                    // Simulate a response object with a flag indicating email existence
                    const response = { exists: false }; // Change to `true` to simulate an existing email
                    callback(response);
                }, 500);
            }
            
            checkEmailExists(email, function(response) {
                if (response.exists) {
                    alert('Email is already in use.');
                    return;
                }
                alert('Email is valid and not in use.');
                
                if (password.length < 8) {
                    alert('Password must be at least 8 characters long.');
                    return;
                }

                let hasUppercase = false;
                let hasSpecialChar = false;
                let hasNumber = false;

                for (let i = 0; i < password.length; i++) {
                    if (password[i] >= 'A' && password[i] <= 'Z') {
                        hasUppercase = true;
                    }
                    if (password[i] >= '0' && password[i] <= '9') {
                        hasNumber = true;
                    }
                    if (['@', '$', '!', '%', '*', '?', '&'].includes(password[i])) {
                        hasSpecialChar = true;
                    }
                }

                if (!hasUppercase || !hasSpecialChar || !hasNumber) {
                    alert('Password must include at least one uppercase letter, one number, and one special character.');
                    return;
                }
                if (password !== confirmPassword) {
                    alert('Passwords do not match.');
                    return;
                }

                alert('Registration successful!');
                registerForm.submit();
            });
        });
    }
    jQuery(document).ready(function($) {
        $('#register-form').on('submit', function(e) {
            e.preventDefault();
    
            var uname = $('#uname').val();
            var email = $('#register-email').val();
            var password = $('#register-password').val();
            var confirmPassword = $('#register-confirm-password').val();
    
            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                return;
            }
    
            $.ajax({
                url: myPluginData.ajax_url,
                type: 'POST',
                data: {
                    action: 'register_user',
                    uname: uname,
                    email: email,
                    password: password,
                    nonce: myPluginData.todoListNonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = 'index.php/login/';
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
    

 jQuery(document).ready(function($) {
    function fetchTasks() {
        $.ajax({
            url: myPluginData.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_todo_tasks',
                nonce: myPluginData.todoListNonce
            },
            success: function(response) {
                if (response.success) {
                    $('#todo-list').empty();
                    response.data.tasks.forEach(function(task) {
                        $('#todo-list').append(
                            `<li class="todo-item" data-id="${task.id}">
                                <input type="checkbox" class="todo-item__checkbox" ${task.status === 'completed' ? 'checked' : ''}>
                                <span class="todo-item__text">${task.task}</span>
                                <button class="todo-item__delete">Delete</button>
                                <select class="todo-item__status">
                                    <option value="pending" ${task.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="completed" ${task.status === 'completed' ? 'selected' : ''}>Completed</option>
                                </select>
                            </li>`
                        );
                    });
                }
            }
        });
    }

    // Fetch tasks on page load
    fetchTasks();

    // Add new task
    $('#todo-form').on('submit', function(e) {
        e.preventDefault();
        var task = $('#todo-item').val();

        $.ajax({
            url: myPluginData.ajax_url,
            type: 'POST',
            data: {
                action: 'add_todo_task',
                nonce: myPluginData.todoListNonce,
                task: task
            },
            success: function(response) {
                if (response.success) {
                    $('#todo-item').val('');
                    fetchTasks();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Delete task
    $('#todo-list').on('click', '.todo-item__delete', function() {
        var taskId = $(this).closest('.todo-item').data('id');

        $.ajax({
            url: myPluginData.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_todo_task',
                nonce: myPluginData.todoListNonce,
                task_id: taskId
            },
            success: function(response) {
                if (response.success) {
                    fetchTasks();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Update task status
    $('#todo-list').on('change', '.todo-item__status', function() {
        var taskId = $(this).closest('.todo-item').data('id');
        var status = $(this).val();

        $.ajax({
            url: myPluginData.ajax_url,
            type: 'POST',
            data: {
                action: 'update_todo_task',
                nonce: myPluginData.todoListNonce,
                task_id: taskId,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    fetchTasks();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Toggle task completion
    $('#todo-list').on('change', '.todo-item__checkbox', function() {
        var taskId = $(this).closest('.todo-item').data('id');
        var status = $(this).is(':checked') ? 'completed' : 'pending';

        $.ajax({
            url: myPluginData.ajax_url,
            type: 'POST',
            data: {
                action: 'update_todo_task',
                nonce: myPluginData.todoListNonce,
                task_id: taskId,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    fetchTasks();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});

});