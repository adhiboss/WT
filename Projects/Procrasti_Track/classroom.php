<?php
/**
 * ProcrastiTrack — Google Classroom API Proxy
 * Fetches assignments from Google Classroom and returns JSON
 * 
 * Required: Valid Google OAuth access_token in session
 * Scopes needed:
 *   - https://www.googleapis.com/auth/classroom.courses.readonly
 *   - https://www.googleapis.com/auth/classroom.coursework.me.readonly
 */
session_start();
header('Content-Type: application/json');

// Auth check
if (empty($_SESSION['user']['google'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated with Google']);
    exit;
}

$access_token = $_SESSION['google_access_token'] ?? null;

/**
 * Fetch courses from Google Classroom API
 * GET https://classroom.googleapis.com/v1/courses?courseStates=ACTIVE
 */
function fetchCourses(string $token): array {
    $url = 'https://classroom.googleapis.com/v1/courses?courseStates=ACTIVE';
    $ctx = stream_context_create(['http' => [
        'header' => "Authorization: Bearer $token\r\nAccept: application/json\r\n"
    ]]);
    $res = @file_get_contents($url, false, $ctx);
    if (!$res) return [];
    $data = json_decode($res, true);
    return $data['courses'] ?? [];
}

/**
 * Fetch coursework (assignments) for a course
 * GET https://classroom.googleapis.com/v1/courses/{courseId}/courseWork
 */
function fetchCoursework(string $token, string $courseId): array {
    $url = "https://classroom.googleapis.com/v1/courses/$courseId/courseWork?orderBy=dueDate+asc";
    $ctx = stream_context_create(['http' => [
        'header' => "Authorization: Bearer $token\r\nAccept: application/json\r\n"
    ]]);
    $res = @file_get_contents($url, false, $ctx);
    if (!$res) return [];
    $data = json_decode($res, true);
    return $data['courseWork'] ?? [];
}

// --- Demo mode (no real token) ---
if (!$access_token) {
    echo json_encode([
        'demo' => true,
        'courses' => [
            ['id' => 'c1', 'name' => 'Math 101', 'section' => 'Section A'],
            ['id' => 'c2', 'name' => 'Biology',  'section' => 'Section B'],
            ['id' => 'c3', 'name' => 'English Lit', 'section' => 'Section C'],
        ],
        'assignments' => [
            ['id'=>'a1','courseId'=>'c1','courseName'=>'Math 101','title'=>'Chapter 6 Exercises','dueDate'=>['year'=>2025,'month'=>4,'day'=>8],'maxPoints'=>100,'state'=>'PUBLISHED'],
            ['id'=>'a2','courseId'=>'c2','courseName'=>'Biology','title'=>'Cell Division Report','dueDate'=>['year'=>2025,'month'=>4,'day'=>10],'maxPoints'=>50,'state'=>'PUBLISHED'],
            ['id'=>'a3','courseId'=>'c3','courseName'=>'English Lit','title'=>'Essay Draft','dueDate'=>['year'=>2025,'month'=>4,'day'=>5],'maxPoints'=>80,'state'=>'PUBLISHED'],
            ['id'=>'a4','courseId'=>'c1','courseName'=>'Math 101','title'=>'Practice Quiz','dueDate'=>['year'=>2025,'month'=>4,'day'=>12],'maxPoints'=>30,'state'=>'PUBLISHED'],
        ]
    ]);
    exit;
}

// Real API call
$courses = fetchCourses($access_token);
$assignments = [];
foreach ($courses as $course) {
    $work = fetchCoursework($access_token, $course['id']);
    foreach ($work as $item) {
        $item['courseName'] = $course['name'];
        $assignments[] = $item;
    }
}
echo json_encode(['courses' => $courses, 'assignments' => $assignments]);
