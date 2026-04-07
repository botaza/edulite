<?php
// File 2 of 8: api.php - COMPLETE WITH MODULE CONFIG + INFO TEXT + SENTENCES CLOUD + SNAPSHOTS + PDF PINNING
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
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/data/upload_errors.log'); // Debug log
session_start();
header('Content-Type: application/json');

$dataDir = __DIR__ . '/data/';
$adminPassHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

// Ensure data directory exists and is writable
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}
// Quick write-permission check
if (!is_writable($dataDir)) {
    error_log("CRITICAL: data/ directory is not writable: " . $dataDir);
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

// ============================================================================
// Persistent Emoji Totals Helpers (for accumulating lap-by-lap stats)
// ============================================================================

// Helper: Get persistent emoji totals (all-time accumulated across completed laps)
function getEmojiTotals($dataDir) {
    $file = $dataDir . 'emoji_totals.json';
    if (!file_exists($file)) {
        return array('done' => 0, 'unsure' => 0, 'pain' => 0, 'happy' => 0, 'help' => 0, 'total' => 0, 'lapsCompleted' => 0);
    }
    $data = readJsonLocked($file);
    // Merge with defaults to ensure all keys exist
    return array_merge(
        array('done' => 0, 'unsure' => 0, 'pain' => 0, 'happy' => 0, 'help' => 0, 'total' => 0, 'lapsCompleted' => 0),
        $data
    );
}

// Helper: Save persistent emoji totals
function saveEmojiTotals($dataDir, $totals) {
    return writeJsonLocked($dataDir . 'emoji_totals.json', $totals);
}

// Helper: Calculate stats for a specific lap from votes array
function getCurrentLapStats($votes, $targetLap) {
    $stats = array('done' => 0, 'unsure' => 0, 'pain' => 0, 'happy' => 0, 'help' => 0, 'total' => 0);
    foreach ($votes as $vote) {
        $emoji = isset($vote['emoji']) ? $vote['emoji'] : '';
        $voteLap = isset($vote['lap']) ? $vote['lap'] : 1;
        if ($voteLap == $targetLap && isset($stats[$emoji])) {
            $stats[$emoji]++;
            $stats['total']++;
        }
    }
    return $stats;
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

// ============================================================================
// SENTENCES CLOUD MODULE - NEW ENDPOINTS
// ============================================================================

// 2a. Sentences Cloud: Submit Sentence (NO character limit)
if ($action === 'add_sentence') {
    $sentence = strip_tags($_POST['sentence'] ?? '');
    $user = strip_tags($_POST['username'] ?? '');
    // Only require non-empty, no maxlength restriction
    if (strlen($sentence) > 0) {
        $normalized = strtolower(trim($sentence));
        $sentences = readJsonLocked($dataDir . 'sentences.json');
        $found = false;
        foreach ($sentences as &$item) {
            if (strtolower($item['sentence']) === $normalized) {
                $item['count'] = (isset($item['count']) ? $item['count'] : 1) + 1;
                $item['lastTime'] = time();
                if (!isset($item['users'])) $item['users'] = array();
                $item['users'][] = $user;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $sentences[] = array(
                'sentence' => $normalized, 
                'display' => $sentence, 
                'count' => 1,
                'firstTime' => time(), 
                'lastTime' => time(), 
                'users' => array($user)
            );
        }
        // Limit to 100 unique sentences to prevent database bloat
        if (count($sentences) > 100) {
            usort($sentences, function($a, $b) { return $b['lastTime'] - $a['lastTime']; });
            $sentences = array_slice($sentences, 0, 100);
        }
        writeJsonLocked($dataDir . 'sentences.json', $sentences);
    }
    echo json_encode(array('success' => true));
    exit;
}

// 2b. Sentences Cloud: Get Sentences
if ($action === 'get_sentences') {
    $sentences = readJsonLocked($dataDir . 'sentences.json');
    usort($sentences, function($a, $b) {
        $countA = isset($a['count']) ? $a['count'] : 1;
        $countB = isset($b['count']) ? $b['count'] : 1;
        if ($countB !== $countA) return $countB - $countA;
        return $b['lastTime'] - $a['lastTime'];
    });
    echo json_encode($sentences);
    exit;
}

// 2c. Sentences Cloud: Delete Sentence (Admin only)
if ($action === 'delete_sentence' && isset($_SESSION['is_admin'])) {
    $sentenceToDelete = isset($_POST['sentence']) ? strtolower(trim($_POST['sentence'])) : '';
    if ($sentenceToDelete) {
        $sentences = readJsonLocked($dataDir . 'sentences.json');
        $filtered = array();
        foreach ($sentences as $item) {
            if (strtolower($item['sentence']) !== $sentenceToDelete) $filtered[] = $item;
        }
        writeJsonLocked($dataDir . 'sentences.json', $filtered);
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Invalid sentence'));
    }
    exit;
}

// ============================================================================
// CLASS NAME & SNAPSHOTS & EXPORT MODULE - NEW ENDPOINTS
// ============================================================================

// Get Class Config
if ($action === 'get_class_config') {
    $config = readJsonLocked($dataDir . 'class_config.json');
    echo json_encode(array(
        'success' => true,
        'class_name' => $config['class_name'] ?? ''
    ));
    exit;
}

// Save Class Name (Admin only)
if ($action === 'save_class_config' && isset($_SESSION['is_admin'])) {
    $className = isset($_POST['class_name']) ? trim(strip_tags($_POST['class_name'])) : '';
    $config = array(
        'class_name' => $className,
        'last_updated' => time()
    );
    $success = writeJsonLocked($dataDir . 'class_config.json', $config);
    echo json_encode(array('success' => $success));
    exit;
}

// Create Snapshot (Admin only) - NOW INCLUDES ACTIVE USERS
if ($action === 'create_snapshot' && isset($_SESSION['is_admin'])) {
    $words = readJsonLocked($dataDir . 'words.json');
    $sentences = readJsonLocked($dataDir . 'sentences.json');
    $classConfig = readJsonLocked($dataDir . 'class_config.json');
    $className = $classConfig['class_name'] ?? 'Unnamed Class';
    
    // Capture active users
    $usersRaw = readJsonLocked($dataDir . 'active_users.json');
    $activeUsers = array();
    foreach ($usersRaw as $ip => $uData) {
        $activeUsers[] = array(
            'username' => $uData['username'] ?? 'Anonymous',
            'ip' => $uData['ip'] ?? $ip,
            'lastActive' => $uData['lastActive'] ?? 0
        );
    }
    
    $snapId = 'snap_' . time();
    $snap = array(
        'id' => $snapId,
        'timestamp' => date('Y-m-d H:i:s'),
        'class_name' => $className,
        'word_count' => count($words),
        'sentence_count' => count($sentences),
        'active_users_count' => count($activeUsers),
        'data' => array(
            'words' => $words, 
            'sentences' => $sentences,
            'active_users' => $activeUsers
        )
    );
    
    $snaps = readJsonLocked($dataDir . 'snapshots.json');
    if (!is_array($snaps)) $snaps = array();
    $snaps[] = $snap;
    writeJsonLocked($dataDir . 'snapshots.json', $snaps);
    
    echo json_encode(array('success' => true, 'id' => $snapId));
    exit;
}

// Get Snapshots List (Admin only)
if ($action === 'get_snapshots' && isset($_SESSION['is_admin'])) {
    $snaps = readJsonLocked($dataDir . 'snapshots.json');
    if (!is_array($snaps)) $snaps = array();
    // Return metadata only to keep response light
    $list = array_map(function($s) {
        return array(
            'id' => $s['id'], 
            'timestamp' => $s['timestamp'], 
            'class_name' => $s['class_name'], 
            'word_count' => $s['word_count'], 
            'sentence_count' => $s['sentence_count'],
            'active_users_count' => isset($s['active_users_count']) ? $s['active_users_count'] : 0
        );
    }, $snaps);
    echo json_encode(array('success' => true, 'snapshots' => $list));
    exit;
}

// Delete Snapshot(s) (Admin only)
if ($action === 'delete_snapshot' && isset($_SESSION['is_admin'])) {
    $snaps = readJsonLocked($dataDir . 'snapshots.json');
    if (!is_array($snaps)) $snaps = array();
    
    $idsToDelete = isset($_POST['selected_ids']) ? json_decode($_POST['selected_ids'], true) : array();
    if (empty($idsToDelete)) {
        echo json_encode(array('success' => false, 'message' => 'No snapshots selected'));
        exit;
    }
    
    // Filter out the snapshots to delete
    $filtered = array_filter($snaps, function($snap) use ($idsToDelete) {
        return !in_array($snap['id'], $idsToDelete);
    });
    // Re-index array to keep it clean
    $filtered = array_values($filtered);
    
    writeJsonLocked($dataDir . 'snapshots.json', $filtered);
    echo json_encode(array('success' => true, 'remaining' => count($filtered)));
    exit;
}

// Export Snapshot(s) as Multi-Section CSV (Admin only)
if ($action === 'export_snapshot' && isset($_SESSION['is_admin'])) {
    $snaps = readJsonLocked($dataDir . 'snapshots.json');
    if (!is_array($snaps)) $snaps = array();
    
    $exportScope = $_POST['scope'] ?? 'all';
    $selectedIds = isset($_POST['selected_ids']) ? json_decode($_POST['selected_ids'], true) : array();
    
    $targetSnaps = array();
    if ($exportScope === 'all') {
        $targetSnaps = $snaps;
    } else {
        foreach ($snaps as $s) {
            if (in_array($s['id'], $selectedIds)) $targetSnaps[] = $s;
        }
    }
    
    if (empty($targetSnaps)) {
        echo json_encode(array('success' => false, 'message' => 'No snapshots found to export'));
        exit;
    }
    
    // --- FILENAME LOGIC ---
    $classConfig = readJsonLocked($dataDir . 'class_config.json');
    $className = $classConfig['class_name'] ?? 'Class';
    $safeClassName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $className);
    if (empty($safeClassName)) $safeClassName = 'Export';
    $timestamp = date('Y-m-d_His');
    $filename = $safeClassName . '_' . $timestamp . '.csv';
    // ------------------------

    // Generate CSV with UTF-8 BOM for Excel compatibility
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    
    // Output UTF-8 BOM
    echo chr(0xEF) . chr(0xBB) . chr(0xBF);
    
    $output = fopen('php://output', 'w');
    
    // SECTION 1: SNAPSHOT METADATA
    fputcsv($output, array('=== SNAPSHOT METADATA ==='));
    fputcsv($output, array('Snapshot ID', 'Class Name', 'Timestamp', 'Word Count', 'Sentence Count', 'Active Users'));
    foreach ($targetSnaps as $snap) {
        fputcsv($output, array(
            $snap['id'],
            $snap['class_name'],
            $snap['timestamp'],
            $snap['word_count'],
            $snap['sentence_count'],
            isset($snap['active_users_count']) ? $snap['active_users_count'] : 0
        ));
    }
    fwrite($output, "\n"); // Separator

    // SECTION 2: WORDS
    fputcsv($output, array('=== WORDS LIST ==='));
    fputcsv($output, array('Snapshot ID', 'Word', 'Count', 'Submitted By', 'First Seen', 'Last Seen'));
    foreach ($targetSnaps as $snap) {
        $sid = $snap['id'];
        if (isset($snap['data']['words'])) {
            foreach ($snap['data']['words'] as $w) {
                fputcsv($output, array(
                    $sid,
                    $w['display'] ?? $w['word'],
                    $w['count'] ?? 1,
                    isset($w['users']) ? implode('; ', array_unique($w['users'])) : '',
                    isset($w['firstTime']) ? date('Y-m-d H:i:s', $w['firstTime']) : '',
                    isset($w['lastTime']) ? date('Y-m-d H:i:s', $w['lastTime']) : ''
                ));
            }
        }
    }
    fwrite($output, "\n"); // Separator

    // SECTION 3: SENTENCES
    fputcsv($output, array('=== SENTENCES LIST ==='));
    fputcsv($output, array('Snapshot ID', 'Sentence', 'Count', 'Submitted By', 'First Seen', 'Last Seen'));
    foreach ($targetSnaps as $snap) {
        $sid = $snap['id'];
        if (isset($snap['data']['sentences'])) {
            foreach ($snap['data']['sentences'] as $s) {
                fputcsv($output, array(
                    $sid,
                    $s['display'] ?? $s['sentence'],
                    $s['count'] ?? 1,
                    isset($s['users']) ? implode('; ', array_unique($s['users'])) : '',
                    isset($s['firstTime']) ? date('Y-m-d H:i:s', $s['firstTime']) : '',
                    isset($s['lastTime']) ? date('Y-m-d H:i:s', $s['lastTime']) : ''
                ));
            }
        }
    }
    fwrite($output, "\n"); // Separator

    // SECTION 4: ACTIVE USERS
    fputcsv($output, array('=== ACTIVE USERS AT SNAPSHOT ==='));
    fputcsv($output, array('Snapshot ID', 'Username', 'IP', 'Last Active'));
    foreach ($targetSnaps as $snap) {
        $sid = $snap['id'];
        if (isset($snap['data']['active_users'])) {
            foreach ($snap['data']['active_users'] as $u) {
                fputcsv($output, array(
                    $sid,
                    $u['username'] ?? 'Anonymous',
                    $u['ip'] ?? '',
                    isset($u['lastActive']) ? date('Y-m-d H:i:s', $u['lastActive']) : ''
                ));
            }
        }
    }
    
    fclose($output);
    exit; // Critical: stop further JSON output
}

// ============================================================================
// EMOJI METER: Submit Vote
// ============================================================================
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

// 6. EMOJI METER: Get Stats - Returns persistent all-time totals
if ($action === 'get_emoji_stats') {
    $votes = readJsonLocked($dataDir . 'emoji_votes.json');
    $currentLap = getCurrentLap($dataDir);
    
    // Get persistent all-time totals (accumulated from completed laps)
    $allTime = getEmojiTotals($dataDir);
    
    // Calculate current lap stats from live votes
    $currentLapStats = getCurrentLapStats($votes, $currentLap);
    
    echo json_encode(array(
        'success' => true,
        'allTime' => $allTime,           // Persistent accumulated totals
        'currentLap' => $currentLapStats, // Live stats for current lap only
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

// 8. EMOJI METER: Reset Votes / New Lap - FIXED: properly resets all-time stats on "all"
if ($action === 'reset_emoji' && isset($_SESSION['is_admin'])) {
    $type = isset($_POST['type']) ? $_POST['type'] : 'all';
   
    if ($type === 'lap') {
        // Get current lap and votes
        $laps = readJsonLocked($dataDir . 'emoji_laps.json');
        $currentLap = isset($laps['current']) ? $laps['current'] : 1;
        $votes = readJsonLocked($dataDir . 'emoji_votes.json');
        
        // Calculate stats for the lap that's ending
        $lapStats = getCurrentLapStats($votes, $currentLap);
        
        // Add to persistent all-time totals
        $totals = getEmojiTotals($dataDir);
        foreach (array('done', 'unsure', 'pain', 'happy', 'help') as $emoji) {
            $totals[$emoji] += $lapStats[$emoji];
        }
        $totals['total'] += $lapStats['total'];
        $totals['lapsCompleted'] = ($totals['lapsCompleted'] ?? 0) + 1;
        saveEmojiTotals($dataDir, $totals);
        
        // Increment lap counter and save history
        $laps['current'] = $currentLap + 1;
        if (!isset($laps['history'])) $laps['history'] = array();
        $laps['history'][] = array(
            'lap' => $currentLap, 
            'time' => time(), 
            'stats' => $lapStats
        );
        writeJsonLocked($dataDir . 'emoji_laps.json', $laps);
        
        echo json_encode(array(
            'success' => true, 
            'lap' => $laps['current'],
            'lapStats' => $lapStats,
            'allTime' => $totals
        ));
    } else if ($type === 'all') {
        // ✅ FIXED: Reset ALL - Clear votes AND reset persistent all-time totals to zero
        $zeroTotals = array('done' => 0, 'unsure' => 0, 'pain' => 0, 'happy' => 0, 'help' => 0, 'total' => 0, 'lapsCompleted' => 0);
        saveEmojiTotals($dataDir, $zeroTotals);
        
        writeJsonLocked($dataDir . 'emoji_votes.json', array());
        $laps = array('current' => 1, 'history' => array());
        writeJsonLocked($dataDir . 'emoji_laps.json', $laps);
        
        echo json_encode(array('success' => true, 'allTime' => $zeroTotals));
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
    $cutoffTime = time() - (5 * 60);  // 300 seconds = 5 minutes
   
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

// ============================================================================
// 13. LESSON MODE: Upload PDF - HARDENED VERSION
// ============================================================================
if ($action === 'upload_pdf' && isset($_SESSION['is_admin'])) {
    // --- Step 1: Check PHP upload errors ---
    if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit (upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form limit (MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE    => 'No file was sent',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION  => 'Upload stopped by PHP extension'
        ];
        $errorCode = $_FILES['pdf']['error'] ?? -1;
        $errorMsg = $uploadErrors[$errorCode] ?? 'Unknown upload error (code: ' . $errorCode . ')';
        error_log("PDF upload failed - Error $errorCode: $errorMsg");
        echo json_encode(['success' => false, 'message' => $errorMsg]);
        exit;
    }

    // --- Step 2: Validate extension ---
    $originalName = basename($_FILES['pdf']['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed']);
        exit;
    }

    // --- Step 3: Validate MIME type using fileinfo ---
    if (!function_exists('finfo_open')) {
        error_log("WARNING: PHP fileinfo extension not enabled - skipping MIME validation");
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['pdf']['tmp_name']);
        finfo_close($finfo);
        // Accept standard PDF MIME types
        if (!in_array($mimeType, ['application/pdf', 'application/x-pdf', 'application/acrobat'])) {
            error_log("PDF upload rejected - Invalid MIME type: $mimeType");
            echo json_encode(['success' => false, 'message' => 'File is not a valid PDF (MIME: ' . $mimeType . ')']);
            exit;
        }
    }

    // --- Step 4: Validate PDF magic bytes (%PDF-) ---
    $handle = fopen($_FILES['pdf']['tmp_name'], 'rb');
    if (!$handle) {
        echo json_encode(['success' => false, 'message' => 'Could not read uploaded file']);
        exit;
    }
    $header = fread($handle, 8);
    fclose($handle);
    if (strlen($header) < 4 || substr($header, 0, 4) !== '%PDF') {
        error_log("PDF upload rejected - Invalid magic bytes: " . bin2hex(substr($header, 0, 8)));
        echo json_encode(['success' => false, 'message' => 'File is corrupted or not a valid PDF']);
        exit;
    }

    // --- Step 5: Generate unique, safe filename ---
    $newFilename = 'lesson_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
    $uploadPath = $dataDir . $newFilename;

    // --- Step 6: Move and verify file ---
    if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadPath)) {
        error_log("move_uploaded_file failed for: $originalName");
        echo json_encode(['success' => false, 'message' => 'Server failed to save the file']);
        exit;
    }

    // Post-upload integrity checks
    $fileSize = filesize($uploadPath);
    if ($fileSize < 100) { // PDFs are rarely <100 bytes
        unlink($uploadPath);
        echo json_encode(['success' => false, 'message' => 'Uploaded file is too small to be valid']);
        exit;
    }

    // Re-check magic bytes on saved file (defense in depth)
    $savedHeader = file_get_contents($uploadPath, false, null, 0, 5);
    if (substr($savedHeader, 0, 4) !== '%PDF') {
        unlink($uploadPath);
        echo json_encode(['success' => false, 'message' => 'Saved file failed integrity check']);
        exit;
    }

    // --- Step 7: Save metadata ---
    $pdfInfo = [
        'filename'   => $newFilename,
        'original'   => $originalName,
        'uploadTime' => time(),
        'uploadedBy' => 'admin',
        'size'       => $fileSize,
        'mime'       => $mimeType ?? 'unknown'
    ];
    writeJsonLocked($dataDir . 'lesson_pdf.json', $pdfInfo);

    error_log("PDF uploaded successfully: $originalName ($fileSize bytes) -> $newFilename");
    echo json_encode(['success' => true, 'filename' => $newFilename, 'size' => $fileSize]);
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
            'uploadTime' => $pdfInfo['uploadTime'] ?: 0,
            'size' => $pdfInfo['size'] ?? filesize($dataDir . $pdfInfo['filename'])
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
            if (!unlink($filePath)) {
                error_log("Failed to delete PDF file: $filePath");
            }
        }
        writeJsonLocked($dataDir . 'lesson_pdf.json', array());
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'message' => 'No PDF found'));
    }
    exit;
}

