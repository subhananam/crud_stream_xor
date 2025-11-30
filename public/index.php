<?php

declare(strict_types=1);
header("Content-Type: application/json; charset=utf-8");

$dbHost = getenv("DB_HOST") ?: "127.0.0.1";
$dbName = getenv("DB_NAME") ?: "crud_stream_xor";
$dbUser = getenv("DB_USER") ?: "root";
$dbPass = getenv("DB_PASS") ?: "";

$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "DB connection failed",
        "message" => $e->getMessage(),
    ]);
    exit();
}

// --- XOR Stream functions ---
function keystream_from_key(string $key, int $len): string
{
    if ($key === "") {
        $key = "defaultkey";
    }
    $out = "";
    $klen = strlen($key);
    for ($i = 0; $i < $len; $i++) {
        $out .= $key[$i % $klen];
    }
    return $out;
}

function xor_stream_encrypt(string $plaintext, string $key): string
{
    $ks = keystream_from_key($key, strlen($plaintext));
    $cipher = $plaintext ^ $ks;
    return base64_encode($cipher);
}
function xor_stream_decrypt(string $b64cipher, string $key): string
{
    $cipher = base64_decode($b64cipher);
    $ks = keystream_from_key($key, strlen($cipher));
    $plain = $cipher ^ $ks;
    return $plain;
}
// Simple router
$method = $_SERVER["REQUEST_METHOD"];
$rawPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$scriptName = $_SERVER["SCRIPT_NAME"];

$path = $rawPath;
if (preg_match('#^/items(?:/(\d+))?$#', $path, $m)) {
    $id = isset($m[1]) ? intval($m[1]) : null;
    if ($method === "POST" && $id === null) {
        $data = json_decode(file_get_contents("php://input"), true) ?: [];
        $name = $data["name"] ?? null;
        $secret = $data["secret"] ?? "";
        $key = $data["key"] ?? "";
        if (!$name) {
            http_response_code(400);
            echo json_encode([
                "error" => "name missing",
            ]);
            exit();
        }
        $encResult = xor_stream_encrypt($secret, $key);
        $query = $pdo->prepare(
            "INSERT INTO items (name, secret_enc) VALUES (?, ?)",
        );
        $query->execute([$name, $encResult]);
        echo json_encode(["ok" => true, "id" => $pdo->lastInsertId()]);
        exit();
    }
    if ($method === "GET" && $id === null) {
        $key = $_GET["key"] ?? "";
        $rows = $pdo
            ->query(
                "SELECT id,name,secret_enc,created_at FROM items ORDER BY id DESC",
            )
            ->fetchAll();
        foreach ($rows as &$r) {
            $r["secret"] = $key
                ? xor_stream_decrypt($r["secret_enc"], $key)
                : null;
            unset($r["secret_enc"]);
        }
        echo json_encode($rows);
        exit();
    }
    if ($method === "GET" && $id !== null) {
        $key = $_GET["key"] ?? "";
        $query = $pdo->prepare(
            "SELECT id,name,secret_enc,created_at FROM items WHERE id = ?",
        );
        $query->execute([$id]);
        $result = $query->fetch();
        if (!$result) {
            http_response_code(404);
            echo json_encode([
                "error" => "not found",
            ]);
            exit();
        }
        $result["secret"] = $key ? xor_stream_decrypt($result["secret_enc"], $key) : null;
        unset($result["secret_enc"]);
        echo json_encode($result);
        exit();
    }
    if (($method === "PUT" || $method === "PATCH") && $id !== null) {
        $data = json_decode(file_get_contents("php://input"), true) ?: [];
        $name = $data["name"] ?? null;
        $secret = $data["secret"] ?? null;
        $key = $data["key"] ?? "";
        $fields = [];
        $params = [];
        if ($name !== null) {
            $fields[] = "name = ?";
            $params[] = $name;
        }
        if ($secret !== null) {
            $fields[] = "secret_enc = ?";
            $params[] = xor_stream_encrypt($secret, $key);
        }
        if (!$fields) {
            http_response_code(400);
            echo json_encode(["error" => "nothing to update"]);
            exit();
        }
        $params[] = $id;
        $sql = "UPDATE items SET " . implode(", ", $fields) . " WHERE id = ?";
        $pdo->prepare($sql)->execute($params);
        echo json_encode(["ok" => true]);
        exit();
    }
    if ($method === "DELETE" && $id !== null) {
        $pdo->prepare("DELETE FROM items WHERE id = ?")->execute([$id]);
        echo json_encode(["ok" => true]);
        exit();
    }
}
http_response_code(404);
echo json_encode(["error" => "route not found"]);
