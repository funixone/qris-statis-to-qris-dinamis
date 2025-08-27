<?php
require __DIR__ . '/vendor/autoload.php';

use Zxing\QrReader;

function parseEMV($qris) {
    $data = [];
    $i = 0;
    while ($i < strlen($qris)) {
        $tag = substr($qris, $i, 2);
        $len = intval(substr($qris, $i + 2, 2));
        $val = substr($qris, $i + 4, $len);
        $data[$tag] = $val;
        $i += 4 + $len;
    }
    return $data;
}

function generateDynamicQris($qrisString, $amount) {
    $parsed = parseEMV($qrisString);

    $parsed['54'] = $amount;

    $newQris = '';
    foreach ($parsed as $tag => $val) {
        $newQris .= $tag . sprintf("%02d", strlen($val)) . $val;
    }

    $newQrisNoCRC = preg_replace('/6304.{4}$/', '', $newQris);
    $crc = strtoupper(dechex(crc_ccitt(hex2bin(bin2hex($newQrisNoCRC . '6304')))));
    $crc = str_pad($crc, 4, '0', STR_PAD_LEFT);

    return $newQrisNoCRC . '6304' . $crc;
}

function crc_ccitt($data) {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($data); $i++) {
        $crc ^= (ord($data[$i]) << 8);
        for ($j = 0; $j < 8; $j++) {
            if ($crc & 0x8000) {
                $crc = ($crc << 1) ^ 0x1021;
            } else {
                $crc <<= 1;
            }
            $crc &= 0xFFFF;
        }
    }
    return $crc;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>QRIS Generator</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f9;
        margin: 0;
        padding: 20px;
    }
    .card {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        max-width: 600px;
        margin: 20px auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h2, h3 {
        margin-top: 0;
        color: #333;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    input, button, textarea {
        width: 100%;
        padding: 10px;
        margin: 6px 0 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }
    button {
        background: #007bff;
        color: #fff;
        cursor: pointer;
        border: none;
    }
    button:hover {
        background: #0056b3;
    }
    .qr-img {
        margin-top: 10px;
        text-align: center;
    }
</style>
</head>
<body>

<div class="card">
    <h2>Upload QRIS</h2>
    <form method="post" enctype="multipart/form-data">
        <label for="qris">Pilih file QRIS:</label>
        <input type="file" name="qris" id="qris" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['qris'])) {
    $fileTmp = $_FILES['qris']['tmp_name'];

    $qrcode = new QrReader($fileTmp);
    $qrisString = $qrcode->text();

    if (!$qrisString) {
        echo "<div class='card'><p style='color:red'>Gagal decode QRIS.</p></div>";
    } else {
        $parsed = parseEMV($qrisString);

        echo "<div class='card'>";
        echo "<h3>Data QRIS</h3>";
        echo "<p><b>Merchant Name:</b> " . ($parsed['59'] ?? '-') . "</p>";
        echo "<p><b>Merchant City:</b> " . ($parsed['60'] ?? '-') . "</p>";
        echo "<p><b>Merchant ID:</b> " . ($parsed['26'] ?? '-') . "</p>";

        echo '<form method="post">';
        echo '<input type="hidden" name="qris_string" value="' . htmlspecialchars($qrisString) . '">';
        echo '<label>Nominal:</label><input type="number" name="amount" required>';
        echo '<button type="submit" name="generate">Buat QRIS Dinamis</button>';
        echo '</form>';
        echo "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $qrisString = $_POST['qris_string'];
    $amount = $_POST['amount'];

    $dynamicQris = generateDynamicQris($qrisString, $amount);

    echo "<div class='card'>";
    echo "<h3>QRIS Dinamis</h3>";
    echo "<textarea rows='6' readonly>$dynamicQris</textarea><br>";
    echo "<div class='qr-img'><img src='https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($dynamicQris) . "&size=200x200'></div>";
    echo "</div>";
}
?>

</body>
</html>
