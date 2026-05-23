<?php
/*
 * insert.php
 * Endpoint yang dipanggil ESP32 via HTTP POST.
 * Simpan file ini di: htdocs/pzem/insert.php  (XAMPP)
 *                     atau public_html/pzem/insert.php  (hosting)
 *
 * Sesuaikan DB_HOST, DB_USER, DB_PASS jika perlu.
 */

header('Content-Type: application/json');

// ── Konfigurasi Database ──────────────────────────────────────────────────────
define('DB_HOST', 'sql104.byethost5.com');
define('DB_USER', 'b5_41985393');       // ganti sesuai user MySQL kamu
define('DB_PASS', 'AdsDwqd123');           // ganti sesuai password MySQL kamu
define('DB_NAME', 'b5_41985393_pzem_db');

// ── Hanya terima POST ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// ── Ambil & validasi data ─────────────────────────────────────────────────────
$tegangan = isset($_POST['tegangan']) ? floatval($_POST['tegangan']) : null;
$arus     = isset($_POST['arus'])     ? floatval($_POST['arus'])     : null;
$daya     = isset($_POST['daya'])     ? floatval($_POST['daya'])     : null;

if ($tegangan === null || $arus === null || $daya === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

// ── Simpan ke Database ────────────────────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare(
        "INSERT INTO sensor_data (tegangan, arus, daya) VALUES (:v, :i, :p)"
    );
    $stmt->execute([':v' => $tegangan, ':i' => $arus, ':p' => $daya]);

    echo json_encode([
        'status'   => 'ok',
        'inserted' => [
            'tegangan' => $tegangan,
            'arus'     => $arus,
            'daya'     => $daya,
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
