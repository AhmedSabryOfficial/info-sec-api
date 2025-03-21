<?php
// product.php
header("Content-Type: application/json");
require_once 'config.php';
require_once 'jwt_helper.php';

// استخراج التوكن من الهيدر
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    echo json_encode(["error" => "Authorization header missing"]);
    exit;
}
list($bearer, $token) = explode(" ", $headers['Authorization'], 2);
$userData = validateJWT($token);
if (!$userData) {
    echo json_encode(["error" => "Invalid or expired token"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
// إذا كان هناك معامل pid في URL
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

// قراءة بيانات JSON (لطلبات POST وPUT)
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if ($method == "GET") {
    if ($pid > 0) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE pid = ?");
        $stmt->execute([$pid]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) {
            echo json_encode($product);
        } else {
            echo json_encode(["error" => "Product not found"]);
        }
    } else {
        // جلب كل المنتجات
        $stmt = $pdo->query("SELECT * FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($products);
    }
}
else if ($method == "POST") {
    // إضافة منتج جديد
    $pname = $data['pname'] ?? '';
    $description = $data['description'] ?? '';
    $price = $data['price'] ?? 0;
    $stock = $data['stock'] ?? 0;
    if(empty($pname) || $price == 0){
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO products (pname, description, price, stock) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$pname, $description, $price, $stock])) {
        echo json_encode(["message" => "Product added successfully"]);
    } else {
        // إظهار معلومات الخطأ من PDO
        $errorInfo = $stmt->errorInfo();
        echo json_encode(["error" => "Failed to add product", "details" => $errorInfo]);
    }
}

else if ($method == "PUT") {
    // تحديث منتج موجود
    if ($pid <= 0) {
        echo json_encode(["error" => "Product ID is required for update"]);
        exit;
    }
    $pname = $data['pname'] ?? '';
    $description = $data['description'] ?? '';
    $price = $data['price'] ?? 0;
    $stock = $data['stock'] ?? 0;
    $stmt = $pdo->prepare("UPDATE products SET pname = ?, description = ?, price = ?, stock = ? WHERE pid = ?");
    if ($stmt->execute([$pname, $description, $price, $stock, $pid])) {
        echo json_encode(["message" => "Product updated successfully"]);
    } else {
        echo json_encode(["error" => "Failed to update product"]);
    }
}
else if ($method == "DELETE") {
    if ($pid <= 0) {
        echo json_encode(["error" => "Product ID is required for deletion"]);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM products WHERE pid = ?");
    if ($stmt->execute([$pid])) {
        echo json_encode(["message" => "Product deleted successfully"]);
    } else {
        echo json_encode(["error" => "Failed to delete product"]);
    }
}
else {
    echo json_encode(["error" => "Method not allowed"]);
}
?>
