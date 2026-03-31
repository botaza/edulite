<?php
// File 2 of 8: api.php - WITH FILE LOCKING FOR CONCURRENT USERS

// NO-CACHE HEADERS
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// CORS HEADERS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

$dataDir = __DIR__ . '/data/';
$adminPassHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

// Ensure data directory exists
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0777, true);
}

// IMPROVED: File locking helper functions
function readJsonLocked($file) {
    if (!file_exists($file)) return array();
    
    $handle = fopen($file, 'r');
    if (!$handle) return array();
    
    // Lock for reading (shared lock)
    if (flock($handle, LOCK_SH)) {
        $content = fread($handle, filesize($file));
        flock($handle, LOCK_UN);
        fclose($handle);
        return json_decode($content, true) ? json_decode($content, true) : array();
    }
    
    fclose($handle);
    return array();
}

function writeJsonLocked($file, $data) {
    $handle = fopen($file, 'c+');
    if (!$handle) return false;
    
    // Lock for writing (exclusive lock)
    if (flock($handle, LOCK_EX)) {
        ftruncate($handle, 0);
        fwrite($handle, json_encode($data, JSON_PRETTY_PRINT));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
        return true;
    }
    
    fclose($handle);
    return false;
}

// Get Action
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// 1. Admin Login
if ($action === 'login') {
    $pass = isset($_POST['password']) ? $_POST['password'] : '';
    if ($pass === 'admin123' || password_verify($pass, $adminPassHash)) {
        $_SESSION['is_admin'] = true;
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false));
    }
    exit;
}

// 2. Word Cloud: Submit Word (WITH FILE LOCKING)
if ($action === 'add_word') {
    $word = strip_tags($_POST['word']);
    $user = strip_tags($_POST['username']);
    
    if (strlen($word) > 0 && strlen($word) < 50) {
        $normalized = strtolower(trim($word));
        $words = readJsonLocked($dataDir . 'words.json');
        
        $found = false;
        foreach ($words as &$item) {
            if (strtolower($item['word']) === $normalized) {
                $item['count'] = (isset($item['count']) ? $item['count'] : 1) + 1;
                $item['lastTime'] = time();
                if (!isset($item['users'])) {
                    $item['users'] = array();
                }
                $item['users'][] = $user;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $words[] = array(
                'word' => $normalized,
                'display' => $word,
                'count' => 1,
                'firstTime' => time(),
                'lastTime' => time(),
                'users' => array($user)
            );
        }
        
        if (count($words) > 100) {
            usort($words, function($a, $b) {
                return $b['lastTime'] - $a['lastTime'];
            });
            $words = array_slice($words, 0, 100);
        }
        
        writeJsonLocked($dataDir . 'words.json', $words);
    }
    echo json_encode(array('success' => true));
    exit;
}

// 3. Word Cloud: Get Words (WITH FILE LOCKING)
if ($action === 'get_words') {
    $words = readJsonLocked($dataDir . 'words.json');
    
    usort($words, function($a, $b) {
        if (!isset($a['count'])) $a['count'] = 1;
        if (!isset($b['count'])) $b['count'] = 1;
        if ($b['count'] !== $a['count']) {
            return $b['count'] - $a['count'];
        }
        return $b['lastTime'] - $a['lastTime'];
    });
    
    echo json_encode($words);
    exit;
}

// 4. Word Cloud: Delete Word (WITH FILE LOCKING)
if ($action === 'delete_word' && isset($_SESSION['is_admin'])) {
    $wordToDelete = isset($_POST['word']) ? strtolower(trim($_POST['word'])) : '';
    
    if ($wordToDelete) {
        $words = readJsonLocked($dataDir . 'words.json');
        $filtered = array();
        
        foreach ($words as $item) {
            if (strtolower($item['word']) !== $wordToDelete) {
                $filtered[] = $item;
            }
        }
        
        writeJsonLocked($dataDir . 'words.json', $filtered);
        echo json_encode(array('success' => true, 'message' => 'Word deleted'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Invalid word'));
    }
    exit;
}

// 5. Satisfaction: Vote (WITH FILE LOCKING)
if ($action === 'vote') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $rating = intval($_POST['rating']);
    if ($rating < 1 || $rating > 5) {
        echo json_encode(array('success' => false, 'message' => 'Invalid rating'));
        exit;
    }
    $votes = readJsonLocked($dataDir . 'votes.json');
    if (isset($votes[$ip]) && (time() - $votes[$ip]['time']) < 60) {
        echo json_encode(array('success' => false, 'message' => 'Please wait 1 minute before voting again.'));
        exit;
    }
    $votes[$ip] = array('rating' => $rating, 'time' => time());
    writeJsonLocked($dataDir . 'votes.json', $votes);
    echo json_encode(array('success' => true));
    exit;
}

// 6. Satisfaction: Get Stats (WITH FILE LOCKING)
if ($action === 'get_stats') {
    $votes = readJsonLocked($dataDir . 'votes.json');
    $total = count($votes);
    $sum = 0;
    foreach($votes as $v) {
        $sum += $v['rating'];
    }
    $avg = $total > 0 ? round($sum / $total, 1) : 0;
    echo json_encode(array('average' => $avg, 'count' => $total));
    exit;
}

// 7. Admin: Reset Data (WITH FILE LOCKING)
if ($action === 'reset' && isset($_SESSION['is_admin'])) {
    $type = $_POST['type'];
    if ($type === 'words') writeJsonLocked($dataDir . 'words.json', array());
    if ($type === 'votes') writeJsonLocked($dataDir . 'votes.json', array());
    echo json_encode(array('success' => true));
    exit;
}

// 8. Admin: Check Session
if ($action === 'check_session') {
    echo json_encode(array('is_admin' => isset($_SESSION['is_admin'])));
    exit;
}

// Default response
echo json_encode(array('error' => 'Unknown action'));
?>