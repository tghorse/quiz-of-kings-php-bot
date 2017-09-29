<?php

function checkWithProbability($probability=0.1, $length=10000000)
{
   $test = mt_rand(1, $length);
   return $test<=$probability*$length;
}

function dumpDecode($str) {
    $r = [];

    foreach (explode("\r\n", $str) as $d) {
        $x = explode(':', $d);
        $r[$x[0]] = $x[1];
    }
    return $r;
}

function curlPost($url, $data = NULL, $headers = NULL) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);

    curl_setopt($ch ,CURLOPT_CAINFO, realpath("qok.pem"));


    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);

    if (curl_error($ch)) {
        //trigger_error('Curl Error:' . curl_error($ch));
        throw new Exception('Curl Error:' . curl_error($ch), 99);
    }



    curl_close($ch);



    $headers = array();

    $data = @explode("\r\n\r\n", $response);
    if (empty($data[2])) {
        throw new Exception('Empty response', 88);
    }

    //var_dump('<pre>',$data);die();
    $headersrow = explode("\n", $data[1]);
    $headers['status'] = trim($headersrow[0]);

    //array_shift($headersrow);

    foreach ($headersrow as $part) {
        $middle = explode(":", $part);
        $headers[trim($middle[0])] = trim(@$middle[1]);
    }

    return array("content" => $data[2], "response" => $response, "header" => $headers);
}

function loadGames($sessionId) {
    $data = "asset_version:10
res_type_id:4
locale:fa
palang:1
app_version:1.12.3924
client:Android
last_games_fetch:0
:";

    $data = ( dumpDecode($data));
    $url = "https://secureapi.quizofkings.com:443/api/v1/player/load";
    $r = curlPost($url, $data, ["Cookie: PHPSESSID=$sessionId; path=/; HttpOnly"]);
//var_dump('<pre>',json_decode($r['content'],true)['data']['games']);
    return json_decode($r['content'], true);
}

function fetchGame($sessionId, $gameId) {
    $data = "locale:fa
asset_version:10
app_version:1.12.3924
device_type:Hol-U19
palang:1
client:Android
:";

    $data = ( dumpDecode($data));
    $data['game_id'] = $gameId;
    $url = "https://secureapi.quizofkings.com:443/api/v1/game/fetch";
    $r = curlPost($url, $data, ["Cookie: PHPSESSID=$sessionId; path=/; HttpOnly"]);
    return json_decode($r['content'], true);
}

function gameSetCat($sessionId, $gameId) {
//sleep(rand(1,5));
    $data = "hint_used:0
palang:1
locale:fa
asset_version:10
app_version:1.12.3924
client:Android
category_id:2
:";



    $data = ( dumpDecode($data));

    $cats = [29, 20, 142, 13, 141, 9, 17, 4];
    $data['category_id'] = $cats[rand(0, count($cats) - 1)];
    $data['game_id'] = $gameId;
    $url = "https://secureapi.quizofkings.com:443/api/v1/game/setcategory";
    $r = curlPost($url, $data, ["Cookie: PHPSESSID=$sessionId; path=/; HttpOnly"]);
// var_dump('<pre>',json_decode($r['content'],true));
    return json_decode($r['content'], true);
}

function submitAnswers($sessionId, $roundId, $questions) {
    $data = "locale:fa
asset_version:10
app_version:1.12.3924
palang:1
client:Android
:";

    $data = (dumpDecode($data));
    $answersData = [];
    $answersData['round_id'] = $roundId;
    /*
      $questions=[
      ['id'=>114200,'answer'=>1],
      ['id'=>396103,'answer'=>1],
      ['id'=>708928,'answer'=>1],
      ]; */

    $answersData['answers'] = [];
    $sleep = 0;
    
    foreach ($questions as $i => $q) {
		//answer question incurrectly by probability of 3/10
        if(checkWithProbability(0.3)){
            $answer = ($q['answer']+mt_rand(1,3))%4;
        }else{
            $answer = $q['answer'];
        }
        
		$t = mt_rand(1, 3);
        $a = [
            "status" => 1,
            "question_number" => $i + 1,
            "time" => $t,
            "question_id" => $q['id'],
            "answer" => $answer,
            "rate" => 0,
            "hint" => 0
        ];

        $sleep+=$t;
        $answersData['answers'][] = $a;
    }

//sleep($sleep);
//var_dump('<pre>',$answersData['answers']);die;
    $data['submittedAnswers'] = json_encode([$answersData]);

    $url = "https://secureapi.quizofkings.com:443/api/v1/question/submit";
    $r = curlPost($url, $data, ["Cookie: PHPSESSID=$sessionId; path=/; HttpOnly"]);
// var_dump('<pre>',json_decode($r['content'],true));
    return json_decode($r['content'], true);
}

function randomRival($sessionId) {
    $data = "asset_version:10
palang:1
view_ad:0
app_version:1.12.3924
locale:fa
client:Android
extra_game:0
:";

    $data = ( dumpDecode($data));

    $url = "https://secureapi.quizofkings.com:443/api/v1/game/random";
    $r = curlPost($url, $data, ["Cookie: PHPSESSID=$sessionId; path=/; HttpOnly"]);
// var_dump('<pre>',json_decode($r['content'],true));

    return json_decode($r['content'], true);
}

