<?php
// File 2 of 8: api.php - COMPLETE WITH USERNAME FOR EMOJI VOTES

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

// File locking helper functions
function readJsonLocked($file) {
    if (!file_exists($file)) return array();
    $handle = fopen($file, 'r');
    if (!$handle) return array();
    if (flock($handle, LOCK_SH)) {
        $content = fread($handle, filesize($file) ?: 0);
        flock($handle, LOCK_UN);
        fclose($handle);
        return json_decode($content, true) ?: array();
    }
    fclose($handle);
    return array();
}

function writeJsonLocked($file, $data) {
    $handle = fopen($file, 'c+');
    if (!$handle) return false;
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

// Helper: Get current lap number
function getCurrentLap($dataDir) {
    $laps = readJsonLocked($dataDir . 'emoji_laps.json');
    return isset($laps['current']) ? $laps['current'] : 0;
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

// 2. Word Cloud: Submit Word
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
                if (!isset($item['users'])) $item['users'] = array();
                $item['users'][] = $user;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $words[] = array(
                'word' => $normalized, 'display' => $word, 'count' => 1,
                'firstTime' => time(), 'lastTime' => time(), 'users' => array($user)
            );
        }
        if (count($words) > 100) {
            usort($words, function($a, $b) { return $b['lastTime'] - $a['lastTime']; });
            $words = array_slice($words, 0, 100);
        }
        writeJsonLocked($dataDir . 'words.json', $words);
    }
    echo json_encode(array('success' => true));
    exit;
}

// 3. Word Cloud: Get Words
if ($action === 'get_words') {
    $words = readJsonLocked($dataDir . 'words.json');
    usort($words, function($a, $b) {
        $countA = isset($a['count']) ? $a['count'] : 1;
        $countB = isset($b['count']) ? $b['count'] : 1;
        if ($countB !== $countA) return $countB - $countA;
        return $b['lastTime'] - $a['lastTime'];
    });
    echo json_encode($words);
    exit;
}

// 4. Word Cloud: Delete Word
if ($action === 'delete_word' && isset($_SESSION['is_admin'])) {
    $wordToDelete = isset($_POST['word']) ? strtolower(trim($_POST['word'])) : '';
    if ($wordToDelete) {
        $words = readJsonLocked($dataDir . 'words.json');
        $filtered = array();
        foreach ($words as $item) {
            if (strtolower($item['word']) !== $wordToDelete) $filtered[] = $item;
        }
        writeJsonLocked($dataDir . 'words.json', $filtered);
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Invalid word'));
    }
    exit;
}

// 5. EMOJI METER: Submit Vote (WITH USERNAME)
if ($action === 'emoji_vote') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $emoji = isset($_POST['emoji']) ? $_POST['emoji'] : '';
    $username = isset($_POST['username']) ? strip_tags($_POST['username']) : 'Anonymous';
    $validEmojis = array('done', 'unsure', 'pain', 'happy', 'help');
    
    if (!in_array($emoji, $validEmojis)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid emoji'));
        exit;
    }
    
    $votes = readJsonLocked($dataDir . 'emoji_votes.json');
    
    // 60 second cooldown (by IP)
    if (isset($votes[$ip]) && (time() - $votes[$ip]['time']) < 60) {
        $waitTime = 60 - (time() - $votes[$ip]['time']);
        echo json_encode(array(
            'success' => false, 
            'message' => 'Please wait ' . $waitTime . ' seconds',
            'waitTime' => $waitTime
        ));
        exit;
    }
    
    // Record vote with username
    $votes[$ip] = array(
        'emoji' => $emoji, 
        'time' => time(), 
        'lap' => getCurrentLap($dataDir),
        'username' => $username
    );
    writeJsonLocked($dataDir . 'emoji_votes.json', $votes);
    
    // Broadcast emoji for animation
    file_put_contents($dataDir . 'emoji_animation.json', json_encode(array(
        'emoji' => $emoji,
        'time' => time(),
        'lap' => getCurrentLap($dataDir)
    )));
    
    echo json_encode(array('success' => true, 'emoji' => $emoji));
    exit;
}

// 6. EMOJI METER: Get Stats
if ($action === 'get_emoji_stats') {
    $votes = readJsonLocked($dataDir . 'emoji_votes.json');
    $currentLap = getCurrentLap($dataDir);
    
    $stats = array(
        'done' => 0, 'unsure' => 0, 'pain' => 0, 'happy' => 0, 'help' => 0,
        'total' => 0, 'lap' => $currentLap
    );
    
    $lapStats = array('done' => 0, 'unsure' => 0, 'pain' => 0, 'happy' => 0, 'help' => 0, 'total' => 0);
    
    foreach ($votes as $vote) {
        $emoji = isset($vote['emoji']) ? $vote['emoji'] : '';
        $voteLap = isset($vote['lap']) ? $vote['lap'] : 0;
        
        if (isset($stats[$emoji])) {
            $stats[$emoji]++;
            $stats['total']++;
            
            if ($voteLap == $currentLap && isset($lapStats[$emoji])) {
                $lapStats[$emoji]++;
                $lapStats['total']++;
            }
        }
    }
    
    echo json_encode(array(
        'success' => true,
        'allTime' => $stats,
        'currentLap' => $lapStats,
        'lapNumber' => $currentLap
    ));
    exit;
}

