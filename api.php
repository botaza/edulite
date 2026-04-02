<?php
// File 2 of 8: api.php - COMPLETE WITH MODULE CONFIG + INFO TEXT
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
    $word = strip_tags($_POST['word'] ?? '');
    $user = strip_tags($_POST['username'] ?? '');
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
                'word' => $normalized, 
                'display' => $word, 
                'count' => 1,
                'firstTime' => time(), 
                'lastTime' => time(), 
                'users' => array($user)
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

// 5. EMOJI METER: Submit Vote
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
   
    $votes[] = array(
        'ip' => $ip,
        'emoji' => $emoji,
        'time' => time(),
        'lap' => getCurrentLap($dataDir),
        'username' => $username
    );
    writeJsonLocked($dataDir . 'emoji_votes.json', $votes);
   
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
   
    $allTime = array('done' => 0, 'unsure' => 0, 'pain' => 0, 'happy' => 0, 'help' => 0, 'total' => 0);
    $currentLapStats = array('done' => 0, 'unsure' => 0, 'pain' => 0, 'happy' => 0, 'help' => 0, 'total' => 0);
   
    foreach ($votes as $vote) {
        $emoji = isset($vote['emoji']) ? $vote['emoji'] : '';
        $voteLap = isset($vote['lap']) ? $vote['lap'] : 1;
       
        if (isset($allTime[$emoji])) {
            $allTime[$emoji]++;
            $allTime['total']++;
        }
       
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
        $laps = readJsonLocked($dataDir . 'emoji_laps.json');
        $currentLap = isset($laps['current']) ? $laps['current'] : 1;
        $laps['current'] = $currentLap + 1;
        if (!isset($laps['history'])) $laps['history'] = array();
        $laps['history'][] = array('lap' => $laps['current'], 'time' => time());
        writeJsonLocked($dataDir . 'emoji_laps.json', $laps);
        echo json_encode(array('success' => true, 'lap' => $laps['current']));
    } else if ($type === 'all') {
        writeJsonLocked($dataDir . 'emoji_votes.json', array());
        $laps = array('current' => 1, 'history' => array());
        writeJsonLocked($dataDir . 'emoji_laps.json', $laps);
        echo json_encode(array('success' => true));
    }
    exit;
}

// 9. EMOJI METER: Get Vote Log
if ($action === 'get_emoji_log' && isset($_SESSION['is_admin'])) {
    $votes = readJsonLocked($dataDir . 'emoji_votes.json');
    $log = array_reverse($votes);
   
    foreach ($log as &$entry) {
        $entry = array(
            'username' => isset($entry['username']) ? $entry['username'] : 'Anonymous',
            'emoji' => isset($entry['emoji']) ? $entry['emoji'] : 'unknown',
            'time' => isset($entry['time']) ? $entry['time'] : 0,
            'lap' => isset($entry['lap']) ? $entry['lap'] : 1
        );
    }
   
    $log = array_slice($log, 0, 100);
   
    echo json_encode(array('success' => true, 'log' => $log));
    exit;
}

// 10. EMOJI METER: Delete Log Entry
if ($action === 'delete_emoji_log' && isset($_SESSION['is_admin'])) {
    $index = isset($_POST['index']) ? intval($_POST['index']) : -1;
    $type = isset($_POST['type']) ? $_POST['type'] : 'single';
   
    $votes = readJsonLocked($dataDir . 'emoji_votes.json');
   
    if ($type === 'all') {
        writeJsonLocked($dataDir . 'emoji_votes.json', array());
        echo json_encode(array('success' => true, 'message' => 'All votes deleted'));
    } else if ($type === 'single' && $index >= 0) {
        $votes = array_reverse($votes);
        if (isset($votes[$index])) {
            array_splice($votes, $index, 1);
            $votes = array_reverse($votes);
            writeJsonLocked($dataDir . 'emoji_votes.json', $votes);
            echo json_encode(array('success' => true, 'message' => 'Vote deleted'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Entry not found'));
        }
    } else {
        echo json_encode(array('success' => false, 'message' => 'Invalid parameters'));
    }
    exit;
}

// 11. USER TRACKING: Log User Login
if ($action === 'log_user_login') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $username = isset($_POST['username']) ? strip_tags($_POST['username']) : 'Anonymous';
   
    if (!$username || $username === 'Anonymous') {
        echo json_encode(array('success' => false, 'message' => 'Username required'));
        exit;
    }
   
    $users = readJsonLocked($dataDir . 'active_users.json');
   
    $users[$ip] = array(
        'username' => $username,
        'ip' => $ip,
        'loginTime' => time(),
        'lastActive' => time()
    );
   
    writeJsonLocked($dataDir . 'active_users.json', $users);
   
    echo json_encode(array('success' => true, 'count' => count($users)));
    exit;
}

// 12. USER TRACKING: Get Unique User Count
if ($action === 'get_user_count') {
    $users = readJsonLocked($dataDir . 'active_users.json');
   
    $activeUsers = array();
    $cutoffTime = time() - (24 * 60 * 60);
   
    foreach ($users as $ip => $userData) {
        if (isset($userData['lastActive']) && $userData['lastActive'] > $cutoffTime) {
            $activeUsers[$ip] = $userData;
        }
    }
   
    if (count($activeUsers) !== count($users)) {
        writeJsonLocked($dataDir . 'active_users.json', $activeUsers);
    }
   
    $uniqueCount = count($activeUsers);
   
    echo json_encode(array(
        'success' => true,
        'count' => $uniqueCount,
        'label' => $uniqueCount . ' user' . ($uniqueCount !== 1 ? 's' : '')
    ));
    exit;
}

// 13. LESSON MODE: Upload PDF
if ($action === 'upload_pdf' && isset($_SESSION['is_admin'])) {
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === 0) {
        $allowed = array('pdf');
        $filename = $_FILES['pdf']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
       
        if (in_array($ext, $allowed)) {
            $newFilename = 'lesson_' . time() . '.' . $ext;
            $uploadPath = $dataDir . $newFilename;
           
            if (move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadPath)) {
                $pdfInfo = array(
                    'filename' => $newFilename,
                    'original' => $filename,
                    'uploadTime' => time(),
                    'uploadedBy' => 'admin'
                );
                writeJsonLocked($dataDir . 'lesson_pdf.json', $pdfInfo);
               
                echo json_encode(array('success' => true, 'filename' => $newFilename));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to save file'));
            }
        } else {
            echo json_encode(array('success' => false, 'message' => 'Only PDF files allowed'));
        }
    } else {
        echo json_encode(array('success' => false, 'message' => 'No file uploaded'));
    }
    exit;
}

