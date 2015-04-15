<?php

// Prevent browser access
if ( !isset( $argv ) ) exit;

error_reporting(0);

$dir = "YOUR_WWW_ROOT_DIR";
$email_to = "YOUR_EMAIL_TO";
$email_from = "YOUR_EMAIL_FROM";

$results = array();

$dir = new RecursiveDirectoryIterator( $dir );

foreach( new RecursiveIteratorIterator( $dir ) as $file )
{
    $pathinfo = pathinfo( $file );
	
	if ( isset( $pathinfo['extension'] ) && isset( $pathinfo['filename'] ) && "config" == $pathinfo['extension'] && "web" == $pathinfo['filename'] )
	{
		$web_config = simplexml_load_file( $file );

		$dir = isset( $pathinfo['dirname'] ) ? $pathinfo['dirname'] : "Error getting directory";

        if ( !is_object( $web_config ) )
        	continue;

        if ( $urls = $web_config->xpath("//action/@url") )
        {
        	foreach( $urls as $url )
        	{
        		$url = ( (string)$url );

        		if ( false !== strpos( $url, "{" ) )
        			continue;

        		if ( $headers = get_headers( $url, 0 ) )
        		{
        			$response_code = substr( $headers[0], 9, 3 );

        			if ( "200" !== $response_code )
        				$results[ $response_code ][] = "Dir: " . $dir . " URL: " . $url;
        		}
        		else
        			$results['No Header Returned'][] = "Dir: " . $dir . " URL: " . $url;
        	}
        }
	}
}

asort( $results, SORT_NUMERIC );

$subject = 'web.config redirect status report ' . date("F j, Y, g:i a");

$headers = 'From: ' . $email_from . "\r\n" .
	       'Reply-To: ' . $email_from . "\r\n" .
	       'X-Mailer: PHP/' . phpversion();

if ( !$results )
	$message = "No issues found!";
else
{
	$message = "";

	foreach( $results as $status => $array )
	{
		$message .= $status . "\r\n";

		foreach( $array as $result_url )
			$message .= $result_url . "\r\n";

		$message .= "\r\n";
	}
}

return mail( $email_to, $subject, $message, $headers );