function useInviteCode() {

}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generateRandomHex($length = 10) {
    $characters = '0123456789ABCDEF';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generateRandomNumber($length = 10) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generateDefaultParams() {
    $data = "asset_version:10
res_type_id:4
app_version:1.12.3924
palang:1
client:Android
locale:fa";

    $data = ( dumpDecode($data));
    $data['signature'] = generateRandomHex(75);
    $data['UDID'] = generateRandomNumber(15);
    $data['device_type'] = generateRandomString(3) . '-' . generateRandomString(2) . generateRandomNumber(2);
    return $data;
}

function gfastRegister() {
 
    $data = "asset_version:10
res_type_id:4
app_version:1.12.3924
palang:1
client:Android
locale:fa
:";

    $data = dumpDecode($data);
    $data = array_merge($data, generateDefaultParams());
    $url = "https://secureapi.quizofkings.com:443/api/v1/player/register/fast";
    $r = curlPost($url, $data, []);
// print_r(json_decode($r['content'],true));
// print_r($r);die;
    $rr = json_decode($r['content'], true);
    preg_match('/PHPSESSID=(?<id>\w+)/i', $r['header']['Set-Cookie'], $ms);

    $rr['sessionId'] = $ms['id'];
    return $rr;
}

function gsetUserPass($sessionId, $user, $pass) {
    
    $data = "asset_version:10
palang:1
locale:fa
app_version:1.12.3924
client:Android
:";

    $data = dumpDecode($data);
    $data = array_merge($data, generateDefaultParams());
    $data['username'] = $user;
    $data['confirm_new_password'] = $data['new_password'] = $pass;

    $url = "https://secureapi.quizofkings.com:443/api/v1/player/profile/edit";
    $r = curlPost($url, $data, ["Cookie: PHPSESSID=$sessionId; path=/; HttpOnly"]);
// var_dump('<pre>',json_decode($r['content'],true));

    return json_decode($r['content'], true);
}

function registerUser($user = null, $pass = null) {
    $data = gfastRegister();
    if (!$user)
        $user = randomName();
    if (!$pass)
        $pass = generateRandomNumber(10);
    // echo "user= `$user`\tpass= `$pass`\t session= `{$data['sessionId']}`\n";
    $r = gsetUserPass($data['sessionId'], $user, $pass);
    $r['sessionId'] = $data['sessionId'];
    $r['user'] = $user;
    $r['pass'] = $pass;
    return $r;
}

function randomName() {
    $stopwords = array("shah", "mamad", "shakh", "above", "king", "after", "soltan", "again", "kabir", "all", "almost", "alone", "along", "already", "also", "although", "always", "am", "among", "amongst", "amoungst", "amount", "an", "and", "another", "any", "anyhow", "anyone", "anything", "anyway", "anywhere", "are", "around", "as", "at", "back", "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom", "but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven", "else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own", "part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");

//Generate a random forename.
    $random_name = $stopwords[mt_rand(0, sizeof($stopwords) - 1)];

//Generate a random surname.
    $random_surname = $stopwords[mt_rand(0, sizeof($stopwords) - 1)];

    return $random_name . '' . $random_surname;
}

function userSearch() {
  
}

function gameInvite($sessionId, $playerId) {
    $data = "asset_version:10
palang:1
view_ad:0
locale:fa
app_version:1.12.3924
extra_game:0
client:Android
:";

    $data = dumpDecode($data);
    $data = array_merge($data, generateDefaultParams());
    $data['player_id'] = $playerId;

    $url = "https://secureapi.quizofkings.com:443/api/v1/game/invite";
    $r = curlPost($url, $data, ["Cookie: PHPSESSID=$sessionId; path=/; HttpOnly"]);
// var_dump('<pre>',json_decode($r['content'],true));

    return json_decode($r['content'], true);
}

function acceptGame($sessionId, $gameId) {
   

    $data = "asset_version:10
palang:1
view_ad:0
locale:fa
app_version:1.12.3924
extra_game:0
client:Android
device_type:Hol-U19
:";

    $data = dumpDecode($data);
    $data = array_merge($data, generateDefaultParams());
    $data['game_id'] = $gameId;

    $url = "https://secureapi.quizofkings.com:443/api/v1/game/accept";
    $r = curlPost($url, $data, ["Cookie: PHPSESSID=$sessionId; path=/; HttpOnly"]);
// var_dump('<pre>',json_decode($r['content'],true));

    return json_decode($r['content'], true);
}

function giveupGame($sessionId, $gameId) {
  
    $data = "locale:fa
asset_version:10
app_version:1.12.3924
palang:1
client:Android
:";

    $data = dumpDecode($data);
    $data = array_merge($data, generateDefaultParams());
    $data['game_id'] = $gameId;

    $url = "https://secureapi.quizofkings.com:443/api/v1/game/giveup";
    $r = curlPost($url, $data, ["Cookie: PHPSESSID=$sessionId; path=/; HttpOnly"]);
// var_dump('<pre>',json_decode($r['content'],true));

    return json_decode($r['content'], true);
}
