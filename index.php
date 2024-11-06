<?php include("option.php"); ?>
<?php
// index.php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    if ($username !== '') {
        $_SESSION['username'] = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        header('Location: select_stall.php');
        exit();
    } else {
        $error = "ユーザー名を入力してください。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><? echo $waitlist_name; ?> - ユーザー選択</title>
    <link rel="stylesheet" href="css/bulma.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title"><? echo $waitlist_name; ?></h1>
            <form method="post">
                <div class="field">
                    <label class="label">ユーザー名</label>
                    <div class="control">
                        <input class="input" type="text" name="username" placeholder="サイト管理者から渡されたIDです。">
                    </div>
                </div>
                <?php if (isset($error)): ?>
                    <p class="help is-danger"><?= $error ?></p>
                <?php endif; ?>
                <div class="control">
                    <button class="button is-primary" type="submit">次へ</button>
                </div>
            </form>
        </div>
    </section>
</body>
</html>