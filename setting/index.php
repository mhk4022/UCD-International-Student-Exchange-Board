<?php
session_start();
require('../library.php');

if(isset($_SESSION['id']) && isset($_SESSION['name'])){
    $id = $_SESSION['id'];
    $name = $_SESSION['name'];
}else{
    header('Location: ../login.php');
    exit();
}

$db=dbconnect();

$stmt = $db->prepare('select name, email from members where id=?'); 
if(!$stmt){
    die($db->error);
}

$stmt->bind_param('i',$id);
$success = $stmt->execute();
if(!$success){
    die($db->error);
}

$stmt->bind_result($prename, $preemail);
if($stmt->fetch());

if(isset($form['name'])){
	$name = $form['name'];
}else{
	$name = $prename;
}

$email = $preemail;

if(isset($_GET['action']) && $_GET['action'] == 'rewrite' && isset($_SESSION['form'])){
    $form = $_SESSION['form'];
}else{
    $form = [
        'name' => '',
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
    if(isset($form['password']) && $form['password'] != "" && strlen($form['password']) < 4){
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
    <title>設定変更</title>

    <link rel="stylesheet" href="../style.css"/>
</head>

<body>
<div id="wrap">
    <div id="head">
        <h1><a href="../index.php" id="Davis">UC Davis 留学生交流サイト</a></h1>
    </div>
    <div id="content">
    <div id="setting"><a href="../index.php" class="btn">一覧にもどる</a></div>
    <div id="logout"><a href="../logout.php" class="btn">ログアウト</a></div>
        <p>変更事項をご記入ください</p>
        <p class="error">変更したい箇所に入力してください</p>
        <form action="" method="post" enctype="multipart/form-data">
            <dl>
                <dt><span class="required">ニックネーム</span></dt>
                <dd>
                    <input type="text" name="name" size="35" maxlength="255" value="<?php echo h($name);?>"/>
                    <?php if(isset($error['name']) && $error['name'] == 'blank'):?>
                        <p class="error">* ニックネームを入力してください</p>
                    <?php endif; ?>
                </dd>
                <dt><span class="required">メールアドレス</span></dt>
                <dd><?php echo h($email);?>
                <dt><span class="required">パスワード</span></dt>
                <dd>
                    <input type="password" name="password" size="10" maxlength="20" value=>
                    <?php if(isset($error['email']) && $error['password'] == 'length'):?>
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
    </div>
</body>

</html>