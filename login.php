<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <link href="css/login.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <!-- <link rel="stylesheet" href="css/main.css" /> -->
  <title>ログイン・新規登録</title>
</head>
<body>

<header>
  <!-- <nav class="navbar navbar-default">LOGIN & REGISTER</nav> -->
</header>

<div id="registerForm">
  <div id="recommend">
    my recommended restaurant
  </div>
  <div id="bookmark">
    ご飯屋さんブックマーク
  </div>
</div>

<div id="tab">
    <input type="radio" id="loginTab" name="tabItem" checked>
    <label for="loginTab" id="tabItem">ログイン</label>
    <input type="radio" id="registerTab" name="tabItem">
    <label for="registerTab" id="tabItem">新規登録</label>
    <div id="loginContainer" class="tabContainer">
        <form name="form1" action="loginAct.php" method="post">
        <div id="loginIdDiv">ログインID</div>
        <input type="text" name="lid" class="inputTag" placeholder="ID（メールアドレス）を入力">
        <div id="loginPassDiv">パスワード</div>
        <input type="password" name="lpw" class="inputTag" placeholder="パスワードを入力"></br>
        <input type="submit" value="ログインする" id="loginDone">
        </form>
    </div>
    <div id="registerContainer" class="tabContainer">
        <form name="form2" action="userRegister.php" method="post">
        <div id="loginIdDiv">新規ログインID</div>
        <input type="text" name="newLid" class="inputTag" placeholder="メールアドレスを入力" required>
        <div id="loginPassDiv">新規パスワード</div>
        <input type="password" name="newLpw" class="inputTag" placeholder="パスワードを入力（英数記号含む8文字以上）" required></br>
        <input type="submit" value="ユーザー登録に進む" id="newRegister">
        </form>
    </div>
</div>

<script>
    // OKボタンがクリックされたときの処理
    $('#nerRegister').on("click", function() {
        console.log('OKボタンがクリックされました');
        window.location.href = 'userinfo.php';
    });
</script>

</body>
</html>