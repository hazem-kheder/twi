<?php
class TwitterAPI {

// Base variables
 private $TwitterUser; //twitter account
 private $TweetsNum;//number of tweets
 public $DBHost; // MySQL Hostname
 public $DBUser; // MySQL Username
 public $DBPass; // MySQL Password
 public $DBName; // MySQL Database
 private  $oauth_access_token;
 private   $oauth_access_token_secret;
 private   $consumer_key;
 private  $consumer_secret;

//
 public function __construct(array $settings)
    {
        if (!in_array('curl', get_loaded_extensions())) 
        {
            throw new Exception('You need to install cURL, see: http://curl.haxx.se/docs/install.html');
        }
        
        if (!isset($settings['oauth_access_token'])
            || !isset($settings['oauth_access_token_secret'])
            || !isset($settings['consumer_key'])
            || !isset($settings['consumer_secret'])
			|| !isset($settings['TwitterUser'])
			|| !isset($settings['TweetsNum'])
			|| !isset($settings['DBHost'])
			|| !isset($settings['DBUser'])
			|| !isset($settings['DBPass'])
			|| !isset($settings['DBName'])
			)
        {
            throw new Exception('Make sure you are passing in the correct parameters');
        }

        $this->oauth_access_token = $settings['oauth_access_token'];
        $this->oauth_access_token_secret = $settings['oauth_access_token_secret'];
        $this->consumer_key = $settings['consumer_key'];
        $this->consumer_secret = $settings['consumer_secret'];
		$this->TwitterUser= $settings['TwitterUser'];
		$this->TweetsNum=$settings['TweetsNum'];
		$this->DBHost=$settings['DBHost'];
		$this->DBName=$settings['DBName'];
		$this->DBPass=$settings['DBPass'];
		$this->DBUser=$settings['DBUser'];
    }
	

//
function buildBaseString($baseURI, $method, $params) {
        $r = array();
        ksort($params);
        foreach($params as $key=>$value){
            $r[] = "$key=" . rawurlencode($value);
        }
        return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
    }
//
function buildAuthorizationHeader($oauth) {
        $r = 'Authorization: OAuth ';
        $values = array();
        foreach($oauth as $key=>$value)
            $values[] = "$key=\"" . rawurlencode($value) . "\"";
        $r .= implode(', ', $values);
        return $r;
    }
	
//sending a request to Twitter
function sendRequest($method,$url,array $postfields,$getfield){
    
	if(!is_null($getfield))
	{
	$postfields = null;
	}
	

    $oauth = array( 
					'oauth_consumer_key' => $this->consumer_key,
                    'oauth_nonce' => time(),
                    'oauth_signature_method' => 'HMAC-SHA1',
                    'oauth_token' => $this->oauth_access_token,
                    'oauth_timestamp' => time(),
                    'oauth_version' => '1.0'
				   );
					if(!is_null($getfield))
					{
						$getfields = str_replace('?', '', explode('&', $getfield));
						foreach ($getfields as $g)
						{
							$split = explode('=', $g);
							
							/** In case a null is passed through */
							 if (isset($split[1]))
							 {
								 $oauth[$split[0]] = urldecode($split[1]);
							 }
						}
					}
					
					if (!is_null($postfields)) {
						foreach ($postfields as $key => $value) {
							$oauth[$key] = $value;
						}
					}

		
    $base_info = $this->buildBaseString($url, $method, $oauth);
    $composite_key = rawurlencode($this->consumer_secret) . '&' . rawurlencode($this->oauth_access_token_secret);
    $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
    $oauth['oauth_signature'] = $oauth_signature;

    // Make requests
    $header = array($this->buildAuthorizationHeader($oauth), 'Expect:');
    $options = array( CURLOPT_HTTPHEADER => $header,
                      CURLOPT_HEADER => false,
                      CURLOPT_URL => $url,
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_SSL_VERIFYPEER => false
					  );
					  
					  if (!is_null($postfields))
					  {
						  $options[CURLOPT_POSTFIELDS] = http_build_query($postfields);
					  }
					   else
					   {
						   if ($getfield !== '')
						   {
							  $options[CURLOPT_URL] .= $getfield;
						   }
					   }

    $feed = curl_init();
    curl_setopt_array($feed, $options);
   $json = curl_exec($feed);

        if (($error = curl_error($feed)) !== '')
        {
            curl_close($feed);

            throw new \Exception($error);
        }

        curl_close($feed);
		$data=$json;
        return $data;	
}



//function to insert tweets into database
function insertTweetsToDB(){
	
	$mysqli=new mysqli($this->DBHost,$this->DBUser,$this->DBPass,$this->DBName);
	
		if ($mysqli->connect_error) {
			throw new Exception('Connect failed: ' . $mysqli->connect_error);
			}
			else
			{
	$NullArray[]='';
	$info=$this->sendRequest('GET','https://api.twitter.com/1.1/statuses/user_timeline.json',$NullArray,'?screen_name='.$this->TwitterUser.'&count='.$this->TweetsNum);
	$infoArray=json_decode($info,true);
	//print_r($infoArray);
	
	foreach($infoArray as $items)
	{
		$id=$items['id_str'];
		
		$name=$items['user']['name'];
		$screenName=$items['user']['screen_name'];
		$text=$items['text'];
		
		if(!empty($items['quoted_status']))
		{
		$replayText=$items['quoted_status']['text'];
		
		$auth=$items['quoted_status']['user']['name'];
		
		}
		else
		{
		$replayText='';
		$auth='';	
		}
		$cDate=$items['created_at'];
		$favorited=$items['favorited'];
		$retweeted=$items['retweet_count'];
		$url=$items['user']['url'];
		$image=$items['user']['profile_image_url'];
		
		$query=$mysqli->query("INSERT INTO tweets(id,name,screen_name,text,date,favorited,retweeted,url,image,replay,replayuser) VALUES ('$id','$name','$screenName','$text','$cDate','$favorited','$retweeted','$url','$image','$replayText','$auth') ON DUPLICATE KEY UPDATE id=id");
		
	}
	return $mysqli->affected_rows;
	$query->close();
			}
}

//update tweets db
function updateTweets($id,$event){
	
	$mysqli=new mysqli($this->DBHost,$this->DBUser,$this->DBPass,$this->DBName);
	
		if ($mysqli->connect_error) {
			throw new Exception('Connect failed: ' . $mysqli->connect_error);
			}
			else
			{
	
		
		$query=$mysqli->query("UPDATE `tweets` SET `".$event."`=`".$event."`+1 WHERE `id`='$id'");
		
	}
	return $mysqli->affected_rows;
	$query->close();
			
}
//get twitter users
function getUsers($string)
{
	$regex="/(@[A-Za-z0-9_])\w+/i";
	// Check if there @ at tweet
	if(preg_match($regex, $string, $user))
		{
		
		   // make the urls hyper links
		   $string= preg_replace($regex, "<a href='https://twitter.com/".str_replace("@","",$user[0])."'>{$user[0]}</a> ", $string);
		   return $string;
		}
		else
		{
		   // if no urls in the tweet just return the text
		   return $string;
		}
	
}

//get URL in tweet
function getURL($string)
{
	$regex="/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/i";
	// Check if there is a url in the tweet
	if(preg_match($regex, $string, $url))
		{
		
		   // make the urls hyper links
		   $string= preg_replace($regex, "<a href=".$url[0].">{$url[0]}</a> ", $string);
		   return $string;
		}
		else
		{
		   // if no urls in the tweet just return the text
		   return $string;
		  
		}
}


}


?>