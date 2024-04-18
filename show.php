<?php
$post_title = "<h2>投稿が見つかりません。</h2>";
$post_message = "<p>指定されたIDの投稿は存在しません。</p>";

// 投稿詳細表示機能
if (isset($_GET['id'])) {
	$postId = $_GET['id'];
	$file = fopen("posts.txt", "r");
    while ($line = fgets($file)) {
        list($id, $title, $message) = explode("|", $line);
        if ($id == $postId) {
            $post_title = "<h2>$title</h2>";
            $post_message = "<p>$message</p>";
            break; // IDが一致したらループを抜ける
        }
    }
    fclose($file);
}

// コメント削除機能
if (isset($_GET['deleteCommentId'])) {
    $deleteCommentId = $_GET['deleteCommentId'];
    $commentFile = "comments_$postId.txt";
    $updatedComments = [];

    $file = fopen($commentFile, "r"); // ファイルを読み込みモードで開く
    while ($comment = fgets($file)) { // ファイルから1行ずつ読み込む
        list($commentId, $commentText) = explode('|', $comment);
        if (trim($commentId) !== $deleteCommentId) { //trimで\nを排除する
            $updatedComments[] = $comment; // 削除対象でなければ配列に追加
        }
    }
    fclose($file); // ファイルストリームを閉じる

    file_put_contents($commentFile, $updatedComments); // 配列をファイルに書き込む
    header("Location: show.php?id=$postId"); // ページをリダイレクト
    exit();
}

// コメント投稿機能
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $comment = $_POST["comment"];
    $errors = [];  // エラーメッセージを保持する配列

    if (empty(trim($comment))) {
        $errors[] = "コメントを入力してください。";
    }

    if (strlen($comment) > 50) {
        $errors[] = "コメントは50文字以内で入力してください。";
    }

    if (empty($errors)) {
        $commentId = time();
        $commentData = "$commentId | $comment\n";
        $commentFile = "comments_$postId.txt";
        file_put_contents($commentFile, $commentData, FILE_APPEND);
        header("Location: show.php?id=$postId");
        exit();
    } else {
        $error = current($errors); // 配列の最初の要素を取得
        while ($error !== false) { // 現在の要素がfalseでない間ループ
            echo "<p style='color: red;'>{$error}</p>";
            $error = next($errors); // 次の要素に移動
        }
    }
}

// コメント一覧機能
$commentFile = "comments_$postId.txt";
$commentDetail = "";
if (file_exists($commentFile)) {
    $comments = file($commentFile);
    if (count($comments) > 0) {
        $index = 0;
        while ($index < count($comments)) {
            $currentComment = $comments[$index];
            list($currentCommentId, $comment) = explode('|', $currentComment);
            $commentDetail .= "<p>".$comment."<a href='show.php?id=$postId&deleteCommentId=$currentCommentId' onclick='return confirm(\"本当に削除しますか？\");'>削除</a></p>";
            $index++;
        }
    } else {
        $commentDetail .= "<p>まだコメントがありません。</p>";
    }
} else {
    $commentDetail .= "<p>まだコメントがありません。</p>";
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>投稿詳細</title>
    <script>
        function confirmSubmit() {
            return confirm('本当にコメントしますか？');
        }
    </script>
</head>
<body>
    <h1>投稿詳細</h1>
    <?php echo $post_title; ?>
    <?php echo $post_message; ?>
    <h2>コメント投稿</h2>
    <form action="show.php?id=<?php echo $postId; ?>" method="post" onsubmit="return confirmSubmit();">
        <label for="comment">コメント:</label>
        <textarea id="comment" name="comment"></textarea>
        <br>
        <input type="hidden" name="commentId" value="<?php echo $commentId; ?>">
        <input type="submit" value="コメント投稿">
    </form>
    <br>
    <h2>コメント一覧</h2>
    <?php echo $commentDetail; ?>
</body>
</html>
