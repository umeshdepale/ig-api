<?php

if(!defined('41PH4_1337')){
    http_response_code(404);
    exit();
}
global $ch;
$ch = curl_init();
function DefaultUA(){
    $resolutions = ['720x1280', '320x480', '480x800', '1024x768', '1280x720', '768x1024', '480x320'];
    $versions = ['GT-N7000', 'SM-N9000', 'GT-I9220', 'GT-I9100'];
    $dpis = ['120', '160', '320', '240'];
    $ver = $versions[array_rand($versions)];
    $dpi = $dpis[array_rand($dpis)];
    $res = $resolutions[array_rand($resolutions)];
    return 'Instagram 4.'.mt_rand(1,2).'.'.mt_rand(0,2).' Android ('.mt_rand(10,11).'/'.mt_rand(1,3).'.'.mt_rand(3,5).'.'.mt_rand(0,5).'; '.$dpi.'; '.$res.'; samsung; '.$ver.'; '.$ver.'; smdkc210; en_US)';
}
function GUID(){
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}
function DeviceID($guid){
    return 'android-'.$guid;
}
function SignData($data){
    return 'signed_body='.hash_hmac('sha256', $data, 'b4a23f5e39b5929e0666ac5de94c89d1618a2916').'.'.urlencode($data).'&ig_sig_key_version=4&d=0';
}
function GetIP(){
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
    $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
    $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
    $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
    $ip = $_SERVER['REMOTE_ADDR'];
    else
    $ip = '103.108.220.137';
    return($ip);
}
function cURL($url, $post = '', $headers = '', $proxy = ''){
    global $ch;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, DefaultUA());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if(!empty($post)){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    if(!empty($headers)){
        if(is_array($headers)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
    }
    if(!empty($proxy)){
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
    }
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    if($header_size){
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $output = ['Header' => $header, 'Body' => $body];
    } else {
        $body = $response;
        $output = ['Header' => false, 'Body' => $body];
    }
    return (object) $output;
}
function Send_cURL($url, $post = '', $headers = '', $proxy = ''){
    global $ch;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, DefaultUA());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if(!empty($post)){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    if(!empty($headers)){
        if(is_array($headers)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
    }
    if(!empty($proxy)){
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
    }
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($code >= 200 && $code < 300){
        return true;
    } else {
        return false;
    }
}

function Multi_cURL($datas){
    $mh = curl_multi_init();
    foreach($datas as $i => $data){
        $ch[$i] = curl_init($data[0]);
        curl_setopt($ch[$i], CURLOPT_NOBODY, true);
        curl_setopt($ch[$i], CURLOPT_HEADER, false);
        curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch[$i], CURLOPT_FAILONERROR, true);
        curl_setopt($ch[$i], CURLOPT_USERAGENT, DefaultUA());
        curl_setopt($ch[$i], CURLOPT_POST, true);
        curl_setopt($ch[$i], CURLOPT_POSTFIELDS, $data[1]);
        curl_setopt($ch[$i], CURLOPT_HTTPHEADER, [$data[2]]);
        if(isset($data[3])){
            curl_setopt($ch[$i], CURLOPT_PROXY, $data[3]);
        }
        curl_multi_add_handle($mh, $ch[$i]);
    }
    do{
        $execReturnValue = curl_multi_exec($mh, $runningHandles);
    } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
    while ($runningHandles && $execReturnValue == CURLM_OK){
        $numberReady = curl_multi_select($mh);
        if ($numberReady != -1) {
            do{
                $execReturnValue = curl_multi_exec($mh, $runningHandles);
            } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
        }
    }
    if ($execReturnValue != CURLM_OK) {
        trigger_error("Curl multi read error $execReturnValue\n", E_USER_WARNING);
    }
    curl_multi_close($mh);
}
function ParseCookieHeader($header){
    if(preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $cookie)){
        $cookies = $cookie[1];
    }
    return 'Cookie: '.implode('; ', $cookies).';';
}
function GetCSRFToken($cookies){
    if(preg_match("'csrftoken=([a-zA-Z-0-9+/=]+);'", $cookies, $matches)){
        return $matches[1];
    } else {
        return false;
    }
}
function GetMediaID($media_url){
    $request = cURL('http://api.instagram.com/oembed/?url='.$media_url);
    $response = @json_decode($request->Body);
    if(isset($response->media_id)){
        $output = (object) ['success' => true, 'media_id' => $response->media_id, 'uploaded_by' => $response->author_name, 'user_id' => $response->author_id, 'thumbnail' => $response->thumbnail_url, 'caption' => $response->title, 'provided_url' => $media_url];
        return $output;
    } else {
        return false;
    }
}
function Login($username, $password){
    $guid=GUID();
    $device_id=DeviceID($guid);
    $data=('{"device_id":"').$device_id.('","guid":"').$guid.('","username":"').$username.('","password":"').$password.('","Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"}');
    $data=SignData($data);
	$request=cURL(('https://i.instagram.com/api/v1/accounts/login/'),$data);
    $headers=$request->Header;
    $response=json_decode($request->Body);
    if(isset($response->status)){
        if($response->status ==('ok')){
            $cookies=ParseCookieHeader($headers);
            $chd=curl_init();
            $result=curl_exec($chd);
            curl_close($chd);
            $output=(object)[('success') =>true,('username') =>$response->logged_in_user->username,('profile_pic') =>$response->logged_in_user->profile_pic_url,('name') =>$response->logged_in_user->full_name,('user_id') =>$response->logged_in_user->pk,('is_private') =>$response->logged_in_user->is_private,('cookies') =>$cookies];
        }else if($response->status ==('fail')){
            if($response->error_type ==('bad_password')){
                $output=(object)[('success') =>false,('error') =>('invalid_credentials')];
        }else if($response->error_type ==('checkpoint_logged_out')){
            $output=(object)[('success') =>false,('error') =>('blocked'),('verify_url') =>$response->checkpoint_url];
        }else{
            $output=(object)[('success') =>false,('error') =>('unknown')];
        }
        }else{
            $output=(object)[('success') =>false,('error') =>('unknown')];
        }
        }else{
            $output=(object)[('success') =>false,('error') =>('unknown')];
        }
        return $output;
}
function GetProfile($userid){
    $request = cURL('https://i.instagram.com/api/v1/users/'.$userid.'/info/');
    return json_decode($request->Body);
}
function GetMedia($username, $cookies){
    $request = cURL('https://i.instagram.com/api/v1/feed/user/'.$username.'/username/', '', [$cookies]);
    $response = json_decode($request->Body);
    if(isset($response->status)){
        if($response->status == 'ok'){
            $media = array_slice($response->items, 0, 12);
            return $media;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function GetFeed($cookies){
    $request = cURL('https://i.instagram.com/api/v1/feed/timeline/', '', [$cookies]);
    $response = json_decode($request->Body);
    if(isset($response->status)){
        if($response->status == 'ok'){
            return $response;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function ValidCookies($cookies){
    $feed = GetFeed($cookies);
    if($feed){
        return true;
    } else {
        return false;
    }
}
function LikeMedia($media_id, $userid, $csrftoken, $cookies){
    $guid = GUID();
    $device_id = DeviceID($guid);
    $data = '{"module_name":"feed_timeline","media_id":"'.$media_id.'","_csrftoken":"'.$csrftoken.'","_uid":"'.$userid.'","_uuid":"'.$guid.'"}';
    $data = SignData($data);
    $request = Send_cURL('https://i.instagram.com/api/v1/media/'.$media_id.'/like/', $data, [$cookies]);
    return $request;
}
function FollowUser($to_user, $from_user, $csrftoken, $cookies){
    $guid = GUID();
    $device_id = DeviceID($guid);
    $data = '{"_csrftoken":"'.$csrftoken.'","user_id":"'.$to_user.'","_uid":"'.$from_user.'","_uuid":"'.$guid.'"}';
    $data = SignData($data);
    $request = Send_cURL('https://i.instagram.com/api/v1/friendships/create/'.$to_user.'/', $data, [$cookies]);
    return $request;
}
function Comment($media_id, $comment, $userid, $csrftoken, $cookies){
    $guid = GUID();
    $device_id = DeviceID($guid);
    $data = '{"_csrftoken": "'.$csrftoken.'", "_uid": "'.$userid.'", "_uuid": "'.$guid.'", "comment_text": "'.$comment.'"}';
    $data = SignData($data);
    $request = Send_cURL('https://i.instagram.com/api/v1/media/'.$media_id.'/comment/', $data, [$cookies]);
    return $request;
}
