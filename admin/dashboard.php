<?php
// admin/dashboard.php
include("../option.php");
session_start();
require '../functions.php';

// 管理者がログインしていない場合、ログインページにリダイレクト
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$stall = getStallById($_SESSION['admin_id']);
if (!$stall) {
    die("無効な管理者IDです。");
}

// キューの情報取得
$data = readData();
$current_queue = [];
foreach ($data['stalls'] as $s) {
    if ($s['id'] == $stall['id']) {
        $current_queue = $s['queue'];
        break;
    }
}

// ユーザーをキューから削除する処理
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_user'])) {
    $username_to_remove = $_POST['username'];
    $removed = removeUserFromQueue($stall['id'], $username_to_remove);
    if ($removed) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "ユーザーの削除に失敗しました。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>管理者ダッシュボード - <?= htmlspecialchars($stall['name'], ENT_QUOTES, 'UTF-8') ?> | <? echo $waitlist_name; ?></title>
    <link rel="stylesheet" href="../css/bulma.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title"><?= htmlspecialchars($stall['name'], ENT_QUOTES, 'UTF-8') ?> 管理者ダッシュボード</h1>
            <p><?= htmlspecialchars($stall['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <hr>
            <h2 class="subtitle">現在のキュー</h2>
            <?php if (isset($error)): ?>
                <p class="help is-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (empty($current_queue)): ?>
                <p>現在、待っているユーザーはいません。</p>
            <?php else: ?>
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ユーザー名</th>
                            <th>アクション</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current_queue as $index => $username): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
                                        <button class="button is-danger is-small" type="submit" name="remove_user" onclick="return confirm('本当に削除しますか？');">削除</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <hr>
            <a href="logout.php" class="button is-link">ログアウト</a>
        </div>
    </section>
</body>
</html>
