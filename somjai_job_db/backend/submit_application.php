<?php
ob_start();
// ── Catch ALL PHP errors and return as JSON ──────────────────
ini_set('display_errors', 0);
error_reporting(E_ALL);
set_error_handler(function($severity, $message, $file, $line) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => "PHP Error: $message",
        'file'    => $file,
        'line'    => $line,
    ]);
    exit;
});
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error'   => "Fatal: " . $err['message'],
            'file'    => $err['file'],
            'line'    => $err['line'],
        ]);
    } else {
        ob_end_flush();
    }
});

require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ── Only allow POST ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── Helper: sanitize string ──────────────────────────────────
function str(?string $val): ?string {
    $v = trim($val ?? '');
    return $v === '' ? null : $v;
}

function num(?string $val): ?int {
    $v = trim($val ?? '');
    return $v === '' ? null : (int)$v;
}

function toBool(?string $val, string $yes = 'มี'): string {
    return (trim($val ?? '') === $yes) ? 'true' : 'false';
}

function padCode(?string $val): ?string {
    $v = trim($val ?? '');
    return $v === '' ? null : str_pad($v, 2, '0', STR_PAD_LEFT);
}

// ── Handle profile image upload ──────────────────────────────
$profileImagePath = null;
if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/profiles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext      = strtolower(pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ประเภทไฟล์รูปภาพไม่รองรับ']);
        exit;
    }

    $filename         = uniqid('profile_', true) . '.' . $ext;
    $profileImagePath = 'uploads/profiles/' . $filename;

    if (!move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadDir . $filename)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'อัปโหลดรูปภาพไม่สำเร็จ']);
        exit;
    }
}

// ── Validate required fields ─────────────────────────────────
$required = [
    'position1'       => 'ตำแหน่งที่สมัคร',
    'expectedSalary'  => 'เงินเดือนที่ต้องการ',
    'fullName'        => 'ชื่อ-นามสกุล',
    'nickname'        => 'ชื่อเล่น',
    'phone'           => 'เบอร์โทรศัพท์',
    'birthDate'       => 'วัน เดือน ปีเกิด',
    'maritalStatus'   => 'สถานะภาพ',
    'cur_houseNo'     => 'บ้านเลขที่ (ที่อยู่ปัจจุบัน)',
    'cur_province_id' => 'จังหวัด (ที่อยู่ปัจจุบัน)',
    'cur_district_id' => 'อำเภอ (ที่อยู่ปัจจุบัน)',
    'reg_houseNo'     => 'บ้านเลขที่ (ทะเบียนบ้าน)',
    'reg_province_id' => 'จังหวัด (ทะเบียนบ้าน)',
    'reg_district_id' => 'อำเภอ (ทะเบียนบ้าน)',
    'emergencyName'   => 'ชื่อผู้ติดต่อฉุกเฉิน',
    'emergencyRelation' => 'ความสัมพันธ์ผู้ติดต่อฉุกเฉิน',
    'emergencyPhone'  => 'เบอร์โทรผู้ติดต่อฉุกเฉิน',
];

