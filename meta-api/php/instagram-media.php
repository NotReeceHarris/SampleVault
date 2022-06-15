<?php
$token = 'YOUR TOKEN'; 
$posts = [];

if ($token) {
    $url = "https://graph.instagram.com/v14.0/me?access_token=".$token;

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $resp = json_decode(curl_exec($curl));
    curl_close($curl);

    if ($resp->id != null) {
        $url = "https://graph.instagram.com/v14.0/".$resp->id."/media?access_token=".$token;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    
        $resp = json_decode(curl_exec($curl));

        if ( !empty($resp->data) ) {
            foreach ($resp->data as $post) {
                $url = "https://graph.instagram.com/".$post->id."?fields=media_url,permalink&access_token=".$token;
                
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            
                $resp = json_decode(curl_exec($curl));
                $posts[] = $resp;
            }
        }
    }
}

?>
