#!/usr/bin/php
<?php
 // configuration

        $webdir = "/var/www/ezweb/";

        $now = time();
        
 // external files
        if ( !@require_once( $webdir ."data/include/config.inc.php" ) ) die( "error loading config-file" );
        if ( !@require_once( $webdir ."data/include/unit_main.inc.php" ) ) die( "error loading main unit" );
        if ( !@require_once( "DB.php" ) ) die( "error loading PEAR:DB - please install using pear..." );

        if ( $cfg["mp3dir"][strlen( $cfg["mp3dir"] )-1] != "/" ) $cfg["mp3dir"] .= "/";

// sql connect
        $dsn = $cfg["sql"]["type"] ."://". $cfg["sql"]["user"] .":". $cfg["sql"]["pass"] ."@". $cfg["sql"]["host"] ."/". $cfg["sql"]["db"];
        $db =& DB::connect( $dsn, $cfg["sql"]["options"] );
        if ( PEAR::isError( $db ) ) {
                die( $db->getMessage() );
        }

// cli-check
        if ( $_SERVER["argc"] > 0 ) $cli = true;
        if ( !$cli ) die( "no web access - please run via ezstream" );

// get next track (first file from queue)
        $query = "SELECT filename, fid FROM queue WHERE pos='1'";
        $res = $db->query( $query );
        $row = $res->fetchrow( DB_FETCHMODE_ASSOC );
        $fid = $row["fid"];
        $filename = $row["filename"];
	if ( !$filename ) $filename = $cfg["fallback"]["filename"];
        echo $filename ."\n";

// delete first file from queue
        $query = "DELETE FROM queue WHERE pos='1'";
        $res = $db->query( $query );

// add track to history
    $filename = $db->escapeSimple( $filename );
	$now = time();
	$query =	"INSERT INTO history ( filename, fid, played ) values ".
			"( '$filename', '$fid', '$now' )";
        $res = $db->query( $query );

// update "last played" tag
	$query = "UPDATE files SET lastplayed='$now' WHERE id='$fid'";
	$res = $db->query( $query );
        
// reorder queue and get endtime
        $query = "SELECT id, fid FROM queue ORDER BY pos ASC";
        $res = $db->query( $query );
        $qlength = intval( $res->numRows() );
        $pos = 1;
        $qendtime = 0;
        while ( $row = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
                $qid = $row["id"];
                $fid = $row["fid"];
                $query = "UPDATE queue SET pos='$pos' WHERE id='$qid'";
                $res2 = $db->query( $query );

                $query = "SELECT length FROM files WHERE id='$fid'";
                $res2 = $db->query( $query );
                $row2 = $res2->fetchrow( DB_FETCHMODE_ASSOC );
                $qendtime += intval( $row2["length"] );
                $pos++;
        }

