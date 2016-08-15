<?php

// Prevent browser access
if ( !isset( $argv ) ) exit;

error_reporting(0);

$path       = "C:\\path\\to\\directory";
$email_to   = "email@domain.com";
$email_from = "email@domain.com";
$ignore     = array( "200", "301", "302", "303", "401", "999" ); // LinkedIn returns 999 so we'll just ignore it I suppose.
$results    = array();
$dir        = new RecursiveDirectoryIterator( $path );

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

        			if ( !in_array( $response_code, $ignore ) )
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

    $message .= "\r\n" . "This email was generated from a script on the web server. For more infomation view the scheduled task.";
}

return mail( $email_to, $subject, $message, $headers );
