<?php

error_reporting(E_ALL);

function add_slash()
{
    $break = "\r\n";

    $xml = simplexml_load_file( "web.config" );
	
	foreach( $xml->xpath("//action/@url") as $match )
	{
		$url = parse_url( $match );

		if( !isset( $url['path'] ) )
			$path = '/';
		elseif( false === strpos( $url['path'], "." ) && substr( $url['path'], -1 ) !== '/' )
			$path = $url['path'] . '/';
		else
			$path = $url['path'];

		$query = isset( $url['query'] ) ? '?' . $url['query'] : '';

		$port = isset( $url['port'] ) ? ':' . $url['port'] : '';
			
		$surl = $url['scheme'] . "://" . $url['host']. $port . $path . $query;
		$match->url = $surl;
	}

	$xml->asXML('results.xml');
}
add_slash();