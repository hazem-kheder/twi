<?php
session_start();
require_once("TwitterAPI.php");
include_once("settings.php");

$Tweets= new TwitterAPI($settings);

if(!empty($_GET))
{

//add comment
if(!empty($_GET['id']) && is_numeric($_GET['id']) && !empty($_GET['comment']))
{
	$id=$_GET['id'];
	$comment=htmlentities(addslashes($_GET['comment']));
	
	$postfields = array(
	'status'=>$comment,
	'in_reply_to_status_id'=>$id
	
);

$addComment=$Tweets->sendRequest('POST','https://api.twitter.com/1.1/statuses/update.json',$postfields,null);

if(!empty($addComment))
{
	
	echo "<div class='ok'>Your comment added successfully</div>";
}
else
{
	echo "<div class='error'>Something wrong happened!<div>";
}

}
//favorit
if(!empty($_GET['id']) && is_numeric($_GET['id']) && $_GET['action']=='ret')
{
	$id=$_GET['id'];
	$postfields = array(
	'id'=>$id
	);
$addRet=$Tweets->sendRequest('POST','https://api.twitter.com/1.1/statuses/retweet/'.$id.'.json',$postfields,null);


if(!empty($addRet))
{
	$update=$Tweets->updateTweets($id,"retweeted");
	echo "<div class='ok'>You have retweeted this tweet</div>";
}
else
{
	echo "<div class='error'>Something wrong happened!</div>";
}	
}
//retweet

if(!empty($_GET['id']) && is_numeric($_GET['id']) && $_GET['action']=='fav')
{

	$id=$_GET['id'];
	$postfields = array(
	'id'=>$id
	);
$addFAV=$Tweets->sendRequest('POST','https://api.twitter.com/1.1/favorites/create.json',$postfields,null);

if(!empty($addFAV))
{
	$update=$Tweets->updateTweets($id,"favorited");
	echo "<div class='ok'>You have favorited this tweet</div>";
}
else
{
	echo "<div class='error'>Something wrong happened!</div>";
}	
	
	
}


}

?>