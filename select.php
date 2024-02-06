<?php
session_start();
include('funcs.php');
sschk();

// 最後に操作してから30分経過したら自動的にログアウト
if (isset($_SESSION["lastActive"]) && (time() - $_SESSION["lastActive"] > 1800)) {
  session_unset();     // unset $_SESSION 変数
  session_destroy();   // セッションを破棄
  header("Location: login.php");
}

//1. DB接続
$pdo = db_conn();

// try {
//   // さくらサーバ データベース
//   // $pdo = new PDO('mysql:dbname=gs-ac07_gs_db08;charset=utf8;host=mysql57.gs-ac07.sakura.ne.jp','gs-ac07','Eiiti0826');
//   // ローカルストレージ データベース Password:MAMP='root',XAMPP=''
//   $pdo = new PDO('mysql:dbname=gs_db08;charset=utf8;host=localhost','root','');
// } catch (PDOException $e) {
//   exit('DBConnection Error:'.$e->getMessage());
// }

//2. 検索条件の初期化
$situationCondition = "";
$genreCondition = "";
$textKey = "";

// シチュエーションの検索条件を確認
if (isset($_POST["situationOption"])) {
  // チェックボックスで選択したもの$situationOptionに格納
  $situationOption = $_POST["situationOption"];
  // situationの前に空白を１つ入れないとWHEREが正しく読み込まれない（大事💡）
  $situationCondition = " situation IN ('";
  // "explode"は配列変換、"implode"は文字列変換である
  $situationCondition .= implode("', '", $situationOption);
  $situationCondition .= "')";
}
// echo $situationCondition;

// ジャンルの検索条件を確認
if (isset($_POST["genreOption"])) {
  // チェックボックスで選択したもの$situationOptionに格納
  $genreOption = $_POST["genreOption"];
  // situationの前に空白を１つ入れないとWHEREが正しく読み込まれない（大事💡）
  $genreCondition = " genre IN ('";
  // "explode"は配列変換、"implode"は文字列変換である
  $genreCondition .= implode("', '", $genreOption);
  $genreCondition .= "')";
}

// 検索タイプを確認（OR検索かAND検索か/デフォルトはOR検索）
// POSTセットされていること且つPOSTがorだったら" OR "、そうでない場合は" AND "
// ?と:は、手前の式が真なら" OR "、偽なら" AND "というコード（IF文に似ている）
$search = isset($_POST["searchType"]) && $_POST["searchType"] == "or" ? " OR " : " AND ";

// エリアの入力条件を確認
if (isset($_POST["textKey"])) {
  $textKey = $_POST["textKey"];
}

//3. データ登録SQL作成
// $stmt = $pdo->prepare("SELECT * FROM omise_table");
// $stmt = $pdo->prepare("SELECT * FROM omise_table WHERE situation = '会食：接待' OR situation = '会食：ゆるめ'");
// 以下、"IN"を使っても同じことができる💡
// $stmt = $pdo->prepare("SELECT * FROM omise_table WHERE situation IN ('会食：接待','プライベート')");

// SQLに検索結果を反映させるためのWHERE設定
$whereData = "";
// シチュエーションに条件がある＆ジャンルが空の場合はシチュエーションだけセット
if (!empty($situationCondition) && empty($genreCondition)) {
  $whereData = " WHERE" .$situationCondition;
// シチュエーションにが空＆ジャンルに条件がある場合はジャンルだけセット
} else if (empty($situationCondition) && !empty($genreCondition)) {
  $whereData = " WHERE" .$genreCondition;
// シチュエーションもジャンルも両方に条件セットされている場合（.$searchはOR検索/AND検索のセット）
} else if (!empty($situationCondition) && !empty($genreCondition)) {
  $whereData = " WHERE" .$situationCondition .$search .$genreCondition;
}
// echo $whereData;

// テキスト検索に条件がある場合は$whereDataに追加
// if (!empty($textKey)) {
//   if (empty($whereData)) {
//     $whereData .= " WHERE (area LIKE '%$textKey%' OR memo LIKE '%$textKey%')";
//   } else {
//     $whereData .= " AND (area LIKE '%$textKey%' OR memo LIKE '%$textKey%')";
//   }
// }

