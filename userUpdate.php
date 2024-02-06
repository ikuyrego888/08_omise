<?php

//1. POSTデータ取得
$name = $_POST['updateUserName'];
$lid = $_POST['updateUserLid'];
$admFlg = $_POST['updateUserAdmFlg'];
// $lifeFlg = $_POST['lifeFlg'];

// insertと異なり、idを取得する
$id = $_POST['updateUserID'];

//2. DB接続します
include("funcs.php");
$pdo = db_conn();

//３．データ登録SQL作成
$sql = "UPDATE omise_user_table SET name=:name, lid=:lid, admFlg=:admFlg, indate=sysdate() WHERE id=:id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':name', $name, PDO::PARAM_STR);
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR);
$stmt->bindValue(':admFlg', $admFlg, PDO::PARAM_INT);
// insertと異なり、idを取得する
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

//４．データ登録処理後
if($status == false) {
  //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
  sql_error($stmt);

} else {
  
  //５．リダイレクト
  redirect("admin.php");

}

?>