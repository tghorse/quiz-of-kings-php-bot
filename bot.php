<?php

include 'functions.php';


date_default_timezone_set('Asia/Tehran');
echo date("\n[Y-m-d H:i:s]\n");
// echo "random startup delay . . . \n";
// sleep(mt_rand(1,5));

$sessionList=[
];

foreach($sessionList as $sessionId){
	playGames($sessionId);	
}




function playGames($sessionId, $depth = 0) {
    $t1 = time();
    echo "loading games . . .";
    try {
        $gamesData = loadGames($sessionId);
    } catch (Exception $exc) {
        if ($depth > 9) {
            echo "max depth riched. exiting . . .\n";
            exit(-1);
        }

        echo " [Failed with exception: " . $exc->getMessage() . "]\n";
        echo "try again . . .\n";
        sleep(2);
        playGames($sessionId, $depth + 1);
        return;
    }


    //print_r($gamesData);
    if (!empty($gamesData['status'])) {
        echo " [Success]\n";
    } else {
        if ($depth > 9) {
            echo "max depth riched. exiting . . .\n";
            //exit(-1);
			return false;
        }

        echo " [Failed]\n";
        echo "try again . . .\n";
        sleep(2);
        playGames($sessionId, $depth + 1);
        return;
    }

    echo "[ in " . (time() - $t1) . " s]\n";

    $activeGamesData = array_filter($gamesData['data']['games'], function($game) {
        return ($game['state'] == 3 || $game['state'] == 2);
    });

    if (count($activeGamesData) < 1) {
        echo "no games \n";

        for ($i = 0; $i < 50; $i++) {
            echo "random rival \n";
            randomRival($sessionId);
        }

        playGames($sessionId);
        return;
    }

    foreach ($activeGamesData as $game) {
        $t = time();

        if ($game['state'] == 3) {
            echo "fetching game . . .";
            $gameData = fetchGame($sessionId, $game['id']);
            if (!empty($gameData['status'])) {
                echo " [Success]\n";
            } else {
                echo " [Failed]\n";
                continue;
            }

            $roundData = end($gameData['data']['game']['rounds']);

//            if ((time() - $t) < 5) {
//                echo "manual delay . . .\n";
//                sleep(mt_rand(2, 5));
//            }

            echo "submiting game . . .";
            $submitData = submitAnswers($sessionId, $roundData['id'], $roundData['questions']);
            if (!empty($submitData['status'])) {
                echo " [Success]\n";
            } else {
                echo " [Failed]\n";
                continue;
            }
        }


        if ($game['state'] == 2) {
            echo "setting categury of game . . .";
            $gameData = gameSetCat($sessionId, $game['id']);
            if (!empty($gameData['status'])) {
                echo " [Success]\n";
            } else {
                echo " [Failed]\n";
                continue;
            }

            $roundData = $gameData['data']['round'];

//            if ((time() - $t) < 5) {
//                echo "manual delay . . .\n";
//                sleep(mt_rand(2, 5));
//            }

            echo "submiting game . . .";
            $submitData = submitAnswers($sessionId, $roundData['id'], $roundData['questions']);
            if (!empty($submitData['status'])) {
                echo " [Success]\n";
            } else {
                echo " [Failed]\n";
                continue;
            }
        }
    }
}