// テキスト検索に条件がある場合は$whereDataに追加（※複数キーワード検索に対応）
if (!empty($textKey)) {
  // 全角スペースで区切って配列にセットする（＝複数キーワードを全角スペースで認識する）
  $textKeyData = explode("　", $textKey);
  $textKeyArray = [];
  foreach ($textKeyData as $text) {
    $textKeyArray[] = "(area LIKE '%$text%' OR memo LIKE '%$text%')";
  }
  if (empty($whereData)) {
    // シチュエーション・ジャンルが空の場合は"WHERE"からセットする
    $whereData .= " WHERE " .implode(" AND ", $textKeyArray);
  } else {
    // シチュエーション・ジャンルに何かしらの検索条件がある場合は"WHERE"は不要なので、"AND"からセットする
    $whereData .= " AND " .implode(" AND ", $textKeyArray);
  }
}

// "ORDER BY"を使って、omiseで並び替えしてみた（が、完全に50音順にはならず...）
$orderData = " ORDER BY omise COLLATE utf8_unicode_ci";

// 何も検索していない場合にも全部表示にしておかないとエラーになってしまう（💡要確認）

if (empty($whereData)) {
  $stmt = $pdo->prepare("SELECT * FROM omise_table" .$orderData);
  $status = $stmt->execute();
} else {
  // $stmt = $pdo->prepare("SELECT * FROM omise_table $whereData AND area LIKE '%$textKey%'");
  $stmt = $pdo->prepare("SELECT * FROM omise_table $whereData" .$orderData);
  $status = $stmt->execute();
}

// $pdo->prepareは以下2行でもいける
// $stmt = $pdo->prepare("SELECT * FROM omise_table $whereData" .$orderData);
// $status = $stmt->execute();

//4. データ表示
$view = "";

// １つのテーブルにする場合は以下コードを使用する
// $view .= "<table id='resultTable'>";

if($status == false) {
    //execute（SQL実行時にエラーがある場合）
  sql_error($stmt);
  // ↑をfuncs.phpから引用しているため、以下2行は不要
  // $error = $stmt -> errorInfo();
  // exit("SQL_ERROR:" .$error[2]);

} else {
  //Selectデータの数だけ自動でループしてくれる
  //FETCH_ASSOC=http://php.net/manual/ja/pdostatement.fetch.php
  while( $res = $stmt -> fetch(PDO::FETCH_ASSOC)){
    $view .= "<table id='resultTable'>";
    $view .= "<tr id ='trOmise'>";
    // $view .= "<td>" .h($res["id"]) ."</td>";
    $view .= "<td colspan='8' id='tdOmise'>" .'★ ' .h($res["omise"]) ."</td>";
    // ↑上のコードだと"detail.php"に遷移しない設定。"detail.phpに遷移するために以下３行に書き換え"
    // $view .= "<td colspan='5' id='tdOmise'><a href='detail.php?id=";
    // $view .= h($res["id"])."'>";
    // $view .= "★ ".h($res["omise"]) ."</a></td>";
    $view .= "<td id='tdGenre' colspan='2'><div id='divGenre'>" .h($res["genre"]) ."</div></td></tr>";
    $view .= "<tr><td colspan='6' id='tdArea'>" ."<span id='spanSpace'>　</span>場所：" .h($res["area"]) ."</td>";
    $view .= "<td colspan='1' id='tdSituation'>" .h($res["situation"]) ."</td>";
    // $view .= "<td>" .h($res["genre"]) ."</td>";
    // $view .= "<td>" .h($res["area"]) ."</td>";
    $view .= "<td colspan='3' id='tdMemo'>" .'メモ：' .h($res["memo"]) ."</td></tr>";
    // relに"noopener"と"noreferrer"はセキュリティ設定
    $view .= "<tr id='trUrl'><td colspan='8' id='tdUrl'><span id='spanSpace'>　</span><a href='" .h($res["url"]) ."' target='_blank' rel='noopener noreferrer' id='urlCSS'>".h($res["url"])."</a></td>";
    $view .= "<td id='tdDetail' colspan='1'><div id='divDetail'><a href='detail.php?id=".h($res["id"])."'>修正</a></div></td>";
    // $view .= "<td id='tdDelete'><div id='divDelete'><a href='delete.php?id=".h($res["id"])."' id='deleteBtn'>削除</a></div></td>";
    // 削除用のaタグに"data-id"をセット。これにより後述するダイアログ設定でも削除するデータIDを取得できるようにする。
    $view .= "<td id='tdDelete' colspan='1'><div id='divDelete'><a href='delete.php?id=".h($res["id"])."' data-id='".h($res["id"])."'>削除</a></div></td>";
    $view .= "</tr>";
    $view .= "</table>";
  }
}

