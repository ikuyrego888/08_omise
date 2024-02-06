<?php
// エラー表示設定（最後にコメントアウトすること！）
// ini_set('display_errors', 'On');
// error_reporting(E_ALL);

//0. SESSION開始！！
session_start();

// 最後に操作してから30分経過したら自動的にログアウト
if (isset($_SESSION["lastActive"]) && (time() - $_SESSION["lastActive"] > 1800)) {
    session_unset();     // unset $_SESSION 変数
    session_destroy();   // セッションを破棄
    header("Location: login.php");
}

?>

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

// insertと異なり、idを取得する
$id = $_POST['id'];

//2. DB接続します
include("funcs.php");
$pdo = db_conn();
// try {
//   // さくらサーバ データベース
//   // $pdo = new PDO('mysql:dbname=gs-ac07_gs_db08;charset=utf8;host=mysql57.gs-ac07.sakura.ne.jp','gs-ac07','Eiiti0826');
//   // ローカルストレージ データベース Password:MAMP='root',XAMPP=''
//   $pdo = new PDO('mysql:dbname=gs_db08;charset=utf8;host=localhost','root','');
// } catch (PDOException $e) {
//   exit('DBConnection Error:'.$e->getMessage());
// }

//３．データ登録SQL作成
$sql = "UPDATE omise_table SET omise=:omise, situation=:situation, genre=:genre, area=:area, url=:url, memo=:memo, indate=sysdate() WHERE id=:id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':omise', $omise, PDO::PARAM_STR);
$stmt->bindValue(':situation', $situation, PDO::PARAM_STR);
$stmt->bindValue(':genre', $genre, PDO::PARAM_STR);
$stmt->bindValue(':area', $area, PDO::PARAM_STR);
$stmt->bindValue(':url', $url, PDO::PARAM_STR);
$stmt->bindValue(':memo', $memo, PDO::PARAM_STR);
// insertと異なり、idを取得する
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
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
  redirect("select.php");
  // ↑をfuncs.phpから引用しているため、以下2行は不要
  // header("Location: index.php");
  // exit();
}

?>