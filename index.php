<?php
// 投稿機能
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $message = $_POST["message"];
    $postId = time(); // idとして使用する目的

    $post = "$postId | $title | $message\n";
    file_put_contents('posts.txt', $post, FILE_APPEND);
    header("Location: index.php"); // リロード時の再投稿防止
    exit();
}

// 投稿一覧機能
$postDetail = ""; // 投稿データを保存するための変数
if (file_exists('posts.txt')) {
    $posts = file('posts.txt');
    $index = 0;

    while ($index < count($posts)) {
        $currentPost = $posts[$index];
        list($postId, $title, $message) = explode('|', $currentPost);
        $postDetail .= "<p>タイトル:$title<br>投稿内容:$message</p>"; // 各投稿を $postDetail に追加
        $index++;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel News</title>
</head>
<body>
    <h1>Laravel News</h1>
    <form action="./index.php" method="post">
        <label for="title">タイトル:</label>
        <input type="text" id="title" name="title">
        <br><br>
        <label for="message">投稿内容:</label>
        <textarea id="title" name="message"></textarea>
        <br><br>
        <input type="submit" value="投稿">
    </form>
    <h2>投稿一覧</h2>
    <?php echo $postDetail; ?> <!-- 保存された全ての投稿データを表示 -->
</body>
</html>
