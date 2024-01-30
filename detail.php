<?php

// IDをGETで取得
$id = $_GET["id"];

// データベース読み込み
include("funcs.php");
$pdo = db_conn();

// データ登録SQL作成
$stmt = $pdo->prepare("SELECT * FROM omise_table WHERE id = :id");
$stmt -> bindValue(":id", $id, PDO::PARAM_INT);
$status = $stmt->execute();

// データ表示
$view = "";
if($status==false) {
    //SQLエラーの場合
    sql_error($stmt);
  } else {
    //SQL成功の場合（１つのデータだけ表示させる）
    $row = $stmt->fetch();
  }

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>お店お気に入り登録</title>
  <link href="css/index.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>

<!-- Head[Start] -->
<header>
</header>
<!-- Head[End] -->

<!-- Main[Start] -->
<div id="searchPage"><a href="select.php">お気に入り検索へ</a></div>

<div id="registerForm">
  <div id="recommend">
    my recommended restaurant
  </div>
  <div id="bookmark">
    ご飯屋さんブックマーク
  </div>
</div>

<div id="form">
    <!-- シンプルなダイアログの場合は以下コード（onsubmitを使う） -->
	<!-- <form method="POST" action="update.php" onsubmit="return confirmSubmit();"> -->
    <!-- 以下、独自のダイアログ設定の場合 -->
	<form method="POST" action="update.php">
		<table>
			<tr>
				<td>お店<span id="must" required>必須</span></td>
				<td><input type="text" name="omise" id="omise" value="<?=$row["omise"]?>" placeholder="店名を入力"></td>
			</tr>
			<tr>
				<td>シチュエーション<span id="must" required>必須</span></td>
				<td>
					<select name="situation" id="situation" >
						<option value="" hidden>選んでください</option>
						<option value="会食：接待" <?php echo ($row["situation"] == "会食：接待") ? "selected" : ""; ?>>会食：接待</option>
						<option value="会食：ゆるめ" <?php echo ($row["situation"] == "会食：ゆるめ") ? "selected" : ""; ?>>会食：ゆるめ</option>
						<option value="プライベート" <?php echo ($row["situation"] == "プライベート") ? "selected" : ""; ?>>プライベート</option>
						<option value="その他" <?php echo ($row["situation"] == "その他") ? "selected" : ""; ?>>その他</option>
					</select>
				</td>
			</tr>
      <tr>
				<td>ジャンル<span id="must">必須</span></td>
				<td>
					<select name="genre" id="genre" required>
						<option value="" hidden>選んでください</option>
                        <!-- 選択データをもとにselectedを設定 -->
						<option value="和食" <?php echo ($row["genre"] == "和食") ? "selected" : ""; ?>>和食</option>
						<option value="洋食" <?php echo ($row["genre"] == "洋食") ? "selected" : ""; ?>>洋食</option>
						<option value="中華" <?php echo ($row["genre"] == "中華") ? "selected" : ""; ?>>中華</option>
						<option value="エスニック" <?php echo ($row["genre"] == "エスニック") ? "selected" : ""; ?>>エスニック</option>
						<option value="焼肉・肉系" <?php echo ($row["genre"] == "焼肉・肉系") ? "selected" : ""; ?>>焼肉・肉系</option>
						<option value="寿司" <?php echo ($row["genre"] == "寿司") ? "selected" : ""; ?>>寿司</option>
						<option value="カレー" <?php echo ($row["genre"] == "カレー") ? "selected" : ""; ?>>カレー</option>
						<option value="ラーメン" <?php echo ($row["genre"] == "ラーメン") ? "selected" : ""; ?>>ラーメン</option>
						<option value="カフェ・バー" <?php echo ($row["genre"] == "カフェ・バー") ? "selected" : ""; ?>>カフェ・バー</option>
						<option value="パン" <?php echo ($row["genre"] == "パン") ? "selected" : ""; ?>>パン</option>
						<option value="お菓子" <?php echo ($row["genre"] == "お菓子") ? "selected" : ""; ?>>お菓子</option>
						<option value="その他" <?php echo ($row["genre"] == "その他") ? "selected" : ""; ?>>その他</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>エリア<span id="must" required>必須</span></td>
				<td><input type="text" name="area" id="area" value="<?=$row["area"]?>" placeholder="エリアを入力（番地手前までの入力を推奨）"></td>
			</tr>
			<tr>
      <tr>
				<td>URL<span id="must" required>必須</span></td>
				<td><input type="text" name="url" id="url" value="<?=$row["url"]?>" placeholder="お店のURLを入力"></td>
			</tr>
			<tr>
      <tr>
				<td>メモ<span id="optional">任意</span></td>
				<td><input type="text" name="memo" id="memo" value="<?=$row["memo"]?>" placeholder="何かメモあれば..."></td>
			</tr>
		</table>
        <!-- idを隠して送信 -->
        <input type="hidden" name="id" value="<?=$row["id"]?>">
		<input type="submit" value="お気に入り登録（修正）" id="register">
	</form>
</div>
<!-- Main[End] -->

<!-- <div id="confirmation-dialog" title="確認">
    <p>本当に修正しますか？</p>
</div> -->

<!-- モーダル用の背景 -->
<div id="modalBackground"></div>

<!-- 確認ダイアログ用のモーダル -->
<div id="confirmDialog">
    <p>本当に修正しますか？</p>
    <div id="dialogBtn">
        <button id="confirmBtn">OK</button>
        <button id="cancelBtn">キャンセル</button>
    </div>
</div>

</body>

<script>
// シンプルなダイアログの場合は以下コード（＋formタグのonsubmitを使う）
// function confirmSubmit() {
//     // confirm()関数で確認メッセージを表示
//     let result = confirm("本当に修正しますか？");

//     // ユーザーがOKを選択した場合にのみフォームを送信
//     return result;
// }


// 以下、独自のダイアログ設定の場合
// 修正ボタンがクリックされたときの処理
$('#register').on("click", function(e) {
    // モーダル背景とダイアログを表示
    $('#modalBackground, #confirmDialog').fadeIn();
    
    // フォームの送信をキャンセル
    e.preventDefault();
});

// キャンセルボタンがクリックされたときの処理
$('#cancelBtn, #modalBackground').on("click", function() {
    // モーダル背景とダイアログを非表示
    $('#modalBackground, #confirmDialog').fadeOut();
});

// OKボタンがクリックされたときの処理
$('#confirmBtn').on("click", function() {
    // フォームを送信
    $("form").submit();
});


</script>

</html>