// fill queue
        if ( $qlength < intval( $cfg["autoqueue"] ) ) {
                $query = "SELECT fid FROM queue ORDER BY pos DESC LIMIT ". $cfg["dupestep"];
                $res = $db->query( $query );
                $queuesize = intval( $res->numRows() );
                $checkartists = array();
                while ( $row = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
                        $fid = intval( $row["fid"] );
                        $query = "SELECT id3_artist FROM files WHERE id='$fid'";
                        $res2 = $db->query( $query );
                        $row2 = $res2->fetchrow( DB_FETCHMODE_ASSOC );

                        if ( array_search( strtolower( $row2["id3_artist"] ), $checkartists ) === false )
                                array_push( $checkartists, $db->escapeSimple( strtolower( $row2["id3_artist"] ) ) );
                }
                if ( $queuesize < intval( $cfg["dupestep"] ) ) {
                        $historysize = intval( $cfg["dupestep"] )-$queuesize;
                        $query = "SELECT fid FROM history ORDER BY id DESC LIMIT $historysize";
                        $res = $db->query( $query );
                        while ( $row = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
                                $fid = intval( $row["fid"] );
                                $query = "SELECT id3_artist FROM files WHERE id='$fid'";
                                $res2 = $db->query( $query );
                                $row2 = $res2->fetchrow( DB_FETCHMODE_ASSOC );

                                if ( array_search( strtolower( $row2["id3_artist"] ), $checkartists ) === false )
                                        array_push( $checkartists, $db->escapeSimple( strtolower( $row2["id3_artist"] ) ) );
                        }
                }
                $missing = intval( $cfg["autoqueue"] )-$qlength;

                $qendtime += intval( date( "G", $now ) )*60*60;
                $qendtime += intval( date( "i", $now ) )*60;

                $dow = intval( date( "w" ) )+1;

                $query = "SELECT id, filename, filesize, weight FROM files WHERE";
                foreach ( $checkartists as $ca ) {
                        if ( strlen( $ca ) > 0 )
                                $query .= " id3_artist NOT LIKE '$ca' AND";
                }
                $sqendtimemax = $qendtime+600;
                $sqendtimemin = $qendtime-600;
                $query2 = "SELECT id, cid, days FROM automat WHERE starttime<'$sqendtimemax' AND starttime>'$sqendtimemin' AND endtime='999999' AND lastexec<>'$dow' ORDER BY starttime DESC";
                $res2 = $db->query( $query2 );
                if ( $res2->numRows() > 0 ) {
                        $row2 = $res2->fetchrow( DB_FETCHMODE_ASSOC );
                        $autodays = unserialize( $row2["days"] );
                        if ( $autodays[$dow] == 1 ) {
	                        $aid = intval( $row2["id"] );
	                        $cid = intval( $row2["cid"] );
                		$query3 = "SELECT id, filename, weight FROM files WHERE collection='$cid' AND weight>'0' ORDER BY RAND()*weight DESC LIMIT 1";

                            $res3 = $db->query( $query3 );

                            $row3 = $res3->fetchrow( DB_FETCHMODE_ASSOC );
                            $filename = $db->escapeSimple( $row3["filename"] );
                            $query3 =       "INSERT INTO queue ( filename, fid, pos ) values ".
                                                    "( '$filename', '". $row3["id"] ."', ".
                                                    "'$pos' )";
                            $res3 = $db->query( $query3 );
                            $query3 = "UPDATE automat SET lastexec='$dow' WHERE id='$aid'";
                            $res3 = $db->query( $query3 );
                            $pos++;
                            $missing--;
                            if ( $missing < 0 ) $missing = 0;
                        }

                }
		$query2 = "SELECT id, cid, days FROM automat WHERE starttime<'$qendtime' AND endtime>'$nqendtime' AND endtime<'999999' ORDER BY starttime DESC";
                $res2 = $db->query( $query2 );
                if ( $res2->numRows() > 0 ) {
                        $row2 = $res2->fetchrow( DB_FETCHMODE_ASSOC );
                        $autodays = unserialize( $row2["days"] );
                        $cid = intval( $row2["cid"] );
                        if ( $autodays[$dow] == 1 ) $query .= " collection='$cid' AND";
                }

                $query .= " weight>'0' ORDER BY ";
                
                $playlow = rand( 0, 100 );
                if ( $playlow < $cfg["lowweightchance"] ) $query .= "lastplayed ASC LIMIT $missing";
                	else $query .= "RAND()*weight DESC LIMIT $missing";
                
                $res = $db->query( $query );
                if ( intval( $res->numRows() ) < $missing ) {
                        $query = "SELECT id, filename, filesize, weight FROM files WHERE collection='$cid' AND weight>'0' ORDER BY RAND()*weight DESC LIMIT $missing";
                        $res = $db->query( $query );
                        if ( intval( $res->numRows() ) < $missing ) {
                                $query = "SELECT id, filename, filesize, weight FROM files WHERE weight>'0' ORDER BY RAND()*weight DESC LIMIT $missing";
                                $res = $db->query( $query );
                        }
                }
                if ( PEAR::isError( $res ) ) {
                     echo $res->getMessage() ."\n";
                     die ( "query: ". $query ."\n" );
                }
                while ( $row = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
                        $filename = $db->escapeSimple( $row["filename"] );
			$fid = intval( $row["id"] );
			if ( ( !$fid ) OR ( $fid == 0 ) ) {
				$fid = $cfg["fallback"]["fid"];
				$filename = $cfg["fallback"]["filename"];
			}
                        $query =        "INSERT INTO queue ( filename, fid, pos ) values ".
                                                "( '$filename', '". $fid ."', ".
                                                "'$pos' )";
                        $res2 = $db->query( $query );
                        $pos++;
                }
        }

?>