// １つのテーブルにする場合は以下コードを使用する
// $view .= "</table>";

$_SESSION["result"] = $view;
// $_SESSION["result"] = $view;
// var_dump($_SESSION["result"]);
// var_dump($view);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>お店検索</title>
<!-- <link rel="stylesheet" href="css/range.css"> -->
<!-- <link href="css/bootstrap.min.css" rel="stylesheet"> -->
<link href="css/select.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body id="main">
<!-- Head[Start] -->
<header>
</header>
<!-- Head[End] -->

<!-- Main[Start] -->
<div id="registerPage"><a href="index.php">お気に入り登録へ</a></div>
<div id="logout"><a href="logout.php">ログアウト</a></div>

<!-- 管理者IDの場合にのみ表示させるボタン -->
<?php
if (isset($_SESSION["admFlg"])) {
  if ($_SESSION["admFlg"] == 1 ) {
    echo '<div id="admButton"><a href="admin.php">管理者ページ</a></div>';
  }
};
?>

<div id="searchForm">
  <div id="recommend">
    my recommended restaurant
  </div>
  <div id="bookmark">
    ご飯屋さんブックマーク
  </div>
</div>

<!-- &#9776 は三本線のメニューバーのコード -->
<div id="menuButton">&#9776;</div>
<div>
  <div id="container">
    <div id="sidemenu">
      <form action="" method="post">
        <!-- シチュエーションを選択 -->
        <fieldset>
          <legend id="legendSet">シチュエーション</legend>
          <input type="checkbox" id="situation01" name="situationOption[]" value="会食：接待">
          <label for="situation01">会食：接待</label>
          <input type="checkbox" id="situation02" name="situationOption[]" value="会食：ゆるめ">
          <label for="situation02">会食：ゆるめ</label></br>
          <input type="checkbox" id="situation03" name="situationOption[]" value="プライベート">
          <label for="situation03">プライベート</label>
          <input type="checkbox" id="situation04" name="situationOption[]" value="その他">
          <label for="situation04">その他</label>
        </fieldset>
        <!-- ジャンルを選択 -->
        <fieldset>
          <legend id="legendSet">ジャンル</legend>
          <input type="checkbox" id="genre01" name="genreOption[]" value="和食">
          <label for="genre01">和食</label>
          <input type="checkbox" id="genre02" name="genreOption[]" value="洋食">
          <label for="genre02">洋食</label>
          <input type="checkbox" id="genre03" name="genreOption[]" value="中華">
          <label for="genre03">中華</label>
          <input type="checkbox" id="genre04" name="genreOption[]" value="エスニック">
          <label for="genre04">エスニック</label></br>
          <input type="checkbox" id="genre05" name="genreOption[]" value="焼肉・肉系">
          <label for="genre05">焼肉・肉系</label>
          <input type="checkbox" id="genre06" name="genreOption[]" value="寿司">
          <label for="genre06">寿司</label>
          <input type="checkbox" id="genre07" name="genreOption[]" value="カレー">
          <label for="genre07">カレー</label></br>
          <input type="checkbox" id="genre08" name="genreOption[]" value="ラーメン">
          <label for="genre08">ラーメン</label>
          <input type="checkbox" id="genre09" name="genreOption[]" value="カフェ・バー">
          <label for="genre09">カフェ・バー</label>
          <input type="checkbox" id="genre10" name="genreOption[]" value="パン">
          <label for="genre10">パン</label></br>
          <input type="checkbox" id="genre11" name="genreOption[]" value="お菓子">
          <label for="genre11">お菓子</label>
          <input type="checkbox" id="genre12" name="genreOption[]" value="その他">
          <label for="genre12">その他</label>
        </fieldset>
        <!-- 検索タイプを選択 -->
        <fieldset>
          <legend id="legendSet">検索タイプ</legend>
          <input type="radio" id="or" name="searchType" value="or" checked>
          <label for="or">OR検索</label>
          <input type="radio" id="and" name="searchType" value="and">
          <label for="and">AND検索</label>
        </fieldset>
        <!-- エリアをあいまい検索 -->
        <fieldset>
          <legend id="legendSet">テキスト検索<span id="andSearch">　*AND検索</span></legend>
          <input type="text" name="textKey" id="textKey" placeholder="エリア・メモなど（複数入力は全角スペースで区切る）">
        </fieldset>
        <!-- 検索実行ボタン -->
        <input type="submit" value="検索" id="searchButton">
      </form>
    </div>

    <!-- 検索結果の表示 -->
    <div id="resultField">
      <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION["result"])) {
          echo "<div>".$_SESSION["result"]."</div>";
        }
      ?>
    </div>
  
  </div>