// ============================================================================
// PDF PINNING CONFIG (NEW ENDPOINTS)
// ============================================================================

if ($action === 'get_pdf_config') {
    $config = readJsonLocked($dataDir . 'pdf_config.json');
    echo json_encode(array(
        'success' => true,
        'pinned_page' => isset($config['pinned_page']) ? intval($config['pinned_page']) : 0
    ));
    exit;
}

if ($action === 'set_pdf_config' && isset($_SESSION['is_admin'])) {
    $page = intval($_POST['page'] ?? 0);
    $config = array('pinned_page' => max(0, $page), 'updated' => time());
    $success = writeJsonLocked($dataDir . 'pdf_config.json', $config);
    echo json_encode(array('success' => $success, 'pinned_page' => $config['pinned_page']));
    exit;
}

// ==================== INFO TEXT MODULE ====================

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
            'sentences_cloud' => false,
            'pdf_viewer' => false,
            'emoji_meter' => true,
            'qr_link' => false,
            'info_text' => false
        );
        writeJsonLocked($dataDir . 'modules_config.json', $config);
    } else {
        if (!isset($config['info_text'])) {
            $config['info_text'] = false;
            writeJsonLocked($dataDir . 'modules_config.json', $config);
        }
        if (!isset($config['qr_link'])) {
            $config['qr_link'] = false;
            writeJsonLocked($dataDir . 'modules_config.json', $config);
        }
        if (!isset($config['sentences_cloud'])) {
            $config['sentences_cloud'] = false;
            writeJsonLocked($dataDir . 'modules_config.json', $config);
        }
    }
   
    echo json_encode(array('success' => true, 'config' => $config));
    exit;
}

// 17. MODULES: Update Config
if ($action === 'update_modules_config' && isset($_SESSION['is_admin'])) {
    $config = array(
        'wordcloud'       => isset($_POST['wordcloud'])       ? $_POST['wordcloud']       === 'true' : false,
        'sentences_cloud' => isset($_POST['sentences_cloud']) ? $_POST['sentences_cloud'] === 'true' : false,
        'pdf_viewer'      => isset($_POST['pdf_viewer'])      ? $_POST['pdf_viewer']      === 'true' : false,
        'emoji_meter'     => isset($_POST['emoji_meter'])     ? $_POST['emoji_meter']     === 'true' : false,
        'qr_link'         => isset($_POST['qr_link'])         ? $_POST['qr_link']         === 'true' : false,
        'info_text'       => isset($_POST['info_text'])       ? $_POST['info_text']       === 'true' : false
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
    if ($type === 'sentences') writeJsonLocked($dataDir . 'sentences.json', array());
    if ($type === 'cloud') {
        writeJsonLocked($dataDir . 'words.json', array());
        writeJsonLocked($dataDir . 'sentences.json', array());
    }
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