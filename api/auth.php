<?php
header("Content-Type: application/json");
require_once 'config.php';
require_once 'jwt_helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = json_decode(file_get_contents("php://input"), true);

if ($method == "POST") {
    $action = $_GET['action'] ?? '';

    if ($action == "signup") {
        $name = $request['name'] ?? '';
        $username = $request['username'] ?? '';
        $password = $request['password'] ?? '';

        if(empty($name) || empty($username) || empty($password)) {
            echo json_encode(["error" => "Missing fields"]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(["error" => "Username already exists"]);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, username, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $username, $hashedPassword])) {
            echo json_encode(["message" => "User registered successfully"]);
        } else {
            echo json_encode(["error" => "Registration failed"]);
        }
    }
    else if ($action == "login") {
        $username = $request['username'] ?? '';
        $password = $request['password'] ?? '';

        if(empty($username) || empty($password)) {
            echo json_encode(["error" => "Missing username or password"]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $token = generateJWT(["id" => $user['id'], "username" => $user['username']]);
            echo json_encode(["token" => $token]);
        } else {
            echo json_encode(["error" => "Invalid credentials"]);
        }
    } else {
        echo json_encode(["error" => "Invalid action"]);
    }
} else {
    echo json_encode(["error" => "Only POST method is allowed"]);
}
?>
