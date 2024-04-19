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

// コメント投稿機能
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $comment = $_POST["comment"];
    $postId = $_GET['id'];
    $errors = [];

    if (empty($comment)) {
        $errors[] = "コメントを入力してください。";
    }

    if (strlen($comment) > 50) {
        $errors[] = "コメントは50文字以内で入力してください。";
    }

    if (empty($errors)) {
        $commentId = time();
        $commentData = "$postId|$commentId|$comment\n";
        file_put_contents("comments.txt", $commentData, FILE_APPEND);
        header("Location: show.php?id=$postId");
        exit();
    } else {
        $error = current($errors);
        while ($error !== false) { // 現在の要素がfalseでない間ループ
            echo "<p style='color: red;'>{$error}</p>";
            $error = next($errors);
        }
    }
}

// コメント一覧機能
$commentDetails = []; // 投稿データを保存するための配列
$commentsFile = "comments.txt";
if (file_exists($commentsFile)) {
    $comments = file($commentsFile);
    $index = 0;
    if (count($comments) > 0) {
        while ($index < count($comments)) {
            $line = $comments[$index];
            list($currentPostId, $currentCommentId, $commentText) = explode('|', $line);
            if ($currentPostId == $postId) { // この投稿IDに関連するコメントのみを格納
                $commentDetails[] = [
                    'postId' => $currentPostId,
                    'commentId' => $currentCommentId,
                    'commentText' => $commentText
                ];
            }
            $index++;
        }
    }
}

// コメント削除機能
if (isset($_GET['deleteCommentId'])) {
    $deleteCommentId = $_GET['deleteCommentId'];
    $updatedComments = [];
    $currentPostId = '';  // 現在の投稿IDを追跡（リダイレクト用）

    if (file_exists("comments.txt")) {
        $file = fopen("comments.txt", "r");
        while (($line = fgets($file)) !== false) {
            list($currentPostId, $currentCommentId, $commentText) = explode('|', $line);
            if (trim($currentCommentId) != $deleteCommentId) {  // 削除対象のコメントIDでなければ配列に追加
                $updatedComments[] = $line;
            }
        }
        fclose($file);

        // 更新後のコメントをファイルに書き込む
        file_put_contents("comments.txt", implode('', $updatedComments));
        header("Location: show.php?id=$currentPostId");
        exit();
    }
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
    <h1><a href="./index.php">Laravel News</a></h1>
    <h2>投稿詳細</h2>
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
    <?php
    if (empty($commentDetails)) {
        echo "<p>まだコメントがありません。</p>";
    } else {
        $index = 0;
        while ($index < count($commentDetails)) {
            $comment = $commentDetails[$index];
            echo "<p>$commentText<a href='show.php?id=$postId&deleteCommentId=$currentCommentId' onclick='return confirm(\"本当に削除しますか？\");'>削除</a></p>";
            $index++;
        }
    }
    ?>
</body>
</html>
