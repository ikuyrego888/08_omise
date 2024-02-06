<?php
include("funcs.php");

$newLid = $_POST['newLid'];
$newLpw = $_POST['newLpw'];

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>新規ユーザー登録</title>
  <link href="css/index.css" rel="stylesheet">
</head>
<body>

<div id="registerForm">
  <div id="recommend">
    my recommended restaurant
  </div>
  <div id="bookmark">
    ご飯屋さんブックマーク
  </div>
</div>

<div id="form">
	<form method="POST" action="registerAct.php">
		<table>
			<tr>
				<td>ニックネーム<span id="must">必須</span></td>
				<td><input type="text" name="name" class="userinfo" placeholder="ニックネームを入力" required></td>
			</tr>
      <tr>
				<td>新規ID<span id="must">必須</span></td>
				<td><input type="text" name="newLid" class="userinfo" value="<?=h($newLid)?>" placeholder="新規IDを入力" required></td>
			</tr>
      <tr>
				<td>新規パスワード<span id="must">必須</span></td>
				<td><input type="password" name="newLpw" class="userinfo" value="<?=h($newLpw)?>" placeholder="新規パスワードを入力" required></td>
			</tr>
		</table>
		<input type="submit" value="ユーザー登録（確定）" id="register">
		<!-- <div id="return"><a href="login.php">戻る</a></div> -->
		<a href="login.php" id="return">戻る</a>
	</form>
</div>

</body>
</html>