<?php
// Run this once after importing db.sql to create an admin user.
require 'config.php';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? 'Admin';
    if(!$email || !$password){ $err = 'Provide email and password'; }
    else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?, 'admin')");
        $stmt->bind_param('sss', $name, $email, $hash);
        if($stmt->execute()){
            $msg = 'Admin created. You can delete this file.';
        } else {
            $err = 'DB Error: ' . $stmt->error;
        }
    }
}
?>
<!doctype html><html><head><meta charset='utf-8'><title>Setup Admin</title></head><body>
<h2>Create Admin</h2>
<?php if(!empty($err)) echo '<div style="color:red">'.htmlspecialchars($err).'</div>'; ?>
<?php if(!empty($msg)) echo '<div style="color:green">'.htmlspecialchars($msg).'</div>'; ?>
<form method='post'>
Email: <input name='email' required><br>
Name: <input name='name' value='Admin'><br>
Password: <input name='password' required><br>
<button>Create Admin</button>
</form>
</body></html>
