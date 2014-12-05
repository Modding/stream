<?php
/*
 * ezweb by P. Giebel <spam@stimpyrama.org>
 *
 * Created on 24.12.2007 
 */
 
 	$webdir = "/var/www/ezweb/";

 

 	if ( !@require_once( $webdir ."data/include/config.inc.php" ) ) die( "error loading config-file\n" );
 	if ( !@require_once( $webdir ."data/include/unit_main.inc.php" ) ) die( "error loading main unit\n" );
 	if ( !@require_once( $webdir ."data/include/getid3/getid3.php" ) ) die( "error loading getid3 library\n" );
	if ( !@require_once( "DB.php" ) ) die( "error loading PEAR:DB - please install using pear...\n" );
	
	if ( $cfg["mp3dir"][strlen( $cfg["mp3dir"] )-1] != "/" ) $cfg["mp3dir"] .= "/";
	
	$dsn = $cfg["sql"]["type"] ."://". $cfg["sql"]["user"] .":". $cfg["sql"]["pass"] ."@". $cfg["sql"]["host"] ."/". $cfg["sql"]["db"];
	$db =& DB::connect( $dsn, $cfg["sql"]["options"] );
	if ( PEAR::isError( $db ) ) {
		die( $db->getMessage() ."\n" );
	}

	if ( $_SERVER["argc"] > 0 ) $cli = true;
	
	if ( $cli ) {
		$collection = $_SERVER["argv"][1];
	} else {
		$collection = strip_tags( $_GET["arg1"] );
	}
	if ( !$collection ) {
		$query = "SELECT id FROM collections ORDER BY id";
		$res = $db->query( $query );
		while ( $row = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
			$collection = $row["id"];
			scanmp3s( $cfg["mp3dir"], $db, $collection, $cfg["scanexts"] );
		}
	} else {
		if ( !is_int( $collection ) ) {
			$query = "SELECT id FROM collections WHERE name LIKE '$collection'";
			$res = $db->query( $query );
			$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
			$collection = $row["id"];
		}
			
		scanmp3s( $cfg["mp3dir"], $db, $collection, $cfg["scanexts"] );
		
	}

// cleanup old (missing) files

	$query = "SELECT * FROM files";
	$res = $db->query( $query );
	
	while ( $row = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
		if ( !file_exists( $row["filename"] ) ) {
			$fid = intval( $row["id"] );
			$query = "DELETE FROM files WHERE id='$id'";
			$res2 = $db->query( $query );
		}
	}
?>
