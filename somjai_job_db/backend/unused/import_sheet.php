<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Fatal: ' . $err['message'], 'line' => $err['line']]);
    } else {
        ob_end_flush();
    }
});

require_once './../config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── Read JSON body ───────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);
if (!$body || empty($body['rows'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No rows provided']);
    exit;
}

// ── Helpers ──────────────────────────────────────────────────
function s($v): ?string  { $v = trim($v ?? ''); return $v === '' ? null : $v; }
function n($v): ?int     { $v = trim($v ?? ''); return $v === '' ? null : (int)$v; }
function f($v): ?float   { $v = trim($v ?? ''); return $v === '' ? null : (float)$v; }
function b($v, $yes = 'มี'): string { return (trim($v ?? '') === $yes) ? 'true' : 'false'; }
function dt($v): ?string {
    $v = trim($v ?? '');
    if ($v === '') return null;
    // Try parse various date formats
    $ts = strtotime($v);
    if ($ts === false) return null;
    return date('Y-m-d', $ts);
}

// ── Prepare statements ───────────────────────────────────────
$appSql = "
    INSERT INTO applications (
        position1, position2, expected_salary,
        full_name, nickname, phone, birth_date, age, height_cm, weight_kg, birth_province,
        school_name, education_level, major,
        marital_status, children_count,
        has_acquaintance, acquaintance_name, acquaintance_relation,
        referrer_name, referrer_relation,
        facebook, line_id, has_own_business, business_type,
        cur_house_no, cur_village, cur_road, cur_soi,
        cur_subdistrict_id, cur_district_id, cur_province_id, cur_postal,
        reg_house_no, reg_village, reg_road, reg_soi,
        reg_subdistrict_id, reg_district_id, reg_province_id, reg_postal,
        emergency_name, emergency_relation, emergency_phone,
        eng_speaking, eng_writing,
        ms_word, ms_excel, ms_ppt,
        can_drive_car, car_gear, has_car_license, has_own_car,
        can_drive_moto, has_moto_license, has_own_moto,
        vision, is_pregnant, pregnancy_weeks,
        has_chronic_disease, chronic_disease,
        legal_status, legal_case_type, drug_status
    ) VALUES (
        :position1, :position2, :expected_salary,
        :full_name, :nickname, :phone, :birth_date, :age, :height_cm, :weight_kg, :birth_province,
        :school_name, :education_level, :major,
        :marital_status, :children_count,
        :has_acquaintance, :acquaintance_name, :acquaintance_relation,
        :referrer_name, :referrer_relation,
        :facebook, :line_id, :has_own_business, :business_type,
        :cur_house_no, :cur_village, :cur_road, :cur_soi,
        NULL, NULL, :cur_province_id, :cur_postal,
        :reg_house_no, :reg_village, :reg_road, :reg_soi,
        NULL, NULL, :reg_province_id, :reg_postal,
        :emergency_name, :emergency_relation, :emergency_phone,
        :eng_speaking, :eng_writing,
        :ms_word, :ms_excel, :ms_ppt,
        :can_drive_car, :car_gear, :has_car_license, :has_own_car,
        :can_drive_moto, :has_moto_license, :has_own_moto,
        :vision, :is_pregnant, :pregnancy_weeks,
        :has_chronic_disease, :chronic_disease,
        :legal_status, :legal_case_type, :drug_status
    ) RETURNING id
";

$whSql = "
    INSERT INTO work_histories
        (application_id, seq, company_name, district, province,
         biz_type, position, start_date, end_date,
         duty1, duty2, duty3, resign_reason)
    VALUES
        (:app_id, :seq, :company, :district, :province,
         :biz_type, :position, :start_date, :end_date,
         :duty1, :duty2, :duty3, :reason)
";

// ── Look up province pro_code by name ────────────────────────
$provCache = [];
function getProCode(PDO $pdo, ?string $name): ?string {
    global $provCache;
    if (!$name) return null;
    $name = trim($name);
    if (isset($provCache[$name])) return $provCache[$name];
    $stmt = $pdo->prepare("SELECT pro_code FROM provinces WHERE pro_n_t = :n LIMIT 1");
    $stmt->execute([':n' => $name]);
    $row = $stmt->fetch();
    $provCache[$name] = $row ? $row['pro_code'] : null;
    return $provCache[$name];
}

// ── Process rows ─────────────────────────────────────────────
$appStmt = $pdo->prepare($appSql);
$whStmt  = $pdo->prepare($whSql);

$inserted = 0;
$skipped  = 0;
$errors   = [];

foreach ($body['rows'] as $idx => $r) {
    try {
        $pdo->beginTransaction();

        // Resolve province codes from name
        $curProCode = getProCode($pdo, $r['cur_province'] ?? null);
        $regProCode = getProCode($pdo, $r['reg_province'] ?? null);

        $appStmt->execute([
            ':position1'            => s($r['position1']),
            ':position2'            => s($r['position2'] ?? null),
            ':expected_salary'      => f($r['expectedSalary'] ?? 0) ?? 0,
            ':full_name'            => s($r['fullName']),
            ':nickname'             => s($r['nickname']),
            ':phone'                => s($r['phone']),
            ':birth_date'           => dt($r['birthDate'] ?? null),
            ':age'                  => n($r['age'] ?? null),
            ':height_cm'            => n($r['height'] ?? null),
            ':weight_kg'            => n($r['weight'] ?? null),
            ':birth_province'       => s($r['birthProvince'] ?? null),
            ':school_name'          => s($r['schoolName'] ?? null),
            ':education_level'      => s($r['education'] ?? null),
            ':major'                => s($r['major'] ?? null),
            ':marital_status'       => s($r['maritalStatus']),
            ':children_count'       => n($r['children'] ?? '0') ?? 0,
            ':has_acquaintance'     => b($r['hasAcquaintance'] ?? '', 'มี'),
            ':acquaintance_name'    => s($r['acquaintanceName'] ?? null),
            ':acquaintance_relation'=> s($r['acquaintanceRelation'] ?? null),
            ':referrer_name'        => s($r['referrerName'] ?? null),
            ':referrer_relation'    => s($r['referrerRelation'] ?? null),
            ':facebook'             => s($r['facebook'] ?? null),
            ':line_id'              => s($r['lineId'] ?? null),
            ':has_own_business'     => b($r['hasOwnBusiness'] ?? '', 'มี'),
            ':business_type'        => s($r['businessType'] ?? null),
            ':cur_house_no'         => s($r['cur_houseNo']) ?? '-',
            ':cur_village'          => s($r['cur_village'] ?? null),
            ':cur_road'             => s($r['cur_road'] ?? null),
            ':cur_soi'              => s($r['cur_soi'] ?? null),
            ':cur_province_id'      => $curProCode,
            ':cur_postal'           => s($r['cur_postal'] ?? null),
            ':reg_house_no'         => s($r['reg_houseNo']) ?? '-',
            ':reg_village'          => s($r['reg_village'] ?? null),
            ':reg_road'             => s($r['reg_road'] ?? null),
            ':reg_soi'              => s($r['reg_soi'] ?? null),
            ':reg_province_id'      => $regProCode,
            ':reg_postal'           => s($r['reg_postal'] ?? null),
            ':emergency_name'       => s($r['emergencyName']) ?? '-',
            ':emergency_relation'   => s($r['emergencyRelation']) ?? '-',
            ':emergency_phone'      => s($r['emergencyPhone']) ?? '-',
            ':eng_speaking'         => n($r['engSpeak'] ?? null),
            ':eng_writing'          => n($r['engWrite'] ?? null),
            ':ms_word'              => n($r['msWord'] ?? null),
            ':ms_excel'             => n($r['msExcel'] ?? null),
            ':ms_ppt'               => n($r['msPpt'] ?? null),
            ':can_drive_car'        => s($r['canDriveCar'] ?? null),
            ':car_gear'             => s($r['carGear'] ?? null),
            ':has_car_license'      => b($r['hasCarLicense'] ?? '', 'มี'),
            ':has_own_car'          => b($r['hasOwnCar'] ?? '', 'มี'),
            ':can_drive_moto'       => s($r['canDriveMoto'] ?? null),
            ':has_moto_license'     => b($r['hasMotoLicense'] ?? '', 'มี'),
            ':has_own_moto'         => b($r['hasOwnMoto'] ?? '', 'มี'),
            ':vision'               => s($r['vision'] ?? null),
            ':is_pregnant'          => b($r['isPregnant'] ?? '', 'ใช่'),
            ':pregnancy_weeks'      => n($r['pregnancyWeeks'] ?? null),
            ':has_chronic_disease'  => b($r['hasDisease'] ?? '', 'มี'),
            ':chronic_disease'      => s($r['chronicDisease'] ?? null),
            ':legal_status'         => s($r['legalStatus'] ?? 'ไม่เคย') ?? 'ไม่เคย',
            ':legal_case_type'      => s($r['legalCaseType'] ?? null),
            ':drug_status'          => s($r['drugStatus'] ?? 'ไม่เคย') ?? 'ไม่เคย',
        ]);

        $row   = $appStmt->fetch();
        $appId = $row['id'];

        // Work histories
        foreach (($r['workHistories'] ?? []) as $seq => $job) {
            if (!($job['company'] || $job['position'])) continue;
            $whStmt->execute([
                ':app_id'     => $appId,
                ':seq'        => $seq + 1,
                ':company'    => s($job['company'] ?? null),
                ':district'   => s($job['district'] ?? null),
                ':province'   => s($job['province'] ?? null),
                ':biz_type'   => s($job['bizType'] ?? null),
                ':position'   => s($job['position'] ?? null),
                ':start_date' => dt($job['startDate'] ?? null),
                ':end_date'   => dt($job['endDate'] ?? null),
                ':duty1'      => s($job['duty1'] ?? null),
                ':duty2'      => s($job['duty2'] ?? null),
                ':duty3'      => s($job['duty3'] ?? null),
                ':reason'     => s($job['reason'] ?? null),
            ]);
        }

        $pdo->commit();
        $inserted++;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errors[] = "Row $idx ({$r['fullName']}): " . $e->getMessage();
        $skipped++;
    }
}

ob_end_clean();
echo json_encode([
    'success'  => true,
    'inserted' => $inserted,
    'skipped'  => $skipped,
    'errors'   => $errors,
], JSON_UNESCAPED_UNICODE);