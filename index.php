<?php
session_start();
require_once("includes/TwitterAPI.php");
include_once("includes/settings.php");

$tweets=new TwitterAPI($settings);

//check if session alrady set
if(isset($_SESSION['oauth_token']) && isset($_SESSION['oauth_token_secret']) && isset($_GET['oauth_token']))
{
$request['oauth_token'] = $_SESSION['oauth_token'];
$request['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

if (isset($_GET['oauth_token']) && $request['oauth_token'] == $_GET['oauth_token']) {
   $_SESSION['auth']=1;
}

}
$html='
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/ajax.js" ></script>
<link rel="stylesheet" href="css/style.css">
<title>TweetToDB</title>
</head>
<body>';


if(isset($_SESSION['auth']))
{
							
$html.='
<div id="profile-pic"></div>
<div id="container">
<div id="avatar-image">
<div id="avb">
</div>
</div>
<div id="description">
<h1>Tweets From @ekomi</h1>
<div id="hiden" style="display:none;"></div>
</div>';



//tweets per page
$tweetPerPage=11;

//check tweets page & if it is numeic or not
if(empty($_GET['page']))
{
	$from=0;
	$to=$tweetPerPage;
}
elseif(is_numeric($_GET['page']))
{
	$page=$_GET['page'];
	$from=$tweetPerPage*$page;
	$to=$from+$tweetPerPage;
}

//connect to database and get tweets
$mysqli=new mysqli($tweets->DBHost,$tweets->DBUser,$tweets->DBPass,$tweets->DBName);

		if ($mysqli->connect_error) {
			echo 'Connect failed: ' . $mysqli->connect_error;
			exit;
			}
			
$query=$mysqli->query("SELECT * FROM `tweets` ORDER BY `id` DESC LIMIT $from,$to");

	if($query->num_rows>0)
	{
		while($row = $query->fetch_array(MYSQLI_ASSOC))
		{
			
			$tweet=$tweets->getUsers($tweets->getURL($row['text']));
			$html.='	
			<div class="tweets">
			<img src="'.$row['image'].'" alt="profil picture">
			<div class="text">'.$tweet.'</div>
			<div class="options">
			<span class="fav" id="'.$row['id'].'">
			<span>'.$row['favorited'].'</span><span><img src="images/fa.png" alt=""></span></span>
			<span class="ret" id="'.$row['id'].'">
			<span>'.$row['retweeted'].'</span><span><img src="images/re.png" alt=""></span></span>
			<span class="com" id="'.$row['id'].'" ">
			<span>&nbsp;</span><span><img src="images/co.png" alt=""></span></span>
			<div class="comment commentid'.$row['id'].'"></div>
			<div class="comment comment'.$row['id'].'" style="display:none;">
			<form id="form">
			<textarea name="comment" maxlength="160">@'.$row['screen_name'].' </textarea>
			<a class="sub" id="'.$row['id'].'">Tweet</a>
			</form>
			</div>
			</div>
			</div>';
		}
	}
$html.='</div>';

}
else
{
$getToken=$tweets->sendRequest('POST','https://api.twitter.com/oauth/request_token',array('oauth_callback'=>'http://www.zawity.com/twitter/index.php'),null);	
parse_str($getToken, $getfields);

$_SESSION['oauth_token'] = $getfields['oauth_token'];
$_SESSION['oauth_token_secret'] = $getfields['oauth_token_secret'];			

$html.='<div id="per">
<h1>Welcome to My twitter APP </h1>
<span>Please give this App an authorization to read & write on your profile</span>
<a href="https://api.twitter.com/oauth/authorize?oauth_token='.$getfields['oauth_token'].'">Authorize App on Twitter</a>
</div>';
	
}


$html.='
</body>
</html>';

echo $html;
?>