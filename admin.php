<?php
//0. SESSION開始！！
session_start();

// 最後に操作してから30分経過したら自動的にログアウト
if (isset($_SESSION["lastActive"]) && (time() - $_SESSION["lastActive"] > 1800)) {
    session_unset();     // unset $_SESSION 変数
    session_destroy();   // セッションを破棄
    header("Location: login.php");
}

// 管理者でない場合はアクセス不可とする
if (!isset($_SESSION["admFlg"]) || $_SESSION["admFlg"] != 1) {
    // echo "エラー：管理者権限がありません。";
    exit(); // 以降のコードを実行せずに終了
}

//１．関数群の読み込み
include('funcs.php');
sschk();
$pdo = db_conn();

//２．データ登録SQL作成
$pdo = db_conn();
$stmt = $pdo->prepare("SELECT * FROM omise_user_table");
$status = $stmt->execute();

//3. データ表示
$view = '<form method="POST" action="userUpdate.php" id="editForm">';
$view .= "<table id='userTable'>";
$view .= '<tr><th>id</th><th>ニックネーム</th><th>ログインID</th><th>Adm</br>フラグ</th><th>編集</th><th>削除</th></tr>';


if($status == false) {
    //execute（SQL実行時にエラーがある場合）
  sql_error($stmt);
} else {
    while( $res = $stmt -> fetch(PDO::FETCH_ASSOC)){
        $view .= '<tr>';
        $view .= '<td>' .h($res["id"]) .'</td>';
        $view .= '<td data-field="name" data-id="' .h($res["id"]) .'">' .h($res["name"]) .'</td>';
        $view .= '<td data-field="lid" data-id="' .h($res["id"]) .'">' .h($res["lid"]) .'</td>';
        $view .= '<td data-field="admFlg" data-id="' .h($res["id"]) .'">' .h($res["admFlg"]) .'</td>';
        $view .= '<td><button class="editButton" data-id="' .h($res["id"]) .'">編集</button>';
        // 更新とキャンセルはstyle="display:none"で非表示にしておく
        $view .= '<button class="updateButton" data-id="' .h($res["id"]) .'" style="display:none;">更新</button></br>';
        $view .= '<button class="cancelButton" data-id="' .h($res["id"]) .'" style="display:none;">キャンセル</button></td>';
        // $view .= "<td><div id='userInfo'><a href='userInfo.php?id=".h($res["id"])."'>編集</a></div></td>";
        $view .= "<td><button class='userDelete'><a href='userDelete.php?id=".h($res["id"])."'>削除</a></button></td>";
        $view .= '</tr>';
    }
}
$view .= "</table>";
// userUpdate.phpにsubmitするinputタグを以下にセット（hiddenで見えないようにしておく）
$view .= '<input type="hidden" name="updateUserID" id="updateUserID" value="">';
$view .= '<input type="hidden" name="updateUserName" id="updateUserName" value="">';
$view .= '<input type="hidden" name="updateUserLid" id="updateUserLid" value="">';
$view .= '<input type="hidden" name="updateUserAdmFlg" id="updateUserAdmFlg" value="">';

$view .= "</form>";

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>お店お気に入り登録（修正）</title>
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
<div id="logout"><a href="logout.php">ログアウト</a></div>

<div id="registerFormAdm">
  <div id="recommend">
    my recommended restaurant
  </div>
  <div id="bookmark">
    ご飯屋さんブックマーク
  </div>
</div>

<!-- 検索結果の表示 -->
<div id="resultField">
    <?=$view?>
</div>

