<?php
//1. POSTデータ取得
//$name = filter_input( INPUT_GET, ","name" ); //こういうのもあるよ
//$email = filter_input( INPUT_POST, "email" ); //こういうのもあるよ

//1. POSTデータ取得
$omise = $_POST['omise'];
$situation = $_POST['situation'];
$genre = $_POST['genre'];
$area = $_POST['area'];
$url = $_POST['url'];
$memo = $_POST['memo'];

//2. DB接続します
include("funcs.php");
$pdo = db_conn();

//３．データ登録SQL作成
$stmt = $pdo->prepare("INSERT INTO omise_table ( omise, situation, genre, area, url, memo, indate ) VALUES( :omise, :situation, :genre, :area, :url, :memo, sysdate())");
$stmt->bindValue(':omise', $omise, PDO::PARAM_STR);
$stmt->bindValue(':situation', $situation, PDO::PARAM_STR);
$stmt->bindValue(':genre', $genre, PDO::PARAM_STR);
$stmt->bindValue(':area', $area, PDO::PARAM_STR);
$stmt->bindValue(':url', $url, PDO::PARAM_STR);
$stmt->bindValue(':memo', $memo, PDO::PARAM_STR);
$status = $stmt->execute();

//４．データ登録処理後
if($status == false) {
  //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
  sql_error($stmt);
  // ↑をfuncs.phpから引用しているため、以下2行は不要
  // $error = $stmt->errorInfo();
  // exit("SQL_ERROR:".$error[2]);
} else {
  
  //５．index.phpへリダイレクト
  redirect("index.php");
  // ↑をfuncs.phpから引用しているため、以下2行は不要
  // header("Location: index.php");
  // exit();
}

?>
