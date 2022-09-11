<?php 
session_start();
require('../library.php');

if(isset($_SESSION['form'])){
	$form = $_SESSION['form'];
}else{
	header('Location: index.php');
	exit();
}

$errors = [];
$myname = "shota";
$mymail = "shota0929jp@icloud.com";
$registation_subject = " UC Davis 留学生交流サイト この度はご登録いただきありがとうございます";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$db = dbconnect();
	$stmt = $db->prepare('insert into members (name,email,password,picture) VALUES (?,?,?,?)');
	if(!$stmt){
		die($db->error);
	}
	$password = password_hash($form['password'],PASSWORD_DEFAULT);
	$stmt->bind_param('ssss',$form['name'],$form['email'],$password,$form['image']);
	$success = $stmt->execute();
	if(!$success){
		die($db->error);
	}


	//登録ユーザと管理者へ仮登録されたメール送信
       
	$mailTo = $mail.','.$companymail;
    $body = <<< EOM
    この度はご登録いただきありがとうございます。
	本登録致しました。
EOM;
       mb_language('ja');
       mb_internal_encoding('UTF-8');
   
       //Fromヘッダーを作成
       $header = 'From: ' . mb_encode_mimeheader($myname). ' <' . $mymail. '>';
   
       if(mb_send_mail($mailTo, $registation_mail_subject, $body, $header, '-f'. $mymail)){          
           $message['success'] = "会員登録しました";
       }else{
           $errors['mail_error'] = "メールの送信に失敗しました。";
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
	<title>会員登録</title>

	<link rel="stylesheet" href="../style.css" />
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>UCデービス留学生交流サイト</h1>
		</div>

		<div id="content">
			<p>記入した内容を確認して、「登録する」ボタンをクリックしてください</p>
			<form action="" method="post">
				<dl>
					<dt>ニックネーム</dt>
					<dd><?php echo h($form['name']);?></dd>
					<dt>メールアドレス</dt>
					<dd><?php echo h($form['email']);?></dd>
					<dt>パスワード</dt>
					<dd>
						セキュリティのため非表示
					</dd>
					<dt>写真</dt>
					<dd>
						<img src="../member_picture/<?php echo h($form['image']);?>" width="100" alt="" />
					</dd>
				</dl>
				<div><a class="btn" href="index.php?action=rewrite">書き直す</a>  <input class="btn" type="submit" value="登録する" /></div>
			</form>
		</div>

	</div>
</body>

</html>