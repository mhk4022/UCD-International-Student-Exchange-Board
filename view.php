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

$id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
if(!$id){
    header('Location: index.php');
    exit();
}

$db = dbconnect();

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>一覧</title>

    <link rel="stylesheet" href="style.css"/>
</head>

<body>
<div id="wrap">
    <div id="head">
        <h1><a href="index.php" id="Davis">UC Davis 留学生交流サイト</a></h1>
    </div>
    <div id="content">
        <div style="text-align: right" id="logout"><a href="logout.php" class="btn">ログアウト</a></div>
        <p><div style="text-align: right" id="setting"><a href="index.php" class="btn">一覧にもどる</a></div></p>
        <?php 
        $stmt = $db->prepare('select p.id, p.member_id, p.title, p.message, p.created, m.name, m.picture , p.picture from posts p, members m where p.id = ? and m.id=p.member_id order by id desc'); 
        if(!$stmt){
            die($db->error);
        }

        $stmt->bind_param('i',$id);
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }

        $stmt->bind_result($id, $member_id, $title, $message, $created, $name, $picture,$post_picture);
        if($stmt->fetch()):
        ?>
        <div class="msg">
            <?php if($picture):?>
                <img src="member_picture/<?php echo h($picture);?>" width="48" height="48" alt=""/>
            <?php endif;?>
                <span class="name"><?php echo h($name); ?></span>
                <p><span id="title"><?php echo h($title); ?></span></p>
                <p><?php echo nl2br(h($message));?></p>
            <?php if($post_picture):?>
                <img src="member_picture/<?php echo h($post_picture);?>" width="400" height="300" alt=""/>
            <?php endif;?>
            <p class="day"><a href="view.php?id="><?php echo h($created);?></a>
            <?php if($_SESSION['id'] == $member_id):?>
                [<a href="delete.php?id=<?php echo h($id);?>" style="color: #F33;">削除</a>]
            <?php endif;?>
            </p>
        </div>
        <?php else: ?>
            <p>その投稿は削除されたか、URLが間違えています</p>
        <?php endif; ?>
    </div>
</div>
</body>

</html>