<!DOCTYPE html>
<html>
<head>
    <title>PR database</title>
    <?php require_once('header.php'); ?>
</head>
<?php require_once('signup_connection.php');
global $error;?>
<body>
    <div class="container mt-3 mb-3">
        <div id="signup-form" style="display: block;">
            <form method="post" action="signup_connection.php">
                <div class="row justify-content-center">
                    <div class="col-4">
                        <?php if(isset($_SESSION['flag']) && $_SESSION['flag']) { ?>
                            <p>Email already exists. Please enter a different email.</p>
                            <?php unset($_SESSION['flag']); ?>
                        <?php } ?>
                        <div class="form-group">
                            <label>New Email:</label>
                            <input type="email" class="form-control" id="new-email" placeholder="Enter email" name="new-email" required>
                        </div>
                        <div class="form-group">
                            <label>New Password:</label>
                            <input type="password" class="form-control" id="new-password" placeholder="Enter password" name="new-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" id="signup" style="width: 100%">Sign up</button>
                        <br><br>
                        <p>Already have an account? <a href="login_form.php" id="show-login">Log in</a> now!</p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>