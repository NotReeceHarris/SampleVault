<?php

<?php
$config = [
    'callback' => '',
    'enabled' => TRUE,
    'keys' => [
        'id' => '',
        'secret' => '',
    ],
    'scopes' => array('r_liteprofile', 'r_emailaddress', 'w_member_social', 'r_fullprofile')
];

$authurl = 'https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=' . $config['keys']['id'] . '&redirect_uri=' . urlencode($config['callback']) . '&state=SignupAuth&scope=' . urlencode(implode(" ", $config['scopes']));

if ($_SERVER['REQUEST_METHOD'] == 'GET' && empty($_GET['code'])) {
    echo '<script>window.location.href = "' . $authurl . '"</script>';
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['code'])) {
    //1. received the auth token
    $code =  $_GET['code'];

    //2. POST request to fetch the access token
    $ch = curl_init('https://www.linkedin.com/oauth/v2/accessToken');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

    $redirectUri = urlencode($config['callback']);
    $gt = urlencode('authorization_code');
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=" . $gt . "&code=" . $code . "&redirect_uri=" . $redirectUri . "&client_id=" . $config['keys']['id'] . "&client_secret=" . $config['keys']['secret']);
    // execute!
    $response = curl_exec($ch);
    // close the connection, release resources used
    curl_close($ch);
    // $response contains
    $json = json_decode($response);

    $accessToken = $json->access_token;

    // 3. GET request for user data (does not contain email) using accessToken in Authorization header
    $url = 'https://api.linkedin.com/v2/me?projection=(id,localizedLastName,localizedFirstName,profilePicture(displayImage~:playableStreams))';
    $crl = curl_init();

    curl_setopt($crl, CURLOPT_URL, $url);
    curl_setopt($crl, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($crl, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$accessToken));

    $userDataJson = curl_exec($crl);

    $userData = json_decode($userDataJson,true);
    $userName = $userData['localizedFirstName'].' '.$userData['localizedLastName'];
    $userProfilePic = $userData['profilePicture']['displayImage~']['elements'][0]['identifiers'][0]['identifier'];

    curl_close($crl);

    //  4. GET request for user email
    $email = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';
    $emailcrl = curl_init();

    curl_setopt($emailcrl, CURLOPT_URL, $email);
    curl_setopt($emailcrl, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($emailcrl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($emailcrl, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$accessToken));

    $email_response = curl_exec($emailcrl);

    $userEmail = json_decode($email_response,true);

    $userEmail = $userEmail['elements'][0]['handle~']['emailAddress'];

    curl_close($emailcrl);

    echo $userEmail;
}
