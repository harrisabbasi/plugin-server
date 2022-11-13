<?php
function check_if_script_running($filename){

    $lifelimit = 60*60; // in Second lifetime to prevent errors
    /* check lifetime of file if exist */
    if(file_exists($filename)){
       $lifetime = time() - filemtime($filename);
    }else{
       $lifetime = 0;
    }
    /* check if file exist or if file is too old */
    if(!file_exists($filename) ||  $lifetime > $lifelimit){
        if($lifetime > $lifelimit){
            unlink($filename); //Suppress if exist and too old
        }
        $file=fopen($filename, "w+"); // Create lockfile
        if($file == false){
            die("file didn't create, check permissions");
        }

        return true;
    }else{
        exit(); // Process already in progress
    }
}

function set_up_curl(){

	$ch = curl_init();

	$referer = 'http://localhost/plugin_server/dashboard/cronjob/cronjob_realtime.php';

	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,2);

	curl_setopt($ch, CURLOPT_REFERER, $referer);

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	curl_setopt($ch, CURLOPT_FAILONERROR, true);

	return $ch;
}

?>