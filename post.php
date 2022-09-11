<?php 
session_start();
require('library.php');

if(isset($_SESSION['id']) && isset($_SESSION['name'])){
    $id = $_SESSION['id'];
    $name = $_SESSION['name'];
}else{
    header('Location: login.php');
    exit();
}

$db = dbconnect();

$error = [];

/* メッセージの投稿 */
if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['message']){

    /* 画像の確認 */
    $image = $_FILES['image'];
    if($image['name'] != '' && $image['error'] == 0){
        $type = mime_content_type($image['tmp_name']);
        if($type != 'image/png' && $type != 'image/jpeg'){
            $error['image'] = 'type';
        }
    }

    /*  画像のアップロード */
    if($image['name'] != ''){
        $filename = date('YmdHis').'_'.$image['name'];
        if(!move_uploaded_file($image['tmp_name'],'member_picture/'.$filename)){
            die('ファイルのアップロードに失敗しました');
        }
    }

    
    $message = filter_input(INPUT_POST,'message',FILTER_SANITIZE_STRING);
    $title = filter_input(INPUT_POST,'title',FILTER_SANITIZE_STRING);

    if ($message == ''){
        $error['message'] = 'blank';
    }

    if ($title == ''){
        $error['title'] = 'blank';
    }

    $stmt = $db->prepare('insert into posts (message,member_id,picture,title) values(?,?,?,?)');
    if(!$stmt){
        die($db->error);
    }

    $stmt->bind_param('siss',$message,$id,$filename,$title);
    $success = $stmt->execute();
    if(!$success){
        die($db->error);
    }

    header('Location: index.php');
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>投稿する</title>

    <link rel="stylesheet" href="style.css"/>
</head>

<body>
<div id="wrap">
    <div id="head">
        <h1><a href="index.php" id="Davis">UC Davis 留学生交流サイト</a></h1>
    </div>
    <div id="content">
        <div style="text-align: right" id="logout"><a href="logout.php" class="btn">ログアウト</a></div>
        <p><div id="setting"><a href="index.php" class="btn" >一覧にもどる</a></div></p>
        <form action="" method="post" enctype="multipart/form-data">
            <dl>
                <dt>タイトル</dt>
                <?php if(isset($error['title']) && $error['title'] == 'blank'):?>
                        <p class="error"> タイトルを入力してください</p>
                <?php endif; ?>
                <dd>
                    <textarea name="title" cols="50" rows="1"></textarea>
                </dd>
                <dt>本文</dt>
                <?php if(isset($error['text']) && $error['text'] == 'blank'):?>
                        <p class="error"> 本文を入力してください</p>
                <?php endif; ?>
                <dd>
                    <textarea name="message" cols="50" rows="5"></textarea>
                </dd>
            </dl>
            <input type="file" name="image" size="35" class="btn" value=""/>
                    <?php if(isset($error['image']) && $error['image'] == 'type'):?>
                        <p class="error">* 写真などは「.png」または「.jpg」の画像を指定してください</p>
                    <?php endif;?>
                    <?php if(isset($error['image']) && $error['image'] == 'type'):?>
                        <p class="error">* 恐れ入りますが、画像を改めて指定してください</p>
                    <?php endif;?>
            <div>
                <p>
                    <input type="submit" class="btn" value="投稿する"/>
                </p>
            </div>
        </form>
    </div>
</div>
</body>

</html>