// 7. EMOJI METER: Get Latest Animation
if ($action === 'get_emoji_animation') {
    $animationFile = $dataDir . 'emoji_animation.json';
    if (file_exists($animationFile)) {
        $animation = readJsonLocked($animationFile);
        if (isset($animation['time']) && (time() - $animation['time']) < 5) {
            echo json_encode($animation);
            exit;
        }
    }
    echo json_encode(array('emoji' => null));
    exit;
}

// 8. EMOJI METER: Reset Votes
if ($action === 'reset_emoji' && isset($_SESSION['is_admin'])) {
    $type = isset($_POST['type']) ? $_POST['type'] : 'all';
    
    if ($type === 'lap') {
        $laps = readJsonLocked($dataDir . 'emoji_laps.json');
        $currentLap = isset($laps['current']) ? $laps['current'] : 0;
        $laps['current'] = $currentLap + 1;
        if (!isset($laps['history'])) $laps['history'] = array();
        $laps['history'][] = array('lap' => $currentLap + 1, 'time' => time());
        writeJsonLocked($dataDir . 'emoji_laps.json', $laps);
        echo json_encode(array('success' => true, 'lap' => $currentLap + 1));
    } else if ($type === 'all') {
        writeJsonLocked($dataDir . 'emoji_votes.json', array());
        $laps = array('current' => 0, 'history' => array());
        writeJsonLocked($dataDir . 'emoji_laps.json', $laps);
        echo json_encode(array('success' => true));
    }
    exit;
}

// 9. EMOJI METER: Get Vote Log (ADMIN ONLY - WITH USERNAME)
if ($action === 'get_emoji_log' && isset($_SESSION['is_admin'])) {
    $votes = readJsonLocked($dataDir . 'emoji_votes.json');
    $log = array();
    
    foreach ($votes as $ip => $vote) {
        $log[] = array(
            'username' => isset($vote['username']) ? $vote['username'] : 'Anonymous',
            'emoji' => isset($vote['emoji']) ? $vote['emoji'] : 'unknown',
            'time' => isset($vote['time']) ? $vote['time'] : 0,
            'lap' => isset($vote['lap']) ? $vote['lap'] : 0
        );
    }
    
    usort($log, function($a, $b) {
        return $b['time'] - $a['time'];
    });
    
    $log = array_slice($log, 0, 50);
    
    echo json_encode(array('success' => true, 'log' => $log));
    exit;
}

// 10. Satisfaction: Vote
if ($action === 'vote') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $rating = intval($_POST['rating']);
    if ($rating < 1 || $rating > 5) {
        echo json_encode(array('success' => false, 'message' => 'Invalid rating'));
        exit;
    }
    $votes = readJsonLocked($dataDir . 'votes.json');
    if (isset($votes[$ip]) && (time() - $votes[$ip]['time']) < 60) {
        echo json_encode(array('success' => false, 'message' => 'Please wait 1 minute'));
        exit;
    }
    $votes[$ip] = array('rating' => $rating, 'time' => time());
    writeJsonLocked($dataDir . 'votes.json', $votes);
    echo json_encode(array('success' => true));
    exit;
}

// 11. Satisfaction: Get Stats
if ($action === 'get_stats') {
    $votes = readJsonLocked($dataDir . 'votes.json');
    $total = count($votes);
    $sum = 0;
    foreach($votes as $v) $sum += $v['rating'];
    $avg = $total > 0 ? round($sum / $total, 1) : 0;
    echo json_encode(array('average' => $avg, 'count' => $total));
    exit;
}

// 12. Admin: Reset Word Cloud
if ($action === 'reset' && isset($_SESSION['is_admin'])) {
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    if ($type === 'words') writeJsonLocked($dataDir . 'words.json', array());
    if ($type === 'votes') writeJsonLocked($dataDir . 'votes.json', array());
    echo json_encode(array('success' => true));
    exit;
}

// 13. Admin: Check Session
if ($action === 'check_session') {
    echo json_encode(array('is_admin' => isset($_SESSION['is_admin'])));
    exit;
}

// Default response
echo json_encode(array('error' => 'Unknown action'));
?>