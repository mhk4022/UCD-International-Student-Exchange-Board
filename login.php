<?php 
session_start();
require('library.php');
$error = [];
$email = '';
$password = '';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST,'password',FILTER_SANITIZE_STRING);
    if($email == '' || $password ==''){
        $error['login'] = 'blank';
    }else{
        /* ログインの確認 */
        $db = dbconnect();
        $stmt = $db->prepare('select id, name, password from members where email=? limit 1');
        if(!$stmt){
            die($db->error);
        }

        $stmt->bind_param('s',$email);
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }

        $stmt->bind_result($id,$name,$hash);
        $stmt->fetch();

        if(password_verify($password,$hash)){
            /* ログイン成功 */
            session_regenerate_id();
            $_SESSION['id'] = $id;
            $_SESSION['name'] = $name;
            header('Location: index.php');
            exit();
        }else{
            $error['login'] = 'failed';
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="style.css"/>
    <title>ログインする</title>
</head>

<body>
<div id="wrap">
    <div id="head">
        <h1><a href="#" id="Davis">UC Davis 留学生交流サイト</a></h1>
    </div>
    <div id="content">
        <div id="lead">
            <h2>UC Dacisの留学生と交流しよう</h2>
            <p>メールアドレスとパスワードを記入してログインしてください</p>
            <p>あなたもメンバーに</p>
            <p><a href="join/sign_up.php" class="btn">メンバーになる</a></p>
        </div>
        <form action="" method="post">
            <p>メールアドレス</p>
            <input type="text" name="email" size="35" maxlength="255" value="<?php echo h($email);?>"/>
            <?php if(isset($error['login']) && $error['login'] == 'blank'):?>
                <p class="error">* メールアドレスとパスワードをご記入ください</p>
            <?php endif;?>
            <?php if(isset($error['login']) && $error['login'] == 'failed'):?>
                <p class="error">* ログインに失敗しました。正しくご記入ください</p>
            <?php endif;?>
            <p>パスワード</p>
            <p>
                <input type="password" name="password" size="35" maxlength="255" value="<?php echo h($password);?>"/>
            </p>
            <div>
                <input type="submit" class="btn" value="ログインする"/>
            </div>
        </form>
    </div>
</div>
</body>
</html>
