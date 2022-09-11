<?php
session_start();
require('../library.php');

//クロスサイトリクエストフォージェリ（CSRF）対策
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];
//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

$errors = array();

$db = dbconnect();

if(empty($_GET)){
    header('Location:sign_up.php');
    exit();
}else{
    if(isset($_GET['urltoken'])){
        $urltoken = $_GET['urltoken'];
    }else{
        $urltoken = NULL;
    }
    if($urltoken == ''){
        $errors['urltoken'] = "トークンがありません"; 
    }else{
        try{
			// DB接続	
			//flagが0の未登録者 and 仮登録日から24時間以内

            $stmt = $db->prepare("select count(*) from pre_members where urltoken=? and flag=0 and created > now() - interval 24 hour"); 
            if(!$stmt){
                die($db->error);
            }

            $stmt->bind_param('s',$urltoken);
            $success = $stmt->execute();
            if(!$success){
                die($db->error);
            }
				
			//レコード件数取得
            $stmt->bind_result($cnt);
            $stmt->fetch();

			//24時間以内に仮登録され、本登録されていないトークンの場合
			if($cnt == 1){
                $stmt = NULL;
                $stmt = $db->prepare("select mail from pre_members where urltoken=? and flag=0 and created > now() - interval 24 hour"); 
                if(!$stmt){
                    var_dump($urltoken);
                    die($db->error);
                }

                $stmt->bind_param('s',$urltoken);
                $success = $stmt->execute();
                if(!$success){
                    die($db->error);
                }

                $stmt->bind_result($mail);
                $stmt->fetch();
				$_SESSION['mail'] = $mail;

			}else{
				$errors['urltoken_timeover'] = "このURLはご利用できません。有効期限が過ぎたかURLが間違えている可能性がございます。もう一度登録をやりなおして下さい。";
			}
			//データベース接続切断
			$stmt = null;
		}catch (PDOException $e){
			print('Error:'.$e->getMessage());
			die();
		}
    }
}

if(isset($_GET['action']) && $_GET['action'] == 'rewrite' && isset($_SESSION['form'])){
    $form = $_SESSION['form'];
}else{
    $form = [
        'name' => '',
        'email' => $mail,
        'password' => ''
    ];
}
$error = [];

/* フォームの内容を確認 */
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $form['name'] = filter_input(INPUT_POST,'name',FILTER_SANITIZE_STRING);
    if ($form['name'] == ''){
        $error['name'] = 'blank';
    }
    
    $form['password'] = filter_input(INPUT_POST,'password',FILTER_SANITIZE_STRING);
    if($form['password'] == ''){
        $error['password'] = 'blank';
    }else if(strlen($form['password']) < 4){
        $error['password'] = 'length';
    }

    /* 画像の確認 */
    $image = $_FILES['image'];
    if($image['name'] != '' && $image['error'] == 0){
        $type = mime_content_type($image['tmp_name']);
        if($type != 'image/png' && $type != 'image/jpeg'){
            $error['image'] = 'type';
        }
    }

    if(empty($error)){
        $_SESSION['form'] = $form;

        /*  画像のアップロード */
        if($image['name'] != ''){
            $filename = date('YmdHis').'_'.$image['name'];
            if(!move_uploaded_file($image['tmp_name'],'../member_picture/'.$filename)){
                die('ファイルのアップロードに失敗しました');
            }
            $_SESSION['form']['image'] = $filename;
        }else{
            $_SESSION['form']['image'] = '';
        }
        header('Location: check.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>会員登録</title>

    <link rel="stylesheet" href="../style.css"/>
</head>

<body>
<div id="wrap">
    <div id="head">
        <h1>UC Dacis 留学生交流サイト</h1>
    </div>
    <div id="content">
        <div style="text-align: right" ><a href="../login.php" class="btn" id="logout">ログイン画面に戻る</a></div>
        <?php if(count($errors) > 0): ?>
            <?php
                foreach($errors as $value){
                    echo "<p class='error'>".$value."</p>";
                }
            ?>
        <?php else: ?>
            <p>必要事項をご記入ください。</p>
            <form action="" method="post" enctype="multipart/form-data">
                <dl>
                    <dt><span class="required">ニックネーム  ＜必須＞</span></dt>
                    <dd>
                        <input type="text" name="name" size="35" maxlength="255" value="<?php echo h($form['name']);?>"/>
                        <?php if(isset($error['name']) && $error['name'] == 'blank'):?>
                            <p class="error">* ニックネームを入力してください</p>
                        <?php endif; ?>
                    </dd>
                    <dt><span class="required">メールアドレス</span></dt>
                    <dd>
                        <div><?php echo h($mail);?></div>
                    <dt><span class="required">パスワード  ＜必須＞</span></dt>
                    <dd>
                        <input type="password" name="password" size="10" maxlength="20" value="<?php echo h($form['password']);?>"/>
                        <?php if(isset($error['password'])&& $error['password'] == 'blank'):?>
                            <p class="error">* パスワードを入力してください</p>
                        <?php endif; ?>
                        <?php if(isset($error['password']) && $error['password'] == 'length'):?>
                            <p class="error">* パスワードは4文字以上で入力してください</p>
                        <?php endif;?>
                    </dd>
                    <dt>アカウント写真</dt>
                    <dd>
                        <input type="file" name="image" size="35" class="btn" value=""/>
                        <?php if(isset($error['image']) && $error['image'] == 'type'):?>
                            <p class="error">* 写真などは「.png」または「.jpg」の画像を指定してください</p>
                        <?php endif;?>
                        <?php if(isset($error['image']) && $error['image'] == 'type'):?>

                            <p class="error">* 恐れ入りますが、画像を改めて指定してください</p>
                        <?php endif;?>
                    </dd>
                </dl>
                <div><input type="submit" class="btn" value="入力内容を確認する"/></div>
            </form>
        <?php endif;?>
    </div>
</body>

</html>