<?php
// functions.php

function readData() {
    $filePath = __DIR__ . '/data/stalls.json'; // 絶対パスを使用
    if (!file_exists($filePath)) {
        die("エラー: データファイルが存在しません。");
    }
    $json = file_get_contents($filePath);
    if ($json === false) {
        die("エラー: データファイルを読み込めませんでした。");
    }
    $data = json_decode($json, true);
    if ($data === null) {
        die("エラー: JSONのデコードに失敗しました。");
    }
    return $data;
}

function writeData($data) {
    $filePath = __DIR__ . '/data/stalls.json'; // 絶対パスを使用
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        die("エラー: データのエンコードに失敗しました。");
    }
    $fp = fopen($filePath, 'w');
    if ($fp === false) {
        die("エラー: データファイルを開けませんでした。");
    }
    if (flock($fp, LOCK_EX)) {  // 排他ロックを取得
        fwrite($fp, $json);
        fflush($fp);
        flock($fp, LOCK_UN);    // ロックを解除
    } else {
        die("エラー: ファイルロックに失敗しました。");
    }
    fclose($fp);
}

function getStallById($id) {
    $data = readData();
    foreach ($data['stalls'] as $stall) {
        if ($stall['id'] == $id) {
            return $stall;
        }
    }
    return null;
}

function authenticateAdmin($username, $password) {
    $data = readData();
    foreach ($data['stalls'] as $stall) {
        if ($stall['admin_username'] === $username) {
            // パスワードをハッシュ化されたパスワードと照合
            if (password_verify($password, $stall['admin_password'])) {
                return $stall['id'];
            }
        }
    }
    return false;
}

function getStallByAdminId($admin_id) {
    $data = readData();
    foreach ($data['stalls'] as $stall) {
        if ($stall['id'] == $admin_id) {
            return $stall;
        }
    }
    return null;
}

function addUserToQueue($stall_id, $username) {
    $data = readData();
    foreach ($data['stalls'] as &$stall) {
        if ($stall['id'] == $stall_id) {
            if (!in_array($username, $stall['queue'])) {
                $stall['queue'][] = $username;
                writeData($data);
                return true;
            }
            return false; // 既にキューに追加されている
        }
    }
    return false; // 屋台が見つからない
}

function removeUserFromQueue($stall_id, $username) {
    $data = readData();
    foreach ($data['stalls'] as &$stall) {
        if ($stall['id'] == $stall_id) {
            $key = array_search($username, $stall['queue']);
            if ($key !== false) {
                unset($stall['queue'][$key]);
                // キューを再インデックス化
                $stall['queue'] = array_values($stall['queue']);
                writeData($data);
                return true;
            }
            return false; // ユーザーがキューに存在しない
        }
    }
    return false; // 屋台が見つからない
}
?>
