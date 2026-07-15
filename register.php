<?php
require_once 'config/database.php';
$db = getDB();
if(isset($_POST['name'], $_POST['phone'], $_POST['email'], $_POST['role'], $_POST['password'])){
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    if(empty($name) || empty($phone) || empty($email) || empty($role) || empty($password)){
        die("All fields are required.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO staff (name, phone, email, role, password_hash) VALUES (?, ?, ?, ?, ?)";
    $stmt = db_prepare($db, $sql);
    db_execute($stmt, [$name, $phone, $email, $role, $hashedPassword]);
    echo "User registered successfully.";
}

$direction = "./index.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration portal</title>
  <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="form-container">
    <div class="form-header">
        <h2>Staff Registration</h2>
        <p>Create a new staff account for Addaduman Management System</p>
    </div>
    <form action="register.php" method="post">
        <div class="input-group">
            <label for="name">Name *</label>
            <input
                type="text" name="name" placeholder="Enter staff name" required>
        </div>
        <div class="input-group">
            <label for="phone_number">Phone Number</label>
            <input type="tel" name="phone" placeholder="+233 XXX XXX XXX" required>
        </div>
        <div class="input-group">
            <label for="role">Role</label>
            <select name="role" id="role" required>
                <option value="">Select Role</option>
                <option value="Admin">Admin</option>
                <option value="Manager">Manager</option>
                <option value="Sales">Sales</option>
            </select>
        </div>
        <div class="input-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" placeholder="example@gmail.com" required>
        </div>
        <div class="input-group">
            <label for="password">Password</label>
            <div class="password-box">
                <input type="password" name="password" id="password" placeholder="Enter password"required>
                <span class="toggle-password" onclick="togglePassword()">👁
                </span>
            </div>
        </div>
        <button type="submit">Register Staff</button>
    </form>
    <a href="<?php echo $direction ?>">Already have an Account? <span>Login</span></a>
</div>
</body>
</html>