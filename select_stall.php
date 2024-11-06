<?php include("option.php"); ?>
<?php
// select_stall.php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

require 'functions.php';
$data = readData();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>リストを選択 - <? echo $waitlist_name; ?> | WaitList.php</title>
    <link rel="stylesheet" href="css/bulma.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">リストを選択 - <? echo $waitlist_name; ?></h1>
            <div class="columns is-multiline">
                <?php foreach ($data['stalls'] as $stall): ?>
                    <div class="column is-one-third">
                        <div class="card">
                            <div class="card-content">
                                <p class="title is-4"><?= htmlspecialchars($stall['name'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="subtitle is-6"><?= htmlspecialchars($stall['description'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <footer class="card-footer">
                                <a href="stall_details.php?id=<?= $stall['id'] ?>" class="card-footer-item">詳細を見る</a>
                            </footer>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="admin/login.php" class="button is-warning">管理者ログイン</a>
            <a href="index.php" class="button is-link">戻る</a>
        </div>
    </section>
</body>
</html>