<?php
$dbHost = '127.0.0.1';
$dbName = 'user_registration_db';
$dbUser = 'root';
$dbPass = '';
$charset = 'utf8mb4';
$ports = [3306, 3307];

$pdo = null;
foreach ($ports as $port) {
    try {
        $dsn = "mysql:host=$dbHost;port=$port;dbname=$dbName;charset=$charset";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        break;
    } catch (Exception $e) {}
}

if (!$pdo) {
    die("Could not connect to database\n");
}

$adminEmail = 'admin@example.com';
$adminPass = 'Admin@123';
$hash = password_hash($adminPass, PASSWORD_BCRYPT);

// Check if admin already exists
$stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $adminEmail]);
$existing = $stmt->fetch();

if ($existing) {
    $update = $pdo->prepare("UPDATE users SET password_hash = :hash, role = 'Admin', full_name = 'System Administrator' WHERE email = :email");
    $update->execute([':hash' => $hash, ':email' => $adminEmail]);
    echo "SUCCESS: Admin user ($adminEmail) updated with password '$adminPass' and role 'Admin'.\n";
} else {
    $insert = $pdo->prepare("
        INSERT INTO users (full_name, email, mobile, password_hash, course, year, gender, role)
        VALUES ('System Administrator', :email, '9999999999', :hash, 'Management', 'Staff', 'Other', 'Admin')
    ");
    $insert->execute([':email' => $adminEmail, ':hash' => $hash]);
    echo "SUCCESS: Admin user ($adminEmail) created with password '$adminPass' and role 'Admin'.\n";
}

$all = $pdo->query("SELECT id, full_name, email, role FROM users")->fetchAll();
echo "CURRENT USERS IN DATABASE:\n";
foreach ($all as $u) {
    echo "ID: {$u['id']} | Name: {$u['full_name']} | Email: {$u['email']} | Role: {$u['role']}\n";
}
