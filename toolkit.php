<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include './functions.php';



switch (@$argv[1]) {
    case 'create.user':
        $num=(int)@$argv[2];
        if(!$num){$num=1;}
        echo "creating $num users:\n";
        for($i=0;$i<$num;$i++){
            $data=registerUser();
        echo "user= `{$data['user']}`\tpass= `{$data['pass']}`\t session= `{$data['sessionId']}`\n";
            
        }
        break;

    default:
        echo "invalid command\n";
        break;
}