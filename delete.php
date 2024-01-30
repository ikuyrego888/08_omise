<?php

// IDをGETで取得
$id = $_GET["id"];

// データベース読み込み
include("funcs.php");
$pdo = db_conn();

// ３．データ登録SQL作成
$sql = "DELETE FROM omise_table WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id',$id, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute(); //実行

//４．データ登録処理後
if ($status == false) {
    sql_error($stmt);
} else {
    redirect("select.php");
}

?>