</div>

<!-- モーダル用の背景 -->
<div id="modalBackground"></div>

<!-- 確認ダイアログ用のモーダル -->
<div id="confirmDialog">
    <p>本当に削除しますか？</p>
    <div id="dialogBtn">
        <button id="confirmBtn">OK</button>
        <button id="cancelBtn">キャンセル</button>
    </div>
</div>

<!-- Main[End] -->
<script>
  // メニューボタンの設定
  $("#menuButton").on("click", function() {
    // 以下はサイドメニューが出ている状態がデフォルトでボタンを押すと隠れるコード
    // let menuLeft = parseInt($("#sidemenu").css('left'));
    // サイドメニューをボタンを押して隠す方法（以下2つのlet関数で隠す位置を設定）
    // let leftSet = "-400px"
    // let leftSetMedia = "-280px"
    // サイドメニューをボタンを押して隠す方法
    // if (window.innerWidth > 640 ) {
    //   if (menuLeft === 10) {
    //     $("#sidemenu").css("left", leftSet);
    //   } else {
    //     $("#sidemenu").css("left", "10px");
    //   }
    // } else if (window.innerWidth <= 640 ) {
    //   if (menuLeft === 10) {
    //     $("#sidemenu").css("left", leftSetMedia);
    //   } else {
    //     $("#sidemenu").css("left", "10px");
    //   }
    // }

    // 以下はサイドメニューが隠れている状態がデフォルトでボタンを押すと現れるコード
    let menuLeft = parseInt($("#sidemenu").css('left'));
    // サイドメニューをボタンを押して隠す方法（以下2つのlet関数で隠す位置を設定）
    let leftSet = "-400px"
    let leftSetMedia = "-290px"
    // サイドメニューをボタンを押して隠す方法
    if (window.innerWidth > 640 ) {
      if (menuLeft === -400) {
        $("#sidemenu").css("left", "10px");
      } else {
        $("#sidemenu").css("left", leftSet);
      }
    } else if (window.innerWidth <= 640 ) {
      if (menuLeft === -290) {
        $("#sidemenu").css("left", "10px");
      } else {
        $("#sidemenu").css("left", leftSetMedia);
      }
    }
    // 以下とcssの組み合わせではサイドメニューをスライドして消すアニメーション反応せず💀
    // $("#sidemenu").toggleClass("hidden-menu");
  })

  $("#serchButton").on("click", function() {

  })

  // 以下、ダイアログの設定
  $('#divDelete a').on("click", function(e) {
    // クリックされた場合も同様にモーダル背景とダイアログを表示
    $('#modalBackground, #confirmDialog').fadeIn();

    // 削除データのIDを取得
    let deleteID = $(this).data("id");
    console.log(deleteID); 

    // キャンセルボタンがクリックされたときの処理
    $('#cancelBtn, #modalBackground').on("click", function() {
        console.log('キャンセルボタンがクリックされました');
        // モーダル背景とダイアログを非表示
        $('#modalBackground, #confirmDialog').fadeOut();
    });

    // OKボタンがクリックされたときの処理
    $('#confirmBtn').on("click", function() {
        console.log('OKボタンがクリックされました');
        // ダイアログがOKされた場合、delete.phpに遷移
        window.location.href = 'delete.php?id=' + deleteID;
    });

    // aタグのデフォルト動作を無効化
    e.preventDefault(); 

  });

</script>

</body>
</html>
