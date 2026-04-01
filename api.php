<?php
// File 2 of 8: api.php - COMPLETE FINAL VERSION

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

// Helper: Get current lap number (STARTS AT 1)
function getCurrentLap($dataDir) {
    $laps = readJsonLocked($dataDir . 'emoji_laps.json');
    return isset($laps['current']) ? $laps['current'] : 1;
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

// 5. EMOJI METER: Submit Vote (ARRAY-BASED - MULTIPLE VOTES PER USER)
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
    
    // 60 second cooldown (check last vote from this IP)
    $lastVoteTime = 0;
    foreach ($votes as $vote) {
        if (isset($vote['ip']) && $vote['ip'] === $ip && $vote['time'] > $lastVoteTime) {
            $lastVoteTime = $vote['time'];
        }
    }
    
    if ($lastVoteTime > 0 && (time() - $lastVoteTime) < 60) {
        $waitTime = 60 - (time() - $lastVoteTime);
        echo json_encode(array(
            'success' => false, 
            'message' => 'Please wait ' . $waitTime . ' seconds',
            'waitTime' => $waitTime
        ));
        exit;
    }
    
    // Add new vote to array (keeps all historical votes)
    $votes[] = array(
        'ip' => $ip,
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

// 6. EMOJI METER: Get Stats (ALL-TIME + CURRENT LAP)
if ($action === 'get_emoji_stats') {
    $votes = readJsonLocked($dataDir . 'emoji_votes.json');
    $currentLap = getCurrentLap($dataDir);
    
    // ALL-TIME stats: Count ALL votes regardless of lap
    $allTime = array(
        'done' => 0, 'unsure' => 0, 'pain' => 0, 'happy' => 0, 'help' => 0,
        'total' => 0
    );
    
    // CURRENT LAP stats: Count only votes from current lap
    $currentLapStats = array('done' => 0, 'unsure' => 0, 'pain' => 0, 'happy' => 0, 'help' => 0, 'total' => 0);
    
    foreach ($votes as $vote) {
        $emoji = isset($vote['emoji']) ? $vote['emoji'] : '';
        $voteLap = isset($vote['lap']) ? $vote['lap'] : 1;
        
        // Count for ALL-TIME (always)
        if (isset($allTime[$emoji])) {
            $allTime[$emoji]++;
            $allTime['total']++;
        }
        
        // Count for CURRENT LAP only if vote matches current lap
        if ($voteLap == $currentLap && isset($currentLapStats[$emoji])) {
            $currentLapStats[$emoji]++;
            $currentLapStats['total']++;
        }
    }
    
    echo json_encode(array(
        'success' => true,
        'allTime' => $allTime,
        'currentLap' => $currentLapStats,
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
        // Start NEW lap (increment counter, keep all historical votes)
        $laps = readJsonLocked($dataDir . 'emoji_laps.json');
        $currentLap = isset($laps['current']) ? $laps['current'] : 1;
        $laps['current'] = $currentLap + 1;
        if (!isset($laps['history'])) $laps['history'] = array();
        $laps['history'][] = array('lap' => $laps['current'], 'time' => time());
        writeJsonLocked($dataDir . 'emoji_laps.json', $laps);
        echo json_encode(array('success' => true, 'lap' => $laps['current']));
    } else if ($type === 'all') {
        // Reset EVERYTHING (delete all votes, reset lap to 1)
        writeJsonLocked($dataDir . 'emoji_votes.json', array());
        $laps = array('current' => 1, 'history' => array());
        writeJsonLocked($dataDir . 'emoji_laps.json', $laps);
        echo json_encode(array('success' => true));
    }
    exit;
}

// 9. EMOJI METER: Get Vote Log (ADMIN ONLY - WITH USERNAME)
if ($action === 'get_emoji_log' && isset($_SESSION['is_admin'])) {
    $votes = readJsonLocked($dataDir . 'emoji_votes.json');
    $log = array();
    
    // Votes are already in array format, just reverse to show newest first
    $log = array_reverse($votes);
    
    // Format each entry
    foreach ($log as &$entry) {
        $entry = array(
            'username' => isset($entry['username']) ? $entry['username'] : 'Anonymous',
            'emoji' => isset($entry['emoji']) ? $entry['emoji'] : 'unknown',
            'time' => isset($entry['time']) ? $entry['time'] : 0,
            'lap' => isset($entry['lap']) ? $entry['lap'] : 1
        );
    }
    
    // Limit to last 100 entries
    $log = array_slice($log, 0, 100);
    
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