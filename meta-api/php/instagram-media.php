<?php
// This is set up for wordpress using the advanced custom field plugin
// you can easily adapt this code to work for you :)


// Only run if token present
if (get_field('instagram_token', 'option')) {
    echo 'check 1';
    $token = get_field('instagram_token', 'option'); 
    $updated = get_field('instagram_refresh', 'option');
    $user_id = null;

    $posts = [];

    // Get the user ID from the token
    $url = "https://graph.instagram.com/v14.0/me?access_token=".$token;

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $resp = json_decode(curl_exec($curl));
    curl_close($curl);
    $user_id = $resp->id;

    // Validate token
    if ($user_id != null) {
        $url = "https://graph.instagram.com/v14.0/".$user_id."/media?access_token=".$token;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    
        $resp = json_decode(curl_exec($curl));

        // Updated dead token
        if ( empty($resp->data) ) {
            $url = "https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=".$token;

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

            $resp = json_decode(curl_exec($curl));
            curl_close($curl);

            if ($resp->access_token != null) {
                $token = $resp->access_token;
                update_field('instagram_token', $resp->access_token, 'option');
                update_field('instagram_refresh', time() + $resp->expires_in, 'option');
            }
        }
    }

    // Get the posts
    if ($user_id != null) {
        $url = "https://graph.instagram.com/v14.0/".$user_id."/media?access_token=".$token;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        foreach ($resp->data as $post) {
            $posts[] = $post->id;
        }

        // Update post
        if (!empty($posts)) {
            $loopIndex = 1;
            foreach ($posts as $post) {
                if ($loopIndex == 5) {
                    break;
                }
                $url = "https://graph.instagram.com/".$post."?fields=media_url,permalink&access_token=".$token;

                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            
                $resp = json_decode(curl_exec($curl));

                if (get_field('instagram_post_'.$loopIndex, 'option')['post_url'] != $resp->permalink || get_field('instagram_post_'.$loopIndex, 'option')['image_url'] != $resp->media_url) {
                    update_field('instagram_post_'.$loopIndex.'_post_url', $resp->permalink, 'option');
                    update_field('instagram_post_'.$loopIndex.'_image_url', $resp->media_url, 'option');
                }

                curl_close($curl);
                $loopIndex++;
            }
        }
    } 
}

?>
