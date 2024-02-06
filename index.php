<?php

//0. SESSION開始！！
session_start();
include('funcs.php');
sschk();

// 最後に操作してから30分経過したら自動的にログアウト
if (isset($_SESSION["lastActive"]) && (time() - $_SESSION["lastActive"] > 1800)) {
    session_unset();     // unset $_SESSION 変数
    session_destroy();   // セッションを破棄
    header("Location: login.php");
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>お店お気に入り登録</title>
  <link href="css/index.css" rel="stylesheet">
</head>
<body>

<!-- Head[Start] -->
<header>
</header>
<!-- Head[End] -->

<!-- Main[Start] -->
<div id="searchPage"><a href="select.php">お気に入り検索へ</a></div>
<div id="logout"><a href="logout.php">ログアウト</a></div>

<!-- 管理者IDの場合にのみ表示させるボタン -->
<?php
if (isset($_SESSION["admFlg"])) {
  if ($_SESSION["admFlg"] == 1 ) {
    echo '<div id="admButton"><a href="admin.php">管理者ページ</a></div>';
  }
};
?>

<div id="registerForm">
  <div id="recommend">
    my recommended restaurant
  </div>
  <div id="bookmark">
    ご飯屋さんブックマーク
  </div>
</div>

<div id="form">
	<form method="POST" action="insert.php">
		<table>
			<tr>
				<td>お店<span id="must">必須</span></td>
				<td><input type="text" name="omise" id="omise" placeholder="店名を入力" required></td>
			</tr>
			<tr>
				<td>シチュエーション<span id="must">必須</span></td>
				<td>
					<select name="situation" id="situation" required>
						<option value="" hidden>選んでください</option>
						<option value="会食：接待">会食：接待</option>
						<option value="会食：ゆるめ">会食：ゆるめ</option>
						<option value="プライベート">プライベート</option>
						<option value="その他">その他</option>
					</select>
				</td>
			</tr>
      <tr>
				<td>ジャンル<span id="must">必須</span></td>
				<td>
					<select name="genre" id="genre" required>
						<option value="" hidden>選んでください</option>
						<option value="和食">和食</option>
						<option value="洋食">洋食</option>
						<option value="中華">中華</option>
						<option value="エスニック">エスニック</option>
						<option value="焼肉・肉系">焼肉・肉系</option>
						<option value="寿司">寿司</option>
						<option value="カレー">カレー</option>
						<option value="ラーメン">ラーメン</option>
						<option value="カフェ・バー">カフェ・バー</option>
						<option value="パン">パン</option>
						<option value="お菓子">お菓子</option>
						<option value="その他">その他</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>エリア<span id="must">必須</span></td>
				<td><input type="text" name="area" id="area" placeholder="エリアを入力（番地手前までの入力を推奨）" required></td>
			</tr>
			<tr>
      <tr>
				<td>URL<span id="must">必須</span></td>
				<td><input type="text" name="url" id="url" placeholder="お店のURLを入力" required></td>
			</tr>
			<tr>
      <tr>
				<td>メモ<span id="optional">任意</span></td>
				<td><input type="text" name="memo" id="memo" placeholder="何かメモあれば..."></td>
			</tr>
		</table>
		<input type="submit" value="お気に入り登録（新規）" id="register">
	</form>
</div>
<!-- Main[End] -->

</body>
</html>
