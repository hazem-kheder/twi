<?php
include_once("TwitterAPI.php");
include_once("settings.php");

if(!empty($_GET) && $_GET['key']==$settings['oauth_access_token_secret'])
{
$GetTweets= new TwitterAPI($settings);

$UserTweets=$GetTweets->insertTweetsToDB();
if(!empty($UserTweets))
{
echo "All new tweets have been inserted to database.";	
}
else
{
echo "We couldn't find new tweets.";	
}
}
?>