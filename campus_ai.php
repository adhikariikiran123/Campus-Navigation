<?php
// campus_ai.php - Enhanced version with complete location data from floor maps
// Added: Canteens, accurate washroom locations, LTs, and all visible facilities
declare(strict_types=1);
define('DEV_SHOW_ERRORS', true);

if (DEV_SHOW_ERRORS) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/campus_ai_error.log');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

set_error_handler(function($errno, $errstr, $errfile = null, $errline = null){
    if (error_reporting() === 0) return false;
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    $raw = (string) ($_POST['question'] ?? $_GET['question'] ?? '');
    $q_utf8 = @mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
    if ($q_utf8 === false) $q_utf8 = (string)$raw;
    $q_utf8 = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', ' ', $q_utf8);
    $question = trim($q_utf8);
    $q = mb_strtolower($question, 'UTF-8');

    if ($question === '' || in_array($q, ['hi','hello','hey','start'], true)) {
        echo json_encode(['ok'=>true,'answer'=>'Hello! 👋 I can help you navigate the GEHU campus. Try asking: "Where is the canteen?", "I\'m in CR 401, go to library", or "Find washroom on third floor"']); 
        restore_error_handler(); 
        exit;
    }

    // ===========================================
    // COMPREHENSIVE LABELS - Based on Floor Maps
    // ===========================================
    $labels = [
        // ============ BASEMENT FLOOR ============
        "BASEMENT_CR_A" => "BASEMENT CR A (Basement Floor)",
        "BASEMENT_CR_B" => "BASEMENT CR B (Basement Floor)",
        "BASEMENT_RR_A_1" => "BASEMENT RR A-1 (Basement Floor)",
        "DIGITAL_LOGIC_MICROPROCESSOR_LAB" => "DIGITAL LOGIC MICROPROCESSOR LAB (Basement Floor)",
        "CIVIL_AND_MECHANICAL_ENGINEERING_LAB" => "CIVIL AND MECHANICAL ENGINEERING LAB (Basement Floor)",
        "CCTV_ROOM" => "CCTV ROOM (Basement Floor)",
        "PHD_CELL" => "PHD CELL (Basement Floor)",
        "REGISTRAR_OFFICE" => "REGISTRAR OFFICE (Basement Floor)",
        "FEE_CELL" => "FEE CELL (Basement Floor)",
        "DEGREE_MARKSHEET" => "DEGREE & MARKSHEET (Basement Floor)",
        "EXAM_CELL" => "EXAM CELL (Basement Floor)",
        "MALE_LIFT_BASEMENT" => "MALE LIFT (Basement Floor)",
        "FEMALE_LIFT_BASEMENT" => "FEMALE LIFT (Basement Floor)",
        "STAFF_LIFT_BASEMENT" => "STAFF LIFT (Basement Floor)",
        "PARKING" => "PARKING (Basement Floor)",
        "DEPARTMENT_OF_CIVIL_MECHANICAL_ENGINEERING" => "DEPARTMENT OF CIVIL & MECHANICAL ENGINEERING (Basement Floor)",

        // ============ GROUND FLOOR ============
        "CR101" => "CR 101 (Ground Floor)",
        "CR102" => "CR 102 (Ground Floor)",
        "CR103" => "CR 103 (Ground Floor)",
        "CR104" => "CR 104 (Ground Floor)",
        "CR105" => "CR 105 (Ground Floor)",
        "PHARMACEUTICS_III_LAB" => "PHARMACEUTICS - III LAB (Ground Floor)",
        "COMPUTER_LAB_1" => "COMPUTER LAB 1 (Ground Floor)",
        "COMPUTER_LAB_10" => "COMPUTER LAB 10 (Ground Floor)",
        "UBUNTU_LAB_1" => "UBUNTU LAB 1 (Ground Floor)",
        "UBUNTU_LAB_2" => "UBUNTU LAB 2 (Ground Floor)",
        "LIBRARY" => "LIBRARY (Ground Floor)",
        "SEMINAR_HALL" => "SEMINAR HALL (Ground Floor)",
        "MEETING_HALL" => "MEETING HALL (Ground Floor)",
        "RECEPTION" => "RECEPTION (Ground Floor)",
        "MI_ROOM" => "MI ROOM (Ground Floor)",
        "TRANSPORT_ENQUIRY" => "TRANSPORT ENQUIRY (Ground Floor)",
        "HOSTEL_ENQUIRY" => "HOSTEL ENQUIRY (Ground Floor)",
        "RESEARCH_DEVELOPMENT_CELL" => "RESEARCH & DEVELOPMENT CELL (Ground Floor)",
        "ADMISSION_BACK_OFFICE" => "ADMISSION BACK OFFICE (Ground Floor)",
        "ADMISSION_ENQUIRY" => "ADMISSION ENQUIRY (Ground Floor)",
        "CORPORATE_RESOURCE_CENTER_PLACEMENT_ALUMNI" => "CORPORATE RESOURCE CENTER (PLACEMENT/ALUMNI) (Ground Floor)",
        "OPEN_AUDITORIUM" => "OPEN AUDITORIUM (Ground Floor)",
        "SCHOOL_OF_PHARMACY_GROUND" => "SCHOOL OF PHARMACY (Ground Floor)",
        "MALE_LIFT_GROUND" => "MALE LIFT (Ground Floor)",
        "FEMALE_LIFT_GROUND" => "FEMALE LIFT (Ground Floor)",
        "STAFF_LIFT_GROUND" => "STAFF LIFT (Ground Floor)",

        // ============ FIRST FLOOR ============
        "CR201" => "CR 201 (First Floor)",
        "CR202" => "CR 202 (First Floor)",
        "CR203" => "CR 203 (First Floor)",
        "CR204" => "CR 204 (First Floor)",
        "CR205" => "CR 205 (First Floor)",
        "CR206" => "CR 206 (First Floor)",
        "CR207" => "CR 207 (First Floor)",
        "LT201" => "LT 201 (First Floor) - Lecture Theatre",
        "LT202" => "LT 202 (First Floor) - Lecture Theatre",
        "COMPUTER_LAB_2" => "COMPUTER LAB 2 (First Floor)",
        "COMPUTER_LAB_9" => "COMPUTER LAB 9 (First Floor)",
        "THIN_CLIENT_LAB_1" => "THIN-CLIENT LAB 1 (First Floor)",
        "THIN_CLIENT_LAB_2" => "THIN-CLIENT LAB 2 (First Floor)",
        "VICE_CHANCELLOR_OFFICE" => "VICE CHANCELLOR OFFICE (First Floor)",
        "PRESIDENT_OFFICE" => "PRESIDENT OFFICE (First Floor)",
        "CENTRAL_LIBRARY" => "CENTRAL LIBRARY (First Floor)",
        "SCHOOL_OF_PHARMACY_FIRST" => "SCHOOL OF PHARMACY (First Floor)",
        "SCHOOL_OF_ENGINEERING" => "SCHOOL OF ENGINEERING (First Floor)",
        "SERVER_ROOM" => "SERVER ROOM (First Floor)",
        "HOD_HUMANITIES" => "HOD HUMANITIES (First Floor)",
        "MALE_LIFT_FIRST" => "MALE LIFT (First Floor)",
        "FEMALE_LIFT_FIRST" => "FEMALE LIFT (First Floor)",
        "STAFF_LIFT_FIRST" => "STAFF LIFT (First Floor)",
        "ERP_CELL" => "ERP CELL (First Floor)",

        // ============ SECOND FLOOR (Changed from Third) ============
        "CR301" => "CR 301 (Second Floor)",
        "CR302" => "CR 302 (Second Floor)",
        "CR303" => "CR 303 (Second Floor)",
        "CR304" => "CR 304 (Second Floor)",
        "CR305" => "CR 305 (Second Floor)",
        "LT301" => "LT 301 (Second Floor) - Lecture Theatre",
        "LT302" => "LT 302 (Second Floor) - Lecture Theatre",
        "COMPUTER_LAB_3" => "COMPUTER LAB 3 (Second Floor)",
        "COMPUTER_LAB_8" => "COMPUTER LAB 8 (Second Floor)",
        "ANIMATION_AND_GAMING_LAB" => "ANIMATION AND GAMING LAB (Second Floor)",
        "PHARMA_LAB_I" => "PHARMA LAB - I (Second Floor)",
        "PHARMA_LAB_II" => "PHARMA LAB - II (Second Floor)",
        "PHARMA_CHEMISTRY_LAB" => "PHARMA CHEMISTRY LAB (Second Floor)",
        "PHARMACOGNOSY_LAB" => "PHARMACOGNOSY LAB (Second Floor)",
        "PHARMACEUTICAL_ANALYSIS" => "PHARMACEUTICAL ANALYSIS (Second Floor)",
        "DEPARTMENT_OF_VISUAL_ART" => "DEPARTMENT OF VISUAL ART (Second Floor)",
        "DEPARTMENT_OF_MEDIA_AND_MASS_COMMUNICATION" => "DEPARTMENT OF MEDIA AND MASS COMMUNICATION (Second Floor)",
        "NEW_HALL_STAFF_ROOM" => "NEW HALL STAFF ROOM (Second Floor)",
        "STUDENTS_COUNSELLOR" => "STUDENT'S COUNSELLOR (Second Floor)",
        "MALE_LIFT_SECOND" => "MALE LIFT (Second Floor)",
        "FEMALE_LIFT_SECOND" => "FEMALE LIFT (Second Floor)",
        "STAFF_LIFT_SECOND" => "STAFF LIFT (Second Floor)",

        // ============ THIRD FLOOR ============
        "CR401" => "CR 401 (Third Floor)",
        "CR402" => "CR 402 (Third Floor)",
        "CR403" => "CR 403 (Third Floor)",
        "CR405" => "CR 405 (Third Floor)",
        "CR406" => "CR 406 (Third Floor)",
        "LT401" => "LT 401 (Third Floor) - Lecture Theatre",
        "LT402" => "LT 402 (Third Floor) - Lecture Theatre",
        "COMPUTER_LAB_4" => "COMPUTER LAB 4 (Third Floor)",
        "COMPUTER_LAB_7" => "COMPUTER LAB 7 (Third Floor)",
        "COMPUTER_LAB_11" => "COMPUTER LAB 11 (Third Floor)",
        "COMPUTER_LAB_12" => "COMPUTER LAB 12 (Third Floor)",
        "LOGIC_DESIGN_MICROPROCESSOR_LAB" => "LOGIC DESIGN (MICROPROCESSOR LAB) (Third Floor)",
        "BASIC_ELECTRONIC_ENGINEERING_LAB" => "BASIC ELECTRONIC ENGINEERING LAB (Third Floor)",
        "MAC_LAB" => "MAC LAB (Third Floor)",
        "SCHOOL_OF_COMPUTING" => "SCHOOL OF COMPUTING (Third Floor)",
        "COMPUTER_SCIENCE_STAFF_ROOM" => "COMPUTER SCIENCE & ENGINEERING STAFF ROOM (Third Floor)",
        "HOD_PDP" => "HOD PDP (Third Floor)",
        "FACULTY_ROOM_THIRD" => "FACULTY ROOM (Third Floor)",
        "MALE_LIFT_THIRD" => "MALE LIFT (Third Floor)",
        "FEMALE_LIFT_THIRD" => "FEMALE LIFT (Third Floor)",
        "STAFF_LIFT_THIRD" => "STAFF LIFT (Third Floor)",
        "BCA_BTech_1_YEAR" => "BCA/BTech 1st Year (Third Floor)",

        // ============ FOURTH FLOOR ============
        "CR501" => "CR 501 (Fourth Floor)",
        "CR502" => "CR 502 (Fourth Floor)",
        "CR503" => "CR 503 (Fourth Floor)",
        "CR504" => "CR 504 (Fourth Floor)",
        "LT501" => "LT 501 (Fourth Floor) - Lecture Theatre",
        "LT502" => "LT 502 (Fourth Floor) - Lecture Theatre",
        "COMPUTER_LAB_5" => "COMPUTER LAB 5 (Fourth Floor)",
        "PHYSICS_LAB" => "PHYSICS LAB (Fourth Floor)",
        "IAPT_LAB" => "IAPT LAB (Fourth Floor)",
        "CHEMISTRY_LAB" => "CHEMISTRY LAB (Fourth Floor)",
        "GC_LAB_I" => "G.C. LAB I (Fourth Floor)",
        "GC_LAB_II" => "G.C. LAB II (Fourth Floor)",
        "DEPT_OF_PHYSICS" => "DEPARTMENT OF PHYSICS (Fourth Floor)",
        "DEPT_OF_FASHION_DESIGN" => "DEPARTMENT OF FASHION AND DESIGN (Fourth Floor)",
        "HOD_PHYSICS" => "HOD PHYSICS (Fourth Floor)",
        "HOD_FASHION_DESIGN" => "HOD FASHION DESIGN (Fourth Floor)",
        "HOD_PDP_FOURTH" => "HOD PDP (Fourth Floor)",
        "MALE_LIFT_FOURTH" => "MALE LIFT (Fourth Floor)",
        "FEMALE_LIFT_FOURTH" => "FEMALE LIFT (Fourth Floor)",
        "STAFF_LIFT_FOURTH" => "STAFF LIFT (Fourth Floor)",

        // ============ FIFTH FLOOR ============
        "CR601" => "CR 601 (Fifth Floor)",
        "CR602" => "CR 602 (Fifth Floor)",
        "CR603" => "CR 603 (Fifth Floor)",
        "CR604" => "CR 604 (Fifth Floor)",
        "CR605" => "CR 605 (Fifth Floor)",
        "CR606" => "CR 606 (Fifth Floor)",
        "LT601" => "LT 601 (Fifth Floor) - Lecture Theatre",
        "LT602" => "LT 602 (Fifth Floor) - Lecture Theatre",
        "COMPUTER_LAB_6" => "COMPUTER LAB 6 (Fifth Floor)",
        "LAW_LIBRARY" => "LAW LIBRARY (Fifth Floor)",
        "PDP_STAFF_ROOM" => "PDP STAFF ROOM (Fifth Floor)",
        "LAW_STAFF_ROOM" => "LAW STAFF ROOM (Fifth Floor)",
        "SCHOOL_OF_LAW" => "SCHOOL OF LAW (Fifth Floor)",
        "KP_NAUTIYAL_AUDITORIUM" => "KP NAUTIYAL AUDITORIUM (Fifth Floor)",
        "NEW_AUDITORIUM" => "NEW AUDITORIUM (Fifth Floor)",
        "MOOT_COURT" => "MOOT COURT (Fifth Floor)",
        "NCC_ROOM" => "NCC ROOM (Fifth Floor)",
        "MALE_LIFT_FIFTH" => "MALE LIFT (Fifth Floor)",
        "FEMALE_LIFT_FIFTH" => "FEMALE LIFT (Fifth Floor)",
        "STAFF_LIFT_FIFTH" => "STAFF LIFT (Fifth Floor)",
    ];

    // ===========================================
    // WASHROOMS - Extracted from Floor Maps
    // ===========================================
    $washrooms = [
        // GROUND FLOOR WASHROOMS
        "BOYS_WASHROOM_GROUND" => "Boys Washroom (Ground Floor - Near Computer Lab 1)",
        "GIRLS_WASHROOM_GROUND" => "Girls Washroom (Ground Floor - Near Computer Lab 10)",
        "MALE_WASHROOM_GROUND" => "Male Washroom (Ground Floor - Near Open Auditorium)",
        "FEMALE_WASHROOM_GROUND" => "Female Washroom (Ground Floor - Near MI Room)",
        "GUEST_WASHROOM_GROUND" => "Guest Washroom (Ground Floor - Near Meeting Hall)",
        "FACULTY_ROOM_GROUND" => "Faculty Room (Ground Floor - Near Boys Washroom)",
        
        // FIRST FLOOR WASHROOMS
        "BOYS_WASHROOM_FIRST" => "Boys Washroom (First Floor - Near Computer Lab 2)",
        "GIRLS_WASHROOM_FIRST" => "Girls Washroom (First Floor - Near ERP Cell)",
        "MALE_WASHROOM_FIRST" => "Male Washroom (First Floor - Near LT 202)",
        "FEMALE_WASHROOM_FIRST" => "Female Washroom (First Floor - Near HOD Humanities)",
        "GUEST_WASHROOM_FIRST" => "Guest Washroom (First Floor - Near Vice Chancellor Office)",
        "FACULTY_ROOM_FIRST" => "Faculty Room (First Floor - Near Guest Washroom)",
        
        // SECOND FLOOR WASHROOMS
        "BOYS_WASHROOM_SECOND" => "Boys Washroom (Second Floor - Near Computer Lab 3)",
        "GIRLS_WASHROOM_SECOND" => "Girls Washroom (Second Floor - Near Pharmacognosy Lab)",
        "MALE_WASHROOM_SECOND" => "Male Washroom (Second Floor - Near LT 302)",
        "FEMALE_WASHROOM_SECOND" => "Female Washroom (Second Floor - Near Student's Counsellor)",
        "GUEST_WASHROOM_SECOND" => "Guest Washroom (Second Floor - Near Department of Media)",
        "FACULTY_ROOM_SECOND" => "Faculty Room (Second Floor - Near Guest Washroom)",
        
        // THIRD FLOOR WASHROOMS
        "BOYS_WASHROOM_THIRD" => "Boys Washroom (Third Floor - Near Computer Lab 4)",
        "GIRLS_WASHROOM_THIRD" => "Girls Washroom (Third Floor - Near BCA/BTech 1st Year)",
        "MALE_WASHROOM_THIRD" => "Male Washroom (Third Floor - Near LT 402)",
        "FEMALE_WASHROOM_THIRD" => "Female Washroom (Third Floor - Near Faculty Room)",
        "GUEST_WASHROOM_THIRD" => "Guest Washroom (Third Floor - Near HOD PDP)",
        "FACULTY_ROOM_THIRD_WR" => "Faculty Room (Third Floor - Near Guest Washroom)",
        
        // FOURTH FLOOR WASHROOMS
        "BOYS_WASHROOM_FOURTH" => "Boys Washroom (Fourth Floor - Near Computer Lab 5)",
        "GIRLS_WASHROOM_FOURTH" => "Girls Washroom (Fourth Floor - Near Chemistry Lab)",
        "MALE_WASHROOM_FOURTH" => "Male Washroom (Fourth Floor - Near LT 502)",
        "FEMALE_WASHROOM_FOURTH" => "Female Washroom (Fourth Floor - Near HOD Fashion Design)",
        "GUEST_WASHROOM_FOURTH" => "Guest Washroom (Fourth Floor - Near HOD PDP)",
        "HOD_WASHROOM_FOURTH" => "HOD Washroom (Fourth Floor)",
        
        // FIFTH FLOOR WASHROOMS
        "BOYS_WASHROOM_FIFTH" => "Boys Washroom (Fifth Floor - Near Computer Lab 6)",
        "GIRLS_WASHROOM_FIFTH" => "Girls Washroom (Fifth Floor - Near Law Library)",
        "MALE_WASHROOM_FIFTH" => "Male Washroom (Fifth Floor - Near LT 602)",
        "FEMALE_WASHROOM_FIFTH" => "Female Washroom (Fifth Floor - Near NCC Room)",
        "GUEST_WASHROOM_FIFTH" => "Guest Washroom (Fifth Floor - Near Moot Court)",
        "FACULTY_ROOM_FIFTH" => "Faculty Room (Fifth Floor - Near Guest Washroom)",
    ];

    // ===========================================
    // CANTEENS & FOOD SERVICES
    // ===========================================
    $canteens = [
        "CANTEEN_GROUND" => "Main Canteen (Ground Floor - Near Corridor)",
        "CAFETERIA_GROUND" => "Cafeteria (Ground Floor - Near Open Auditorium)",
        "FOOD_COURT_GROUND" => "Food Court (Ground Floor)",
        "COFFEE_SHOP_FIRST" => "Coffee Shop (First Floor - Near Central Library)",
        "SNACKS_CORNER_THIRD" => "Snacks Corner (Third Floor - School of Computing)",
        "TEA_STALL_GROUND" => "Tea Stall (Ground Floor - Outside Building)",
    ];

    // ===========================================
    // ADDITIONAL FACILITIES
    // ===========================================
    $facilities = [
        "ATM_GROUND" => "ATM (Ground Floor - Near Reception)",
        "MEDICAL_ROOM" => "Medical Room (First Floor)",
        "PHOTOCOPY_CENTER" => "Photocopy Center (Ground Floor - Near Library)",
        "BOOKSTORE" => "Bookstore (Ground Floor - Near Library)",
        "STATIONARY_SHOP" => "Stationary Shop (Ground Floor)",
        "COMMON_ROOM_BOYS" => "Boys Common Room (Second Floor)",
        "COMMON_ROOM_GIRLS" => "Girls Common Room (Second Floor)",
        "WATER_COOLER_GROUND" => "Water Cooler (Ground Floor - Multiple Locations)",
        "WATER_COOLER_FIRST" => "Water Cooler (First Floor - Multiple Locations)",
        "WATER_COOLER_SECOND" => "Water Cooler (Second Floor - Multiple Locations)",
        "WATER_COOLER_THIRD" => "Water Cooler (Third Floor - Multiple Locations)",
        "WATER_COOLER_FOURTH" => "Water Cooler (Fourth Floor - Multiple Locations)",
        "WATER_COOLER_FIFTH" => "Water Cooler (Fifth Floor - Multiple Locations)",
    ];

    // Merge all location types
    $labels = array_merge($labels, $washrooms, $canteens, $facilities);

    // ===========================================
    // BUILD GRAPH - Connect rooms to corridors and vertical transport
    // ===========================================
    $graph = [];
    $floors = [];

    foreach ($labels as $node => $label) {
        // Extract floor name from label
        if (preg_match('/\((.*?) Floor\)/', $label, $m)) {
            $floorName = $m[1];
        } else {
            if (stripos($label, 'Ground') !== false) $floorName = 'Ground';
            elseif (stripos($label, 'First') !== false) $floorName = 'First';
            elseif (stripos($label, 'Second') !== false) $floorName = 'Second';
            elseif (stripos($label, 'Third') !== false) $floorName = 'Third';
            elseif (stripos($label, 'Fourth') !== false) $floorName = 'Fourth';
            elseif (stripos($label, 'Fifth') !== false) $floorName = 'Fifth';
            elseif (stripos($label, 'Basement') !== false) $floorName = 'Basement';
            else $floorName = 'Ground';
        }
        
        $corr = 'Corridor_' . strtoupper($floorName);
        $floors[$floorName] = true;
        
        if (!isset($graph[$node])) $graph[$node] = [];
        if (!isset($graph[$corr])) $graph[$corr] = [];
        
        if (!in_array($corr, $graph[$node], true)) $graph[$node][] = $corr;
        if (!in_array($node, $graph[$corr], true)) $graph[$corr][] = $node;
    }

    // Connect corridors to stairs and elevators
    $stair = 'Stair_A';
    $elevator = 'Elevator_Main';
    if (!isset($graph[$stair])) $graph[$stair] = [];
    if (!isset($graph[$elevator])) $graph[$elevator] = [];
    
    foreach (array_keys($floors) as $fn) {
        $corr = 'Corridor_' . strtoupper($fn);
        if (!in_array($stair, $graph[$corr], true)) $graph[$corr][] = $stair;
        if (!in_array($elevator, $graph[$corr], true)) $graph[$corr][] = $elevator;
        if (!in_array($corr, $graph[$stair], true)) $graph[$stair][] = $corr;
        if (!in_array($corr, $graph[$elevator], true)) $graph[$elevator][] = $corr;
    }

    // ===========================================
    // HELPER FUNCTIONS
    // ===========================================
    function normalize_token(string $s): string {
        $s = trim($s);
        $s = preg_replace('/\s+/', ' ', $s);
        $up = mb_strtoupper($s, 'UTF-8');
        if (preg_match('/\bCR[\s-]*(\d{1,3})\b/u', $up, $m)) return 'CR' . $m[1];
        if (preg_match('/\bLT[\s-]*(\d{1,3})\b/u', $up, $m)) return 'LT' . $m[1];
        $t = preg_replace('/[^A-Z0-9 ]+/', ' ', $up);
        $t = preg_replace('/\s+/', '_', trim($t));
        return $t;
    }

    function find_node_candidates(string $text, array $labels): array {
        $found = [];
        $upper = mb_strtoupper($text, 'UTF-8');

        // Special handling for common queries
        if (preg_match('/\b(canteen|cafe|cafeteria|food)\b/iu', $text)) {
            foreach ($labels as $nid => $lbl) {
                if (stripos($nid, 'CANTEEN') !== false || stripos($nid, 'CAFETERIA') !== false || stripos($nid, 'FOOD') !== false) {
                    if (!in_array($nid, $found, true)) $found[] = $nid;
                }
            }
        }

        if (preg_match('/\b(washroom|toilet|restroom|bathroom|loo)\b/iu', $text)) {
            foreach ($labels as $nid => $lbl) {
                if (stripos($nid, 'WASHROOM') !== false) {
                    if (!in_array($nid, $found, true)) $found[] = $nid;
                }
            }
        }

        // Detect CR / LT patterns
        if (@preg_match_all('/\bCR[\s-]*\d{1,3}\b/u', $text, $m) && !empty($m[0])) {
            foreach ($m[0] as $tok) {
                $nid = normalize_token($tok);
                if (isset($labels[$nid]) && !in_array($nid, $found, true)) $found[] = $nid;
            }
        }
        if (@preg_match_all('/\bLT[\s-]*\d{1,3}\b/u', $text, $m) && !empty($m[0])) {
            foreach ($m[0] as $tok) {
                $nid = normalize_token($tok);
                if (isset($labels[$nid]) && !in_array($nid, $found, true)) $found[] = $nid;
            }
        }

        // Match by human label substrings
        foreach ($labels as $nid => $lbl) {
            $human = preg_replace('/\s*\(.*?\)\s*/', '', $lbl);
            if (@mb_stripos($upper, mb_strtoupper($human, 'UTF-8')) !== false || @mb_stripos($upper, $nid) !== false) {
                if (!in_array($nid, $found, true)) $found[] = $nid;
            }
        }

        return $found;
    }

    function bfs_path(array $graph, string $start, string $goal): ?array {
        if ($start === $goal) return [$start];
        if (!isset($graph[$start]) || !isset($graph[$goal])) return null;
        
        $queue = new SplQueue();
        $queue->enqueue($start);
        $visited = [$start => true];
        $parent = [];
        
        while (!$queue->isEmpty()) {
            $u = $queue->dequeue();
            foreach ($graph[$u] as $v) {
                if (!isset($visited[$v])) {
                    $visited[$v] = true;
                    $parent[$v] = $u;
                    if ($v === $goal) {
                        $path = [$v];
                        while (isset($parent[$path[0]])) array_unshift($path, $parent[$path[0]]);
                        return $path;
                    }
                    $queue->enqueue($v);
                }
            }
        }
        return null;
    }

    function path_to_steps(array $path, array $labels): array {
        $steps = [];
        $prevFloor = null;
        
        foreach ($path as $i => $node) {
            $human = $labels[$node] ?? $node;
            
            // Extract current floor
            if (preg_match('/\((.*?) Floor\)/', $human, $m)) {
                $currentFloor = $m[1];
            } else {
                $currentFloor = null;
            }
            
            if (preg_match('/^CR\d+/i', $node) || preg_match('/^LT\d+/i', $node)) {
                $steps[] = "📍 Arrive at <strong>{$node}</strong> — {$human}";
            } elseif (strpos($node, 'Corridor_') === 0) {
                $floor = str_replace('Corridor_', '', $node);
                if ($prevFloor !== null && $prevFloor !== $floor) {
                    $steps[] = "🚶 Walk through the {$floor} Floor corridor";
                } else {
                    $steps[] = "🚶 Walk through the {$floor} Floor corridor";
                }
            } elseif ($node === 'Stair_A') {
                $steps[] = "🪜 Use the stairwell to change floors";
            } elseif ($node === 'Elevator_Main') {
                $steps[] = "🛗 Take the elevator to change floors";
            } elseif (stripos($node, 'WASHROOM') !== false) {
                $steps[] = "🚻 {$human}";
            } elseif (stripos($node, 'CANTEEN') !== false || stripos($node, 'CAFETERIA') !== false) {
                $steps[] = "🍽️ {$human}";
            } else {
                $steps[] = "📌 {$human}";
            }
            
            $prevFloor = $currentFloor;
        }
        
        return $steps;
    }

    // ===========================================
    // INTENT PARSING & RESPONSE
    // ===========================================
    $from = null; 
    $to = null;

    // Parse "from X to Y" pattern
    if (@preg_match('/\bfrom\s+(.+?)\s+to\s+(.+?)(?:$|\?|\.|,)/iu', $question, $m)) {
        $candFrom = $m[1] ?? ''; 
        $candTo = $m[2] ?? '';
        $fFrom = find_node_candidates($candFrom, $labels);
        $fTo = find_node_candidates($candTo, $labels);
        if (!empty($fFrom)) $from = $fFrom[0];
        if (!empty($fTo)) $to = $fTo[0];
    }

    // Parse "I'm in/at X" pattern
    if ($from === null && @preg_match('/\bI(?:\'?m| am) (?:in|at)\s+([^\.,;!?]+)/iu', $question, $m2)) {
        $cand = $m2[1] ?? '';
        $f = find_node_candidates($cand, $labels);
        if (!empty($f)) $from = $f[0];
    }

    // Parse "go to X" pattern
    if ($to === null && @preg_match('/\b(?:go to|want to go to|goto|to|find|where is|show me)\s+([^\.,;!?]+)/iu', $question, $m3)) {
        $cand = $m3[1] ?? '';
        $f = find_node_candidates($cand, $labels);
        if (!empty($f)) $to = $f[0];
    }

    // Fallback: try to extract any two locations
    if (($from === null || $to === null)) {
        $all = find_node_candidates($question, $labels);
        if (count($all) >= 2) {
            if (preg_match('/\bI(?:\'?m| am) (?:in|at)\b/i', $question)) { 
                $from = $all[0]; 
                $to = $all[1]; 
            } else {
                $from = $all[0]; 
                $to = $all[1];
            }
        } elseif (count($all) === 1) {
            if (preg_match('/\bI(?:\'?m| am) (?:in|at)\b/i', $question)) {
                $from = $all[0];
            } else {
                $to = $all[0];
            }
        }
    }

    // Normalize node IDs
    if ($from !== null && !isset($labels[$from])) { 
        $cand = normalize_token($from); 
        if (isset($labels[$cand])) $from = $cand; 
    }
    if ($to !== null && !isset($labels[$to])) { 
        $cand = normalize_token($to); 
        if (isset($labels[$cand])) $to = $cand; 
    }

    // ===========================================
    // GENERATE RESPONSE
    // ===========================================
    
    // If we have both from and to, find path
    if ($from !== null && $to !== null && isset($labels[$from]) && isset($labels[$to])) {
        $path = bfs_path($graph, $from, $to);
        if ($path !== null) {
            $steps = path_to_steps($path, $labels);
            $answer = ["<strong>🗺️ Route: {$labels[$from]}</strong><br>⬇️<br><strong>🎯 To: {$labels[$to]}</strong><br><br>"];
            $i = 1; 
            foreach ($steps as $s) { 
                $answer[] = "<strong>Step {$i}:</strong> {$s}"; 
                $i++; 
            }
            echo json_encode(['ok' => true, 'answer' => implode('<br>', $answer)]); 
            restore_error_handler(); 
            exit;
        } else {
            echo json_encode([
                'ok' => true, 
                'answer' => "❌ Route not found between <strong>{$labels[$from]}</strong> and <strong>{$labels[$to]}</strong>. Try asking for nearest stair/elevator or check if locations exist."
            ]); 
            restore_error_handler(); 
            exit;
        }
    }

    // If we only have destination (where is X?)
    $found = find_node_candidates($question, $labels);
    if (!empty($found)) { 
        $node = $found[0]; 
        $label = $labels[$node] ?? $node;
        
        // If multiple matches, show all
        if (count($found) > 1) {
            $matches = array_slice($found, 0, 5); // Show max 5
            $list = [];
            foreach ($matches as $match) {
                $list[] = "📍 <strong>{$match}</strong> → {$labels[$match]}";
            }
            echo json_encode([
                'ok' => true, 
                'answer' => "✅ Found multiple locations:<br><br>" . implode('<br>', $list)
            ]); 
        } else {
            echo json_encode([
                'ok' => true, 
                'answer' => "✅ <strong>{$node}</strong><br><br>📍 {$label}<br><br>💡 <em>Tip: Ask 'I'm in [your location], go to {$node}' for directions!</em>"
            ]); 
        }
        restore_error_handler(); 
        exit; 
    }

    // No matches found - suggest popular locations
    $popular = [
        'LIBRARY', 'CANTEEN_GROUND', 'RECEPTION', 'CR101', 'CR401', 
        'LT501', 'LT601', 'MOOT_COURT', 'SCHOOL_OF_COMPUTING', 
        'BOYS_WASHROOM_GROUND', 'CAFETERIA_GROUND'
    ];
    $hints = []; 
    foreach ($popular as $p) {
        if (isset($labels[$p])) {
            $hints[] = "• {$labels[$p]}";
        }
    }
    
    echo json_encode([
        'ok' => true, 
        'answer' => "❌ Location not found. Try asking about:<br><br>" . implode('<br>', array_slice($hints, 0, 8)) . "<br><br>💡 Or try: 'Where is the canteen?', 'Find washroom on third floor', 'I'm in CR 401, go to library'"
    ]); 
    restore_error_handler(); 
    exit;

} catch (\Throwable $e) {
    $msg = $e->getMessage();
    echo json_encode(['ok' => false, 'error' => 'Sorry, something went wrong. Please try again or rephrase your question.']);
    error_log("Campus AI Error: " . $msg);
    restore_error_handler();
    exit;
}
?>