<script>

    // 編集中であるか否かを確認する変数
    let editRow = null;

    // 編集ボタンを押した時の設定
    $(".editButton").on("click", function(e) {
        // 編集ボタンを押した時のイベントをキャンセル（これが無いと何故かsubmitが反応してしまう）
        e.preventDefault();

        // 編集する対象の行を取得する
        let row = $(this).closest("tr");

        // 前に編集中の行がある場合は編集をキャンセル（cancelEdit関数を後述）
        if (editRow) {
            editRow.find(".editButton").show();
            editRow.find(".updateButton").hide();
            editRow.find(".cancelButton").hide();
            cancelEdit(editRow);
        }

        // 編集中の行を更新
        editRow = row;

        // 元のデータをdata-originalに突っ込む処理（後でキャンセルボタンを押した時に必要）
        row.find('[data-field="name"]').data("original", row.find('[data-field="name"]').html());
        row.find('[data-field="lid"]').data("original", row.find('[data-field="lid"]').html());
        row.find('[data-field="admFlg"]').data("original", row.find('[data-field="admFlg"]').html());

        // 各セルの内容を取得する
        let idData = $(this).data("id");
        let nameData = row.find('[data-field="name"]').text();
        let lidData = row.find('[data-field="lid"]').text();
        let admData = row.find('[data-field="admFlg"]').text();
        console.log("編集テスト:", "id:"+idData, "name:"+nameData, "lid:"+lidData, "admFlg:"+admData);

        // 編集用フォームの作成
        row.find('[data-field="name"]').html('<input id="editInput" type="text" name="" value="'+nameData+'">');
        row.find('[data-field="lid"]').html('<input id="editInput" type="text" name="" value="'+lidData+'">');
        // row.find('[data-field="admFlg"]').html('<select name="admFlg"><option value="0">0</option><option value="1">1</option></select>');

        // 編集用フォームの作成（セレクトタグへの対応）
        let admHtml = '<select id="editSelect" name="admFlg">';
        admHtml += '<option value="0" ' + (admData == '0' ? 'selected' : '') + '>0</option>';
        admHtml += '<option value="1" ' + (admData == '1' ? 'selected' : '') + '>1</option>';
        admHtml += '</select>';
        row.find('[data-field="admFlg"]').html(admHtml);

        // ボタンの表示・表示設定
        row.find('.editButton').hide();
        row.find('.updateButton').show();
        row.find('.cancelButton').show();

    });

    // 編集中の行のキャンセル処理
    function cancelEdit(row) {
        row.find('[data-field="name"]').html(row.find('[data-field="name"]').data("original"));
        row.find('[data-field="lid"]').html(row.find('[data-field="lid"]').data("original"));
        row.find('[data-field="admFlg"]').html(row.find('[data-field="admFlg"]').data("original"));
    }

    // キャンセルボタンを押した時の設定
    $(".cancelButton").on("click", function (e) {
        // e.preventDefault();

        // キャンセルボタンが押された時の処理
        let row = $(this).closest("tr");
        let nameData = row.find('[data-field="name"]').text();
        console.log("キャンセルテスト:", nameData);

        // 各セルの内容を元に戻す
        row.find('[data-field="name"]').html(row.find('[data-field="name"]').data("original"));
        row.find('[data-field="lid"]').html(row.find('[data-field="lid"]').data("original"));
        row.find('[data-field="admFlg"]').html(row.find('[data-field="admFlg"]').data("original"));

        // ボタンの表示・表示設定
        row.find(".editButton").show();
        row.find(".updateButton").hide();
        row.find(".cancelButton").hide();

        // 編集中であるステータスを解除
        editRow = null;
    });

    // 更新ボタンを押した時の設定
    $(".updateButton").on("click", function (e) {
        // e.preventDefault();

        // 更新するデータの行を取得する
        let updateUserID = $(this).data("id");
        console.log(updateUserID);

        let row = $(this).closest("tr");
        // let row = $('td[data-id="' +updateUserID+ '"]').closest("tr");

        // 更新データを読み込む（inputとselectのバリューを取得する）
        let nameData = row.find('td[data-field="name"] input').val();
        let lidData = row.find('td[data-field="lid"] input').val();
        let admData = row.find('td[data-field="admFlg"] select :selected').val();
        console.log("更新テスト：", "ID:"+updateUserID, "name:"+nameData, "lid:"+lidData, "adm:"+admData);

        // formのinputタグに更新データを格納する
        $("#updateUserID").val(updateUserID);
        $("#updateUserName").val(nameData);
        $("#updateUserLid").val(lidData);
        $("#updateUserAdmFlg").val(admData);

        // データをsubmitする
        $("#editForm").submit();

        // 上のsubmitでリダイレクトされるので、以下3行のshow/hideはmustではない
        row.find(".editButton").show();
        row.find(".updateButton").hide();
        row.find(".cancelButton").hide();

        // 編集中であるステータスを解除
        editRow = null;
    });

</script>

</body>
</html>
