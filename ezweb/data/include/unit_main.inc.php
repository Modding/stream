<?php
/*
 * ezweb by P. Giebel <spam@stimpyrama.org>
 *
 * Created on 24.12.2007 
 */
 
	 function scanmp3s( $folder, $db, $collection, $scanexts, $level=1 ) {
	        if ( $folder[strlen( $folder )-1] != "/" ) $folder .= "/";
			if ( $level == "1" ) {
	        	$query = "SELECT directory FROM collections WHERE id='$collection'";
	        	$res = $db->query( $query );
	        	$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
	        	$folder = $folder . $row["directory"];
	        	if ( $folder[strlen( $folder )-1] != "/" ) $folder .= "/";
			}
	        
	        if ( $handle = @opendir( $folder ) ) {
	                while ( ( $dir = readdir( $handle ) ) !== false ) {
	                        if ( $dir != "." && $dir != ".." ) {
	                            if ( is_dir( $folder.$dir."/" ) ) {
	                                    $level++;
	                                    scanmp3s( $folder.$dir."/", $db, $collection, $scanexts, $level );
	                                } else {
	                                	$filename = $db->escapeSimple( $folder.$dir );
	                                	$query = "SELECT id FROM files WHERE filename='$filename' AND collection='". intval( $collection ) ."'";
	                                	$res = $db->query( $query );
	                                	if ( $res->numRows() == 0 ) {
											$ext = strtolower( substr( strrchr( $dir, "." ), 1 ) );
											
											if ( array_search( $ext, $scanexts ) !== false ) {
												$getID3 = new getID3;
												$id3 = $getID3->analyze( $folder.$dir );
												getid3_lib::CopyTagsToComments( $id3 );
												
												$filesize = filesize( $folder.$dir );
												$bitrate = intval( round( $id3["bitrate"]/1000 ) );
												$filemtime = filemtime( $folder.$dir );
												$length = intval( round( $id3["playtime_seconds"] ) );
												$id3_artist = $db->escapeSimple( strval( $id3["comments"]["artist"][0] ) );
												if ( !$id3_artist ) $id3_artist = "undefined";
												$id3_title = $db->escapeSimple( strval( $id3["comments"]["title"][0] ) );
												if ( !$id3_title ) $id3_title = "undefined";
												$id3_album = $db->escapeSimple( strval( $id3["comments"]["album"][0] ) );
												if ( !$id3_album ) $id3_album = "undefined";
												$id3_comment = $db->escapeSimple( strval( $id3["comments"]["comment"][0] ) );
												if ( !$id3_comment ) $id3_comment = "undefined";
												$lastplayed = "0";
												$edited = "0";
												$added = time();
												
												$query =	"INSERT INTO files ( filename, filesize, bitrate, filemtime, ".
															"length, id3_artist, id3_title, id3_album, id3_comment, ".
															"lastplayed, edited, added, collection ) VALUES ( '$filename', '$filesize', ".
															"'$bitrate', '$filemtime', '$length', '$id3_artist', '$id3_title', ".
															"'$id3_album', '$id3_comment', '$lastplayed', '$edited', '$added', '$collection' )";
	
												$res = $db->query( $query );
												if ( PEAR::isError( $res ) ) {
													echo $res->getMessage() ."\n";
													echo "query: ". $query ."\n";
													break;
												}
											}
	                                	}
	                                }
	                        }
	                }
	                closedir( $handle );
	        }
	}

	function unesc( $x ) {
		if ( get_magic_quotes_gpc() )
			return stripslashes( $x );
		return $x;
	}

	function get_vars() {
		global $_GET, $_POST, $_SESSION;
		foreach ( array_keys( $_GET ) as $key ) {
		        if ( isset( $_GET[$key] ) ) $_SESSION[$key] = unesc( strip_tags( $_GET[$key] ) );
		}
		foreach ( array_keys( $_POST ) as $key ) {
		        if ( isset( $_POST[$key] ) ) $_SESSION[$key] = unesc( strip_tags( $_POST[$key] ) );
		}
		return true;
	}
?>
