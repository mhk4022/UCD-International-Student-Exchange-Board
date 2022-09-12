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
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta
    name="viewport"
    content="width=device-width,
    initial-scale=1"
    >

    <title>UC Davis 留学生交流サイト</title>

    <link rel="stylesheet" href="style.css"/>
</head>

<body>
<div id="wrap">
    <div id="head">
        <h1><a href="" id="Davis">UC Davis 留学生交流サイト</a></h1>
    </div>
    <div id="content">
        <div id="setting"><a href="setting/index.php" class="hbtn">設定</a></div>
        <div id="logout" style="text-align: right"><a href="logout.php" class="hbtn">ログアウト</a></div>
        <div id="post" style="text-align: center"><a href="post.php" class="btn">投稿する</a></div>

        <div style="text-align: center" id="sama"><p><?php echo h($name);?>様、投稿しましょう！！</p>
        他の人の投稿を閲覧しましょう！！</div>

        <form action="index.php" method="POST">
            <input type="text" name="textbox">
            <input type="submit" name="search" id="search" class="btn" value="名前を検索する">
        </form>

        <?php if(isset($_POST['textbox']) && $_POST['textbox'] != ""): ?>
            <?php
            /* 検索機能が使われたとき */
            $textbox = $_POST["textbox"];    
            $stmt = $db->prepare("select p.id, p.member_id, p.title, p.created, m.name, m.picture from posts p, members m where m.id=p.member_id and m.name like '%" . $textbox . "%' order by id desc"); 
            if(!$stmt){
                die($db->error);
            }

            $success = $stmt->execute();
            if(!$success){
                die($db->error);
            }

            $stmt->bind_result($id, $member_id, $title, $created, $name, $picture);
            while($stmt->fetch()):
            ?>
            <div class="msg">
                <?php if($picture):?>
                    <img src="member_picture/<?php echo h($picture);?>" width="48" height="48" alt=""/>
                <?php endif;?>
                <span class="name"><?php echo h($name); ?>
                <p id="title"><?php echo h($title);?></span></p>
                <p class="day"><a href="view.php?id=<?php echo h($id); ?>"><?php echo h($created);?></a>
                <?php if($_SESSION['id'] == $member_id):?>
                    [<a href="delete.php?id=<?php echo h($id); ?>" style="color: #F33;">削除</a>]
                <?php endif;?>
                </p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <?php
            $stmt = $db->prepare('select p.id, p.member_id, p.title, p.created, m.name, m.picture from posts p, members m where m.id=p.member_id order by id desc'); 
            if(!$stmt){
                die($db->error);
            }

            $success = $stmt->execute();
            if(!$success){
                die($db->error);
            }

            $stmt->bind_result($id, $member_id, $title, $created, $name, $picture);
            while($stmt->fetch()):
            ?>
            <div class="msg">
                <?php if($picture):?>
                    <img src="member_picture/<?php echo h($picture);?>" width="48" height="48" alt=""/>
                <?php endif;?>
                <span class="name"><?php echo h($name); ?></span>
                <p id="title"><?php echo h($title);?></p>
                <p class="day"><a href="view.php?id=<?php echo h($id); ?>">詳細</a>
                <?php if($_SESSION['id'] == $member_id):?>
                    [<a href="delete.php?id=<?php echo h($id); ?>" style="color: #F33;">削除</a>]
                <?php endif;?>
                </p>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
</body>

</html>