$errors = [];
foreach ($required as $field => $label) {
    if (empty(trim($_POST[$field] ?? ''))) {
        $errors[] = "กรุณากรอก: $label";
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// ── Begin transaction ────────────────────────────────────────
try {
    $pdo->beginTransaction();

    // ── INSERT applications ──────────────────────────────────
    $sql = "
        INSERT INTO applications (
            -- Section 1
            position1, position2, expected_salary, profile_image_path,
            -- Section 2
            full_name, nickname, phone, birth_date, age,
            height_cm, weight_kg, birth_province,
            -- Section 3
            school_name, education_level, major,
            -- Section 4
            marital_status, children_count,
            has_acquaintance, acquaintance_name, acquaintance_relation,
            -- Section 5
            referrer_name, referrer_relation,
            -- Section 6
            facebook, line_id, has_own_business, business_type,
            -- Section 7
            cur_house_no, cur_village, cur_road, cur_soi,
            cur_subdistrict_id, cur_district_id, cur_province_id, cur_postal,
            -- Section 8
            reg_house_no, reg_village, reg_road, reg_soi,
            reg_subdistrict_id, reg_district_id, reg_province_id, reg_postal,
            -- Section 9
            emergency_name, emergency_relation, emergency_phone,
            -- Section 10
            eng_speaking, eng_writing,
            -- Section 11
            ms_word, ms_excel, ms_ppt,
            -- Section 12
            can_drive_car, car_gear, has_car_license, has_own_car,
            can_drive_moto, has_moto_license, has_own_moto,
            -- Section 13
            vision, is_pregnant, pregnancy_weeks,
            has_chronic_disease, chronic_disease,
            legal_status, legal_case_type, drug_status
        ) VALUES (
            :position1, :position2, :expected_salary, :profile_image_path,
            :full_name, :nickname, :phone, :birth_date, :age,
            :height_cm, :weight_kg, :birth_province,
            :school_name, :education_level, :major,
            :marital_status, :children_count,
            :has_acquaintance, :acquaintance_name, :acquaintance_relation,
            :referrer_name, :referrer_relation,
            :facebook, :line_id, :has_own_business, :business_type,
            :cur_house_no, :cur_village, :cur_road, :cur_soi,
            :cur_subdistrict_id, :cur_district_id, :cur_province_id, :cur_postal,
            :reg_house_no, :reg_village, :reg_road, :reg_soi,
            :reg_subdistrict_id, :reg_district_id, :reg_province_id, :reg_postal,
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

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        // Section 1
        ':position1'            => str($_POST['position1']),
        ':position2'            => str($_POST['position2'] ?? null),
        ':expected_salary'      => (float)$_POST['expectedSalary'],
        ':profile_image_path'   => $profileImagePath,
        // Section 2
        ':full_name'            => str($_POST['fullName']),
        ':nickname'             => str($_POST['nickname']),
        ':phone'                => str($_POST['phone']),
        ':birth_date'           => str($_POST['birthDate']),
        ':age'                  => num($_POST['age'] ?? null),
        ':height_cm'            => num($_POST['height'] ?? null),
        ':weight_kg'            => num($_POST['weight'] ?? null),
        ':birth_province'       => str($_POST['birthProvince'] ?? null),
        // Section 3
        ':school_name'          => str($_POST['schoolName'] ?? null),
        ':education_level'      => str($_POST['education'] ?? null),
        ':major'                => str($_POST['major'] ?? null),
        // Section 4
        ':marital_status'       => str($_POST['maritalStatus']),
        ':children_count'       => num($_POST['children'] ?? '0'),
        ':has_acquaintance'     => toBool($_POST['hasAcquaintance'] ?? '', 'มี'),
        ':acquaintance_name'    => str($_POST['acquaintanceName'] ?? null),
        ':acquaintance_relation'=> str($_POST['acquaintanceRelation'] ?? null),
        // Section 5
        ':referrer_name'        => str($_POST['referrerName'] ?? null),
        ':referrer_relation'    => str($_POST['referrerRelation'] ?? null),
        // Section 6
        ':facebook'             => str($_POST['facebook'] ?? null),
        ':line_id'              => str($_POST['lineId'] ?? null),
        ':has_own_business'     => toBool($_POST['hasOwnBusiness'] ?? '', 'มี'),
        ':business_type'        => str($_POST['businessType'] ?? null),
        // Section 7
        ':cur_house_no'         => str($_POST['cur_houseNo']),
        ':cur_village'          => str($_POST['cur_village'] ?? null),
        ':cur_road'             => str($_POST['cur_road'] ?? null),
        ':cur_soi'              => str($_POST['cur_soi'] ?? null),
        ':cur_subdistrict_id'   => str($_POST['cur_subdistrict_id'] ?? null),
        ':cur_district_id'      => str($_POST['cur_district_id'] ?? null),
        ':cur_province_id'      => str($_POST['cur_province_id'] ?? null),
        ':cur_postal'           => str($_POST['cur_postal'] ?? null),
        // Section 8
        ':reg_house_no'         => str($_POST['reg_houseNo']),
        ':reg_village'          => str($_POST['reg_village'] ?? null),
        ':reg_road'             => str($_POST['reg_road'] ?? null),
        ':reg_soi'              => str($_POST['reg_soi'] ?? null),
        ':reg_subdistrict_id'   => str($_POST['reg_subdistrict_id'] ?? null),
        ':reg_district_id'      => str($_POST['reg_district_id'] ?? null),
        ':reg_province_id'      => str($_POST['reg_province_id'] ?? null),
        ':reg_postal'           => str($_POST['reg_postal'] ?? null),
        // Section 9
        ':emergency_name'       => str($_POST['emergencyName']),
        ':emergency_relation'   => str($_POST['emergencyRelation']),
        ':emergency_phone'      => str($_POST['emergencyPhone']),
        // Section 10
        ':eng_speaking'         => num($_POST['engSpeak'] ?? null),
        ':eng_writing'          => num($_POST['engWrite'] ?? null),
        // Section 11
        ':ms_word'              => num($_POST['msWord'] ?? null),
        ':ms_excel'             => num($_POST['msExcel'] ?? null),
        ':ms_ppt'               => num($_POST['msPpt'] ?? null),
        // Section 12
        ':can_drive_car'        => str($_POST['canDriveCar'] ?? null),
        ':car_gear'             => str($_POST['carGear'] ?? null),
        ':has_car_license'      => toBool($_POST['hasCarLicense'] ?? '', 'มี'),
        ':has_own_car'          => toBool($_POST['hasOwnCar'] ?? '', 'มี'),
        ':can_drive_moto'       => str($_POST['canDriveMoto'] ?? null),
        ':has_moto_license'     => toBool($_POST['hasMotoLicense'] ?? '', 'มี'),
        ':has_own_moto'         => toBool($_POST['hasOwnMoto'] ?? '', 'มี'),
        // Section 13
        ':vision'               => str($_POST['vision'] ?? null),
        ':is_pregnant'          => toBool($_POST['isPregnant'] ?? '', 'ใช่'),
        ':pregnancy_weeks'      => num($_POST['pregnancyWeeks'] ?? null),
        ':has_chronic_disease'  => toBool($_POST['hasDisease'] ?? '', 'มี'),
        ':chronic_disease'      => str($_POST['chronicDisease'] ?? null),
        ':legal_status'         => str($_POST['legalStatus'] ?? 'ไม่เคย'),
        ':legal_case_type'      => str($_POST['legalCaseType'] ?? null),
        ':drug_status'          => str($_POST['drugStatus'] ?? 'ไม่เคย'),
    ]);

    $row   = $stmt->fetch();
    $appId = $row['id'] ?? null;
    if (!$appId) {
        throw new Exception('ไม่สามารถดึง application ID ได้');
    }

    // ── INSERT work_histories ────────────────────────────────
    $whStmt = $pdo->prepare("
        INSERT INTO work_histories
            (application_id, seq, company_name, district, province,
             biz_type, position, start_date, end_date,
             duty1, duty2, duty3, resign_reason)
        VALUES
            (:app_id, :seq, :company, :district, :province,
             :biz_type, :position, :start_date, :end_date,
             :duty1, :duty2, :duty3, :reason)
    ");

    for ($i = 1; $i <= 5; $i++) {
        $company = str($_POST["job{$i}_company"] ?? null);
        // Skip empty job entries
        if ($company === null && empty(str($_POST["job{$i}_position"] ?? null))) {
            continue;
        }

        $whStmt->execute([
            ':app_id'     => $appId,
            ':seq'        => $i,
            ':company'    => $company,
            ':district'   => str($_POST["job{$i}_district"]  ?? null),
            ':province'   => str($_POST["job{$i}_province"]  ?? null),
            ':biz_type'   => str($_POST["job{$i}_bizType"]   ?? null),
            ':position'   => str($_POST["job{$i}_position"]  ?? null),
            ':start_date' => str($_POST["job{$i}_start"]     ?? null) ?: null,
            ':end_date'   => str($_POST["job{$i}_end"]       ?? null) ?: null,
            ':duty1'      => str($_POST["job{$i}_duty1"]     ?? null),
            ':duty2'      => str($_POST["job{$i}_duty2"]     ?? null),
            ':duty3'      => str($_POST["job{$i}_duty3"]     ?? null),
            ':reason'     => str($_POST["job{$i}_reason"]    ?? null),
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success'        => true,
        'application_id' => (int)$appId,
        'message'        => 'ส่งใบสมัครเรียบร้อยแล้ว',
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ], JSON_UNESCAPED_UNICODE);
}