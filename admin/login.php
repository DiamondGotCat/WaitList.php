<?php
// admin/login.php
include("../option.php");
session_start();
require '../functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 空白の入力をチェック
    if ($username === '' || $password === '') {
        $error = "ユーザー名とパスワードを入力してください。";
    } else {
        $admin_id = authenticateAdmin($username, $password);
        if ($admin_id !== false) {
            $_SESSION['admin_id'] = $admin_id;
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "ユーザー名またはパスワードが正しくありません。";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン - <? echo $waitlist_name; ?> | WaitList.php</title>
    <link rel="stylesheet" href="../css/bulma.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">管理者ダッシュボード - <? echo $waitlist_name; ?> | WaitList.php</h1>
            <form method="post">
                <div class="field">
                    <label class="label">ユーザー名</label>
                    <div class="control">
                        <input class="input" type="text" name="username" placeholder="ユーザー名" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">パスワード</label>
                    <div class="control">
                        <input class="input" type="password" name="password" placeholder="パスワード" required>
                    </div>
                </div>
                <?php if (isset($error)): ?>
                    <p class="help is-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <div class="control">
                    <button class="button is-primary" type="submit">ログイン</button>
                </div>
            </form>
        </div>
    </section>
</body>
</html>