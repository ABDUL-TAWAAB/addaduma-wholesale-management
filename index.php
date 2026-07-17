<?php

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");

    $message = "";

    require_once 'config/database.php';
    $db = getDB();
if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $stmt = db_prepare($db, "SELECT * FROM staff WHERE email = ? LIMIT 1");
    db_execute($stmt, [$email]);
    $user = db_fetch_assoc($stmt);
    if ($user === false || $user === null) {
        echo "User does not exist.";
    } else {

        if ($password === $user["password_hash"]){
            $message = "Login successful.";
            
            // Start the session
            session_start();
            $_SESSION["staff_id"] = $user["id"];
            $_SESSION["staff_name"] = $user["name"];
            $_SESSION["staff_role"] = $user["role"];
            $_SESSION["email"] = $user["email"];
            header("Location: dashboard.php");
            exit;
        }else {
            $message = "Incorrect password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Addaduman Management System</title>
    <link rel="stylesheet" href="css/index.css">
    <!-- Basic Icons -->
<link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
<!-- Filled Icons -->
<link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
<!-- Brand Icons -->
<link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">

</head>
<body>
<div class="container">
    <!-- LEFT SIDE -->
    <div class="left">
        <h1>Addaduman</h1>
        <h3>Management System</h3>
        <p class="subtitle">
            Wholesale Inventory & Sales Management
        </p>
        <form method="POST">
            <label>Email</label>
            <input type="text" name="email" placeholder="Enter email" required>
            <label>Password</label>
            
            <input type="password" name="password" placeholder="Enter Password" required>
            <div class="remember">
                <div><input type="checkbox">Remember Me</div>
                <a href="b" onclick="alert('Please contact the administrator to reset your password.')">Forgot Password?</a>
            </div>
            <p class="showAuthentication"><?php echo $message; ?></p>
            <button type="submit"> Login </button>
        </form>
    </div>
    <!-- RIGHT SIDE -->
    <div class="right">
        <div class="overlay">
            <h1>Welcome to</h1>
            <h2>Addaduman Wholesale Management System</h2>
            <p>
                Manage products, customers, orders,
                inventory, payments and reports all
                from one dashboard.
            </p>
            <div class="features">
                <div class="box">
                    <i class="bx bx-warehouse"></i>
                    <h3>Inventory</h3>
                    <p>Track Stock</p>
                </div>

                <div class="box">
                    <i class="bx bx-bank"></i>
                    <h3>Sales</h3>
                    <p>Monitor Revenue</p>
                </div>

                <div class="box">
                    <i class="bx bx-community"></i>
                    <h3>Customers</h3>
                    <p>Manage Clients</p>
                </div>

            </div>
            <div class="about">
                <h3>About Addaduman</h3>
                <p>
                     Addaduman Enterprise has been supplying bottled water, soft drinks, 
                     alcoholic drinks, and beverages since 2018. This management system simplifies daily
                     business operations, inventory control, staff management, customer records and financial 
                     reporting.
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>