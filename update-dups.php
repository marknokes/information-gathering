<?php

/*
* Parse the web.config file to replace all reduntant regex matches. Example: replace ^something$|^something/$ with ^something/?$
* The best choice for running this script is to copy/paste the web.config into the same directory. It outputs a file named results.xml.
* Just rename results.xml to web.config and BAM! You're done, although, I don't think anyone will ever use this again since it has kind
* of a limited use.
*/

error_reporting(E_ALL);

// Prevent browser access
if ( !isset( $argv ) ) exit; 

function update_web_config_dups()
{
    $break = "\r\n";

	$xml = simplexml_load_file( "web.config" );

	foreach( $xml->xpath("//rule/match/@url") as $match )
	{	
		$pattern = str_replace( array('^', '$'), "", $match );

		if ( false !== strpos( $pattern, '|' ) ) {
			$pieces = explode( "|", $pattern );
			if (
				sizeof( $pieces ) == 2 &&
				( $pieces[0] == $pieces[1] . '/' || $pieces[0] . '/' == $pieces[1] )
			) {
				$new_pattern = '^' . trim( $pieces[0], '/' ) . '/?$';
				$match->url = $new_pattern;
			}
		}
	}
	$xml->asXML('results.xml');
}
update_web_config_dups();