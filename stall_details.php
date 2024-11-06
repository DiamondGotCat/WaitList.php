<?php include("option.php"); ?>
<?php
// stall_details.php
session_start();
require 'functions.php';

// ユーザーが名前を入力していない場合、リダイレクト
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// 屋台IDの取得
$stall_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stall = getStallById($stall_id);

if (!$stall) {
    die("無効な屋台IDです。");
}

// キューの情報取得
$data = readData();
$current_queue = 0;
$wait_time = 0;
$confidence = $stall['confidence'];

foreach ($data['stalls'] as $s) {
    if ($s['id'] == $stall_id) {
        $current_queue = count($s['queue']);
        $wait_time = $s['wait_time_per_person'] * $current_queue;
        break;
    }
}

// キューに追加
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join'])) {
    $username = $_SESSION['username'];
    $added = addUserToQueue($stall_id, $username);
    if ($added) {
        header("Location: stall_details.php?id=$stall_id");
        exit();
    } else {
        $error = "既に順番待ちに登録されています。";
    }
}

// キューからキャンセル
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel'])) {
    $username = $_SESSION['username'];
    $removed = removeUserFromQueue($stall_id, $username);
    if ($removed) {
        header("Location: select_stall.php");
        exit();
    } else {
        $error = "順番待ちから削除できませんでした。";
    }
}

// ユーザーが現在キューに入っているか確認
$is_in_queue = in_array($_SESSION['username'], $stall['queue']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($stall['name'], ENT_QUOTES, 'UTF-8') ?> - <? echo $waitlist_name; ?></title>
    <link rel="stylesheet" href="css/bulma.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title"><?= htmlspecialchars($stall['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars($stall['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <hr>
            <p><strong>待っている人数:</strong> <?= $current_queue ?></p>
            <p><strong>予想待ち時間:</strong> <?= $wait_time ?> 分</p>
            <p><strong>待ち時間に対する自信:</strong> <?= htmlspecialchars($confidence, ENT_QUOTES, 'UTF-8') ?></p>
            <hr>
            <?php if (isset($error)): ?>
                <p class="help is-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if ($is_in_queue): ?>
                <form method="post">
                    <button class="button is-danger" type="submit" name="cancel">キャンセル</button>
                </form>
            <?php else: ?>
                <form method="post">
                    <button class="button is-primary" type="submit" name="join">順番待ちに追加</button>
                </form>
            <?php endif; ?>
            <br>
            <a href="select_stall.php" class="button is-link">戻る</a>
        </div>
    </section>
</body>
</html>
