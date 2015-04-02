<?php

/*
* Recurse through dir and retrieve a list of http redirects from web.config files.
* I just pipe the output into a file from the command line, but you could write to
* a file below if you wanted.
*/

// Prevent browser access
if ( !isset( $argv ) ) exit;

function list_web_config_http_redirects( $d = "C:\inetpub" )
{
    $break = "\r\n";

    $files = array();

	$dir = new RecursiveDirectoryIterator( $d );

	foreach( new RecursiveIteratorIterator( $dir ) as $file )
	{
		$pathinfo = pathinfo( $file );

		if ( isset( $pathinfo['extension'] ) && isset( $pathinfo['filename'] ) )
		{
			if ( "config" == $pathinfo['extension'] && "web" == $pathinfo['filename'] )
			{
				$files[] = $file;
			}
		}
	}

	foreach( $files as $file )
	{	
		$web_config = simplexml_load_file( $file );
        
        if ( !is_object( $web_config ) ) continue;
        
        if ( $destinations = $web_config->xpath("//httpRedirect/@destination") )
        {
        	echo $file . $break;

        	foreach( $destinations as $dest )
        	{
        		echo $dest . $break;
        	}

        	echo $break;
        }

	}
}
list_web_config_http_redirects();
