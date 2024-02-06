<?php

//1. POSTデータ取得
$name = $_POST['name'];
$newLid = $_POST['newLid'];
$newLpw = $_POST['newLpw'];

// パスワードをハッシュ化する
$newLpwHash = password_hash($newLpw, PASSWORD_DEFAULT);

//2. DB接続します
include("funcs.php");
$pdo = db_conn();

//３．データ登録SQL作成
$stmt = $pdo->prepare("INSERT INTO omise_user_table ( name, lid, lpw, admFlg, lifeFlg, indate ) VALUES( :name, :lid, :lpw, :admFlg, :lifeFlg, sysdate())");
$stmt->bindValue(':name', $name, PDO::PARAM_STR);
$stmt->bindValue(':lid', $newLid, PDO::PARAM_STR);
$stmt->bindValue(':lpw', $newLpwHash, PDO::PARAM_STR);
$stmt->bindValue(':admFlg', 0, PDO::PARAM_INT);
$stmt->bindValue(':lifeFlg', 0, PDO::PARAM_INT);
$status = $stmt->execute();

//４．データ登録処理後
if($status == false) {
  //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
  sql_error($stmt);
} else {
  
  //５．index.phpへリダイレクト
  redirect("login.php");

}

?>