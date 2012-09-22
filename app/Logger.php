<?php

class Logger extends Nette\Diagnostics\Logger
{
	public function log($message, $priority = self::INFO)
	{
		$file = str_replace(' @@  ', '', $message[3]);
		$c = curl_init();
		curl_setopt($c, CURLOPT_VERBOSE, 1);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_URL, 'http://trnila.eu:8080/report');
		curl_setopt($c, CURLOPT_POST, true);
		echo __DIR__ . '/../logs/' . $file;
		curl_setopt($c, CURLOPT_POSTFIELDS, array("debugger" => "@" . __DIR__ . '/../logs/' . $file));
		echo curl_exec($c);

		echo ' ';
		exit;
		parent::log($message, $priority);


	}
}
/*
 $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_URL, _VIRUS_SCAN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    // same as <input type="file" name="file_box">
    $post = array(
        "file_box"=>"@/path/to/myfile.jpg",
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $response = curl_exec($ch);*/