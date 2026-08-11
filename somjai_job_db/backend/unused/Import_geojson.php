<?php
require_once 'config.php';

set_time_limit(0);
ini_set('memory_limit', '512M');

// ── Helper ───────────────────────────────────────────────────
function readGeoJSON(string $filename): array
{
    $path = __DIR__ . '/' . $filename;
    if (!file_exists($path)) {
        die("❌ ไม่พบไฟล์: $filename\n");
    }
    $json = json_decode(file_get_contents($path), true);
    if (!$json || !isset($json['features'])) {
        die("❌ รูปแบบไฟล์ผิดพลาด: $filename\n");
    }
    return $json['features'];
}

function importTable(PDO $pdo, string $label, array $features, string $sql, callable $mapper): void
{
    echo "📂 กำลังนำเข้า $label ...\n";

    $stmt      = $pdo->prepare($sql);
    $success   = 0;
    $skipped   = 0;
    $errors    = 0;

    foreach ($features as $feature) {
        $props = $feature['properties'] ?? [];
        $coords = $feature['geometry']['coordinates'] ?? [null, null];
        $params = $mapper($props, $coords);

        try {
            $stmt->execute($params);
            $success++;
        } catch (PDOException $e) {
            // duplicate key → skip silently
            if ($e->getCode() === '23505') {
                $skipped++;
            } else {
                echo "  ⚠️  Error: " . $e->getMessage() . "\n";
                $errors++;
            }
        }
    }

    echo "  ✅ สำเร็จ: $success  |  ข้าม (ซ้ำ): $skipped  |  Error: $errors\n\n";
}

// ════════════════════════════════════════════════════════════
//  1. จังหวัด
// ════════════════════════════════════════════════════════════
$features = readGeoJSON('รายชื่อจังหวัดทั่วประเทศ.geojson');

$sql = "
    INSERT INTO provinces (pro_code, pro_n_t, pro_n_e, longitude, latitude)
    VALUES (:pro_code, :pro_n_t, :pro_n_e, :longitude, :latitude)
    ON CONFLICT (pro_code) DO UPDATE SET
        pro_n_t   = EXCLUDED.pro_n_t,
        pro_n_e   = EXCLUDED.pro_n_e,
        longitude = EXCLUDED.longitude,
        latitude  = EXCLUDED.latitude
";

importTable($pdo, 'จังหวัด (provinces)', $features, $sql,
    function (array $p, array $c): array {
        return [
            ':pro_code'  => str_pad($p['pro_code'], 2, '0', STR_PAD_LEFT),
            ':pro_n_t'   => $p['pro_n_t']  ?? null,
            ':pro_n_e'   => $p['pro_n_e']  ?? null,
            ':longitude' => $c[0]          ?? null,
            ':latitude'  => $c[1]          ?? null,
        ];
    }
);

// ════════════════════════════════════════════════════════════
//  2. อำเภอ
// ════════════════════════════════════════════════════════════
$features = readGeoJSON('รายชื่ออำเภอทั่วประเทศ.geojson');

$sql = "
    INSERT INTO districts (dit_id, dit_n_t, pro_code, dit_code, longitude, latitude)
    VALUES (:dit_id, :dit_n_t, :pro_code, :dit_code, :longitude, :latitude)
    ON CONFLICT (dit_id) DO UPDATE SET
        dit_n_t   = EXCLUDED.dit_n_t,
        pro_code  = EXCLUDED.pro_code,
        dit_code  = EXCLUDED.dit_code,
        longitude = EXCLUDED.longitude,
        latitude  = EXCLUDED.latitude
";

importTable($pdo, 'อำเภอ (districts)', $features, $sql,
    function (array $p, array $c): array {
        return [
            ':dit_id'    => $p['dit_id']   ?? null,
            ':dit_n_t'   => $p['dit_n_t']  ?? null,
            ':pro_code'  => str_pad($p['pro_code'], 2, '0', STR_PAD_LEFT),
            ':dit_code'  => $p['dit_code'] ?? null,
            ':longitude' => $c[0]          ?? null,
            ':latitude'  => $c[1]          ?? null,
        ];
    }
);

// ════════════════════════════════════════════════════════════
//  3. ตำบล
// ════════════════════════════════════════════════════════════
$features = readGeoJSON('รายชื่อตำบลทั่วประเทศ.geojson');

$sql = "
    INSERT INTO subdistricts (tam_code, tam_n_t, pro_code, amp_code, longitude, latitude)
    VALUES (:tam_code, :tam_n_t, :pro_code, :amp_code, :longitude, :latitude)
    ON CONFLICT (tam_code) DO UPDATE SET
        tam_n_t   = EXCLUDED.tam_n_t,
        pro_code  = EXCLUDED.pro_code,
        amp_code  = EXCLUDED.amp_code,
        longitude = EXCLUDED.longitude,
        latitude  = EXCLUDED.latitude
";

importTable($pdo, 'ตำบล (subdistricts)', $features, $sql,
    function (array $p, array $c): array {
        return [
            ':tam_code'  => $p['tam_code'] ?? null,
            ':tam_n_t'   => $p['tam_n_t']  ?? null,
            ':pro_code'  => str_pad($p['pro_code'], 2, '0', STR_PAD_LEFT),
            ':amp_code'  => $p['amp_code'] ?? null,
            ':longitude' => $c[0]          ?? null,
            ':latitude'  => $c[1]          ?? null,
        ];
    }
);

echo "🎉 นำเข้าข้อมูลเสร็จสมบูรณ์\n";