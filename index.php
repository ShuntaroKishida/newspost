<?php
// 投稿機能
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $message = $_POST["message"];
    $errors = []; // エラーメッセージを格納する配列

    if (empty(trim($title))) {
        $errors[] = "タイトルを入力してください。";
    } // タイトルが空白の場合

    if (strlen($title) > 30) {
        $errors[] = "タイトルは30文字以内で入力してください。";
    } // タイトルが30文字以上の場合

    if (empty(trim($message))) {
        $errors[] = "投稿内容を入力してください。";
    } // 投稿内容が空白の場合

    if (empty($errors)) {
        $postId = time(); // 投稿を識別するためのidとして使用する
        $post = "$postId | $title | $message\n";
        file_put_contents('posts.txt', $post, FILE_APPEND);
        header("Location: index.php"); // リロード時の再投稿防止
        exit();
    } else {
        $error = current($errors); // 配列の最初の要素を取得
        while ($error !== false) { // 現在の要素がfalseでない間ループ
            echo "<p style='color: red;'>{$error}</p>";
            $error = next($errors); // 次の要素に移動
        }
    }
}

// 投稿一覧機能
$postDetails = []; // 投稿データを保存するための配列
$postsFile = "posts.txt";
if (file_exists($postsFile)) {
    $posts = file($postsFile);
    $index = 0;
    if (count($posts) > 0) {
        while ($index < count($posts)) {
            $line = $posts[$index];
            list($postId, $title, $message) = explode('|', $line);
            $postDetails[] = [
                'id' => $postId,
                'title' => $title,
                'message' => $message
            ]; // 各投稿を配列として $postDetails に追加
            $index++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel News</title>
    <script>
        // フォーム送信時の確認ダイアログ
        function confirmSubmit() {
            // confirm関数で「OK」が押されたらtrue、それ以外はfalseを返す
            return confirm('本当に投稿しますか？');
        }
    </script>

</head>
<body>
    <h1>Laravel News</h1>
    <form action="./index.php" method="post" onsubmit="return confirmSubmit();">
        <label for="title">タイトル:</label>
        <input type="text" id="title" name="title">
        <br><br>
        <label for="message">投稿内容:</label>
        <textarea id="message" name="message"></textarea>
        <br><br>
        <input type="submit" value="投稿">
    </form>
    <h2>投稿一覧</h2>
    <?php
    if (empty($postDetails)) {
        echo "<p>まだ投稿がありません。</p>";
    } else {
        $index = 0;
        while ($index < count($postDetails)) {
            $post = $postDetails[$index];
            echo "<p><a href='show.php?id=".$post['id']."'>タイトル: ".$post['title']."</a><br>投稿内容: ".$post['message']."</p>";
            $index++;
        }
    }
    ?>
</body>
</html>
