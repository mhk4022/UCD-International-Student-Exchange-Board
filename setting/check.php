<?php 
session_start();
require('../library.php');

if(isset($_SESSION['form'])){
	$form = $_SESSION['form'];
}else{
	header('Location: index.php');
	exit();
}

if(isset($_SESSION['id']) && isset($_SESSION['name'])){
    $id = $_SESSION['id'];
    $name = $_SESSION['name'];
}else{
    header('Location: ../login.php');
    exit();
}

$db=dbconnect();

$stmt = $db->prepare('select name, email, password, picture from members where id=?'); 
if(!$stmt){
    die($db->error);
}

$stmt->bind_param('i',$id);
$success = $stmt->execute();
if(!$success){
    die($db->error);
}

$stmt->bind_result($prename, $preemail, $prepassword, $prepicture);
if($stmt->fetch());

if(isset($form['name']) && $form['name'] != ""){
	$name = $form['name'];
}else{
	$name = $prename;
}

$email = $preemail;

if(isset($form['image']) && $form['image'] != ""){
	$picture = $form['image'];
}else{
	$picture = $prepicture;
}


if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$db = dbconnect();
	$stmt = $db->prepare('update members set name =?,password=?,picture=? where id=?');
	if(!$stmt){
		die($db->error);
	}
	if(isset($form['password']) && $form['password'] != ""){
		$password = password_hash($form['password'],PASSWORD_DEFAULT);
	}else{
		$password = $prepassword;
	}
	$stmt->bind_param('sssi',$name,$password,$picture,$id);
	$success = $stmt->execute();
	if(!$success){
		die($db->error);
	}

	unset($_SESSION['form']);
	header('Location: thanks.php');
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>変更確認</title>

	<link rel="stylesheet" href="../style.css" />
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>UC Davis 留学生交流サイト</h1>
		</div>

		<div id="content">
			<p>記入した内容を確認して、「登録する」ボタンをクリックしてください</p>
			<form action="" method="post">
				<dl>
					<dt>ニックネーム</dt>
					<dd><?php echo h($name);?></dd>
					<dt>メールアドレス</dt>
					<dd><?php echo h($email);?></dd>
					<dt>パスワード</dt>
					<dd>
						セキュリティのため非表示
					</dd>
					<dt>写真</dt>
					<dd>
						<img src="../member_picture/<?php echo h($picture);?>" width="100" alt="" />
					</dd>
				</dl>
				<div><a class="btn" href="index.php?action=rewrite">書き直す</a>  <input class="btn" type="submit" value="登録する" /></div>
			</form>
		</div>

	</div>
</body>

</html>