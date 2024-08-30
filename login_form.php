<!DOCTYPE html>
<html>
<head>
    <title>PR database</title>
    <?php require_once('header.php'); ?>
</head>
<?php require_once('login_connection.php'); ?>
<body>
    <div class="container mt-3 mb-3">
        <div id="login-form" style="display: block;">
            <form method="post" action="searchbar.php">
                <div class="row justify-content-center">
                    <div class="col-4">
                        <?php if(isset($_SESSION['flag']) && $_SESSION['flag']) { ?>
                            <p>Incorrect email/password combination.</p>
                            <?php unset($_SESSION['flag']); ?>
                        <?php } ?>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" class="form-control" id="password" placeholder="Enter password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%">Log in</button>
                        <br><br>
                        <p>Don't have an account? <a href="signup_form.php" id="show-signup">Sign Up</a> now!</p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>