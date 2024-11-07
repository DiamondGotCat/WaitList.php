<?php
// api.php

header('Content-Type: application/json');

// データファイルのパス
$data_file = './data.json';

// UUIDの取得
$uuid = isset($_GET['uuid']) ? $_GET['uuid'] : '';

// アクションの取得
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (!$uuid) {
    echo json_encode(['success' => false, 'error' => 'UUIDが提供されていません。']);
    exit;
}

// データファイルの読み込み
if (!file_exists($data_file)) {
    file_put_contents($data_file, json_encode(['stalls' => []], JSON_PRETTY_PRINT));
}

$data = json_decode(file_get_contents($data_file), true);

// 一意の予約IDを生成する関数
function generateReservationId($length = 8) {
    return strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
}

// 日付と時刻のバリデーション関数
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// アクションごとの処理
switch ($action) {
    case 'getStalls':
        $stalls = isset($data['stalls']) ? $data['stalls'] : [];
        echo json_encode(['success' => true, 'stalls' => $stalls]);
        break;

    case 'addReservationNow':
        $stall_id = isset($_GET['stall_id']) ? $_GET['stall_id'] : '';
        if (!$stall_id) {
            echo json_encode(['success' => false, 'error' => '屋台IDが提供されていません。']);
            exit;
        }

        // 屋台の検索
        foreach ($data['stalls'] as &$stall) {
            if ($stall['id'] == $stall_id) {
                // 今すぐ予約の追加
                $reservation = [
                    'id' => generateReservationId(),
                    'uuid' => $uuid,
                    'type' => 'now',
                    'date' => date('Y-m-d'),
                    'time' => date('H:i'),
                    'status' => 'waiting'
                ];
                if (!isset($stall['reservations'])) {
                    $stall['reservations'] = [];
                }
                $stall['reservations'][] = $reservation;
                file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'error' => '指定された屋台が見つかりません。']);
        break;

    case 'addReservationLater':
        $stall_id = isset($_GET['stall_id']) ? $_GET['stall_id'] : '';
        $date = isset($_GET['date']) ? $_GET['date'] : '';
        $time = isset($_GET['time']) ? $_GET['time'] : '';

        if (!$stall_id || !$date || !$time) {
            echo json_encode(['success' => false, 'error' => '必要なパラメータが不足しています。']);
            exit;
        }

        // 日付と時刻のバリデーション
        if (!validateDate($date, 'Y-m-d') || !validateDate($time, 'H:i')) {
            echo json_encode(['success' => false, 'error' => '無効な日付または時刻です。']);
            exit;
        }

        // 屋台の検索
        foreach ($data['stalls'] as &$stall) {
            if ($stall['id'] == $stall_id) {
                // 後で予約の追加
                $reservation = [
                    'id' => generateReservationId(),
                    'uuid' => $uuid,
                    'type' => 'later',
                    'date' => $date,
                    'time' => $time,
                    'status' => 'waiting'
                ];
                if (!isset($stall['reservations'])) {
                    $stall['reservations'] = [];
                }
                $stall['reservations'][] = $reservation;
                file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'error' => '指定された屋台が見つかりません。']);
        break;

    case 'getMyReservations':
        $my_reservations = [];
        foreach ($data['stalls'] as $stall) {
            if (isset($stall['reservations'])) {
                foreach ($stall['reservations'] as $reservation) {
                    if ($reservation['uuid'] === $uuid) {
                        $my_reservations[] = [
                            'id' => $reservation['id'],
                            'stall_id' => $stall['id'],
                            'status' => $reservation['status']
                        ];
                    }
                }
            }
        }
        echo json_encode(['success' => true, 'reservations' => $my_reservations]);
        break;

    case 'completeReservation':
        $reservation_id = isset($_GET['reservation_id']) ? $_GET['reservation_id'] : '';
        if (!$reservation_id) {
            echo json_encode(['success' => false, 'error' => '予約IDが提供されていません。']);
            exit;
        }

        // 予約の完了処理
        $found = false;
        foreach ($data['stalls'] as &$stall) {
            if (isset($stall['reservations'])) {
                foreach ($stall['reservations'] as &$reservation) {
                    if ($reservation['id'] === $reservation_id && $reservation['uuid'] === $uuid) {
                        if ($reservation['status'] !== 'waiting') {
                            echo json_encode(['success' => false, 'error' => '予約のステータスが変更できません。']);
                            exit;
                        }
                        $reservation['status'] = 'completed';
                        $found = true;
                        break 2; // ループを抜ける
                    }
                }
            }
        }
        if ($found) {
            file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => '指定された予約が見つかりません。']);
        }
        break;

    case 'cancelReservation':
        $reservation_id = isset($_GET['reservation_id']) ? $_GET['reservation_id'] : '';
        if (!$reservation_id) {
            echo json_encode(['success' => false, 'error' => '予約IDが提供されていません。']);
            exit;
        }

        // 予約のキャンセル処理
        $found = false;
        foreach ($data['stalls'] as &$stall) {
            if (isset($stall['reservations'])) {
                foreach ($stall['reservations'] as &$reservation) {
                    if ($reservation['id'] === $reservation_id && $reservation['uuid'] === $uuid) {
                        if ($reservation['status'] !== 'waiting') {
                            echo json_encode(['success' => false, 'error' => '予約のステータスが変更できません。']);
                            exit;
                        }
                        $reservation['status'] = 'cancelled';
                        $found = true;
                        break 2; // ループを抜ける
                    }
                }
            }
        }
        if ($found) {
            file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => '指定された予約が見つかりません。']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => '無効なアクションです。']);
        break;
}
?>