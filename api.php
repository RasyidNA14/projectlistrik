<?php
/*
 * api.php
 * Dipanggil HTML setiap beberapa detik untuk mendapatkan data terbaru.
 * Simpan di folder yang sama dengan insert.php.
 *
 * GET /pzem/api.php?limit=30
 *   → JSON array 30 baris terbaru, urutan ASC (lama → baru) untuk grafik
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // izinkan akses dari browser

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '123');
define('DB_NAME', 'pzem_db');

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 30;
$limit = max(1, min($limit, 200)); // batasi 1–200

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Ambil N baris terbaru, lalu urutkan ASC agar grafik runtut waktu
    $stmt = $pdo->prepare("
        SELECT tegangan, arus, daya,
               DATE_FORMAT(waktu, '%H:%i:%s') AS waktu
        FROM (
            SELECT * FROM sensor_data
            ORDER BY id DESC
            LIMIT :lim
        ) sub
        ORDER BY id ASC
    ");
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'ok',
        'count'  => count($rows),
        'data'   => $rows
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
