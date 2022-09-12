<?php 
session_start();
require('../library.php');

/* 初期設定 */
$db = dbconnect();

$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];

header('X-FRAME-OPTIONS: SAMEORIGIN');

$errors = [];
$myname = "shota";
$mymail = "shota0929jp@icloud.com";
$registation_subject = "UC Davis 留学生交流サイト 仮会員登録完了";

/* 送信ボタンをクリックした後 */
if(isset($_POST['submit'])){
    if(empty($_POST['mail'])){
        $errors['mail'] = "メールアドレスが未入力です。";
    }else{
        $mail = $_POST['mail']; 

        if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $mail)){
			$errors['mail_check'] = "メールアドレスの形式が正しくありません。";
       }

       $stmt = $db->prepare('select count(*) from members where email=?');
        if(!$stmt){
            die($db->error);
        }

        $stmt->bind_param('s',$mail);
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }
        
        $stmt->bind_result($cnt);
        $stmt->fetch();

        if($cnt > 0){
            $errors['user_check'] = 'このメールアドレスはすでに利用されています';
        }
    }

    if(count($errors) == 0){
        $urltoken = hash('sha256',uniqid(rand(),1));
        $url = "https://rxyugaku.click/join/index.php?urltoken=".$urltoken;

        try{
            $db = dbconnect();
            $stmt = $db->prepare('insert into pre_members (urltoken,mail,flag) VALUES (?,?,0)');
            if(!$stmt){
                die($db->error);
            }

            $stmt->bind_param('ss',$urltoken,$mail);
            $success = $stmt->execute();
            if(!$success){
                die($db->error);
            }
            $message = "メールの送信に失敗しました。";
        }catch (PDOException $e){
            print('Error:'.$e->getMessage());
            die();
        }

        /* メール送信処理 */
        $mailTo = $mail;
        $body = <<< EOM
        この度はご登録いただきありがとうございます。
        24時間以内に下記のURLからご登録下さい。
        {$url}
        EOM;

        mb_language('ja');
        mb_internal_encoding('UTF-8');

        //Fromヘッダーを作成
        $header = 'From: ' . mb_encode_mimeheader($myname). ' <' . $mymail. '>';

       if(mb_send_mail($mailTo, $registation_subject, $body, $header, '-f'. $mymail)){      
           //セッション変数を全て解除
           $_SESSION = array();
           //クッキーの削除
           if (isset($_COOKIE["PHPSESSID"])) {
               setcookie("PHPSESSID", '', time() - 1800, '/');
           }
           //セッションを破棄する
           session_destroy();
           $message = "メールをお送りしました。24時間以内にメールに記載されたURLからご登録下さい。";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>メール認証</title>

    <link rel="stylesheet" href="../style.css"/>
</head>
<body>
    <div id="head">
        <h1>UC Davis 留学生交流サイト</h1>
    </div>
    <div id="content">
    <p><div style="text-align: right" id="logout"><a href="../login.php" class="hbtn">ログイン画面に戻る</a></div></p>
        <h2>仮会員登録画面</h2>
        <?php if (isset($_POST['submit']) && count($errors) === 0): ?>
            <!-- 登録完了画面 -->
            <p><?=$message?></p>
        <?php else: ?>
            <!-- 登録画面 -->
            <?php if(count($errors) > 0): ?>
                <?php
                foreach($errors as $value){
                    echo "<p class='error'>".$value."</p>";
                }
                ?>
            <?php endif; ?>
            <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
                <p>メールアドレス</p>
                <p><input type="text" name="mail" size="50" value="<?php if( !empty($_POST['mail']) ){ echo $_POST['mail']; } ?>"></p> 
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <input class="btn" type="submit" name="submit" value="送信する">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