// 14. LESSON MODE: Get PDF Info
if ($action === 'get_pdf_info') {
    $pdfInfo = readJsonLocked($dataDir . 'lesson_pdf.json');
   
    if (isset($pdfInfo['filename']) && file_exists($dataDir . $pdfInfo['filename'])) {
        echo json_encode(array(
            'success' => true,
            'hasPdf' => true,
            'filename' => $pdfInfo['filename'],
            'original' => $pdfInfo['original'] ?: 'Lesson.pdf',
            'uploadTime' => $pdfInfo['uploadTime'] ?: 0
        ));
    } else {
        echo json_encode(array('success' => true, 'hasPdf' => false));
    }
    exit;
}

// 15. LESSON MODE: Delete PDF
if ($action === 'delete_pdf' && isset($_SESSION['is_admin'])) {
    $pdfInfo = readJsonLocked($dataDir . 'lesson_pdf.json');
   
    if (isset($pdfInfo['filename'])) {
        $filePath = $dataDir . $pdfInfo['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        writeJsonLocked($dataDir . 'lesson_pdf.json', array());
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'message' => 'No PDF found'));
    }
    exit;
}

// ==================== NEW: INFO TEXT MODULE ====================

// Get Info Text
if ($action === 'get_info_text') {
    $info = readJsonLocked($dataDir . 'info_text.json');
    echo json_encode(array(
        'success' => true,
        'text' => $info['text'] ?? ''
    ));
    exit;
}

// Save Info Text (Admin only)
if ($action === 'save_info_text' && isset($_SESSION['is_admin'])) {
    $text = isset($_POST['text']) ? trim($_POST['text']) : '';
    $info = array('text' => $text, 'updated' => time());
    $success = writeJsonLocked($dataDir . 'info_text.json', $info);
    echo json_encode(array('success' => $success));
    exit;
}

// 16. MODULES: Get Config
if ($action === 'get_modules_config') {
    $config = readJsonLocked($dataDir . 'modules_config.json');
   
    if (empty($config)) {
        $config = array(
            'wordcloud' => true,
            'pdf_viewer' => false,
            'emoji_meter' => true,
            'qr_link' => false,
            'info_text' => false   // NEW
        );
        writeJsonLocked($dataDir . 'modules_config.json', $config);
    } else {
        // Ensure new field exists for backward compatibility
        if (!isset($config['info_text'])) {
            $config['info_text'] = false;
            writeJsonLocked($dataDir . 'modules_config.json', $config);
        }
        if (!isset($config['qr_link'])) {
            $config['qr_link'] = false;
            writeJsonLocked($dataDir . 'modules_config.json', $config);
        }
    }
   
    echo json_encode(array('success' => true, 'config' => $config));
    exit;
}

// 17. MODULES: Update Config
if ($action === 'update_modules_config' && isset($_SESSION['is_admin'])) {
    $config = array(
        'wordcloud'   => isset($_POST['wordcloud'])   ? $_POST['wordcloud']   === 'true' : false,
        'pdf_viewer'  => isset($_POST['pdf_viewer'])  ? $_POST['pdf_viewer']  === 'true' : false,
        'emoji_meter' => isset($_POST['emoji_meter']) ? $_POST['emoji_meter'] === 'true' : false,
        'qr_link'     => isset($_POST['qr_link'])     ? $_POST['qr_link']     === 'true' : false,
        'info_text'   => isset($_POST['info_text'])   ? $_POST['info_text']   === 'true' : false   // NEW
    );
   
    writeJsonLocked($dataDir . 'modules_config.json', $config);
    echo json_encode(array('success' => true, 'config' => $config));
    exit;
}

// 18. Satisfaction: Vote (kept for compatibility)
if ($action === 'vote') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $rating = intval($_POST['rating'] ?? 0);
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

// 19. Satisfaction: Get Stats
if ($action === 'get_stats') {
    $votes = readJsonLocked($dataDir . 'votes.json');
    $total = count($votes);
    $sum = 0;
    foreach($votes as $v) $sum += $v['rating'];
    $avg = $total > 0 ? round($sum / $total, 1) : 0;
    echo json_encode(array('average' => $avg, 'count' => $total));
    exit;
}

// 20. Admin: Reset
if ($action === 'reset' && isset($_SESSION['is_admin'])) {
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    if ($type === 'words') writeJsonLocked($dataDir . 'words.json', array());
    if ($type === 'votes') writeJsonLocked($dataDir . 'votes.json', array());
    echo json_encode(array('success' => true));
    exit;
}

// 21. Admin: Check Session
if ($action === 'check_session') {
    echo json_encode(array('is_admin' => isset($_SESSION['is_admin'])));
    exit;
}

// Default response
echo json_encode(array('error' => 'Unknown action'));
?>