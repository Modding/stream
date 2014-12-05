<?php
/*
 * ezweb by P. Giebel <spam@stimpyrama.org>
 *
 * Created on 24.12.2007 
 */
 
	$query = "SELECT pos FROM queue ORDER BY pos DESC";
	$res = $db->query( $query );
	$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
	$maxpos = intval( $row["pos"] );
	$now = time(); 
	
	if ( $_SESSION["addrandom"] ) {
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
		$missing = intval( $_SESSION["addrandom"] );
		unset( $_SESSION["addrandom"] );
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

	if ( $_SESSION["delqueue"] ) {
 		$qid = intval( $_SESSION["delqueue"] );
 		$query = "DELETE FROM queue WHERE id='$qid'";
 		$res = $db->query( $query );
 		
 		$query = "SELECT id, pos FROM queue ORDER BY pos ASC";
 		$res = $db->query( $query );
 		$pos = 1;
 		while ( $row = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
 			$qid = $row["id"];
 			$query = "UPDATE queue SET pos='$pos' WHERE id='$qid'";
 			$res2 = $db->query( $query );
 			$pos++;
 		}
 		unset( $_SESSION["delqueue"] );
 	}
 	
 	if ( $_SESSION["move"] ) {
 		list( $direction, $qid ) = split( "-", $_SESSION["move"] );
 		
 		$query = "SELECT pos FROM queue WHERE id='$qid'";
 		$res = $db->query( $query );
 		$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
 		$pos = intval( $row["pos"] );
 		
 		$opos = $pos;

		switch( $direction ) {
			case "up":
				$pos--;
			break;
			case "down":
				$pos++;
			break;
			case "first":
				$pos = 1;
				$bpos = 2;
			break;
			case "last":
				$pos = $maxpos;
				$bpos = 1;
			break;
		}
		if ( ( $direction == "up" ) OR ( $direction == "down" ) ) {
	 		if ( ( $pos > 0 ) AND ( $pos <= $maxpos ) ) {
		 		$query = "UPDATE queue SET pos='$opos' WHERE pos='$pos'";
		 		$res = $db->query( $query );
		  		$query = "UPDATE queue SET pos='$pos' WHERE id='$qid'";
		 		$res = $db->query( $query );
	 		}
		} elseif ( ( $direction == "first" ) OR ( $direction == "last" ) ) {
			$query = "UPDATE queue SET pos='$pos' WHERE id='$qid'";
			$res = $db->query( $query );
			$query = "SELECT id FROM queue WHERE id<>'$qid' ORDER BY pos ASC";
			$res2 = $db->query( $query );
			while ( $row2 = $res2->fetchrow( DB_FETCHMODE_ASSOC ) ) {
				$rqid = intval( $row2["id"] );
				$query = "UPDATE queue SET pos='$bpos' WHERE id='$rqid'";
				$res3 = $db->query( $query );
				$bpos++;
			}
		}

 		unset( $_SESSION["move"] );
 	}
 
 	$query = "SELECT fid, played FROM history ORDER BY id DESC LIMIT 1";
 	$res = $db->query( $query );
 	$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
 	
 	$starts = $row["played"];
 	$fid = intval( $row["fid"] );
 	
 	$query = "SELECT length FROM files WHERE id='$fid' LIMIT 1";
 	$res = $db->query( $query );
 	$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
 	
 	$length = intval( $row["length"] );
 	
 	$starts += $length;
 	
 	$query = "SELECT * FROM queue ORDER BY pos ASC LIMIT 500";
 	$res = $db->query( $query );
 ?>
 
 	<table width="95%" border="0" cellspacing="5">
  		<tr>
			<td align="right" colspan="4">
				<a class="btn" href="?addrandom=1">add random track</a>
			</td>
		</tr>
		<tr class="defaulttable_head">
 			<td align="center" colspan="4">QUEUE</td>
 		</tr>
 		<tr class="defaulttable_head">
 			<td>&nbsp;Track</td>
 			<td width="10%" align="right">Length&nbsp;</td>
 			<td width="10%" align="right">Starts&nbsp;</td>
 			<td width="15%">&nbsp;</td>
 		</tr>
 <?php
 	if ( $res->numRows() > 0 ) {
	 	while ( $queue = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
	 		if ( $c == "a" ) $c = "b";
	 			else $c = "a";
	 		$fid = intval( $queue["fid"] );
	 		$pos = intval( $queue["pos"] );
	 		$qid = intval( $queue["id"] );
	 		$query = "SELECT * FROM files WHERE id='$fid'";
	 		$res2 = $db->query( $query );
	 		$track = $res2->fetchrow( DB_FETCHMODE_ASSOC );
			if ( $track["lastplayed"] < 1 ) $lastplayed = "never";
				else $lastplayed = date( "Y-m-d H:i:s", $track["lastplayed"] );
			$trackname = htmlentities( $track["id3_artist"] ." - ". $track["id3_title"] );
			$length = intval( $track["length"] );
			if ( $length > 3600 ) $outtime = gmdate( "H:i:s", $length );
				else $outtime = gmdate( "i:s", $length );
 ?>
	 		<tr class="defaulttable_text-<?= $c; ?>">
	 			<td>
	 				<a class="help" title="<?= htmlentities( $track["id3_artist"] ." - ". $track["id3_title"] ." (". $track["id3_album"] .") - last played: ". $lastplayed ); ?>"><?= $trackname; ?></a>
	 			</td>
	 			<td align="right"><?= $outtime; ?></td>
	 			<td align="right"><?= date( "H:i:s", $starts ); ?></td>
	 			<td align="center">
	 				<a class="smallbtn" title="delete from queue" href="?delqueue=<?= $qid; ?>">-</a>
<?php
					if ( $pos > 1 ) {
?>
						<a class="smallbtn" title="move to first position" href="?move=first-<?= $qid; ?>">&uArr;</a>
		 				<a class="smallbtn" title="move up" href="?move=up-<?= $qid; ?>">&uarr;</a>
<?php
					} else {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					}
					if ( $pos < $maxpos ) {

?>
						<a class="smallbtn" title="move down" href="?move=down-<?= $qid; ?>">&darr;</a>
		 				<a class="smallbtn" title="move to last position" href="?move=last-<?= $qid; ?>">&dArr;</a>
<?php
					} else {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					}
?>
				</td>
	 		</tr>
 <?php
 			$starts += $length;
 		}
 	} else {
?>
		<tr>
			<td class="defaulttable_text-a" colspan="2">No files in queue</td>
		</tr>
<?php
 	}
?>
	</table>
