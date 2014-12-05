<?php
/*
 * ezweb by P. Giebel <spam@stimpyrama.org>
 *
 * Created on 25.12.2007 
 */
 
 	$query = "SELECT fid, played FROM history ORDER BY id DESC LIMIT 1";
 	$res = $db->query( $query );
 	$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
 	$fid = intval( $row["fid"] );
 	$started = $row["played"];
 	
 	$query = "SELECT * FROM files WHERE id='$fid'";
  	$res = $db->query( $query );
 	$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
 	
	$filename = strrchr( $row["filename"], "/" );
	$filename = substr( $filename, 1 );
	
	$artist = $row["id3_artist"];
	$title = $row["id3_title"];
	$album = $row["id3_album"];
	
	$end = $started+intval( $row["length"] );
	$remain = $end-intval( time() );
	
	if ( intval( $remain ) > 3600 ) $outremain = gmdate( "H:i:s", intval( $remain ) );
		else $outremain = gmdate( "i:s", intval( $remain ) );
	
	if ( intval( $row["length"] ) > 3600 ) $outtime = gmdate( "H:i:s", intval( $row["length"] ) );
		else $outtime = gmdate( "i:s", intval( $row["length"] ) );
		
	$collection = intval( $row["collection"] );
	
	$query = "SELECT name FROM collections WHERE id='$collection'";
	$res = $db->query( $query );
	$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
	$collection = $row["name"];
	
?>
	
		<table width="99%" border="0" cellspacing="5" align="center">
			<tr class="defaulttable_head" align="center">
				<td colspan="7">NOW PLAYING</td>
			</tr>
			<tr class="defaulttable_text-b" style="height: 30px">
				<td width="20%"><a title="Artist: <?= htmlentities( $artist ); ?>" class="help" style="height: 30px; vertical-align: middle; overflow: hidden; display: block"><?= htmlentities( $artist ); ?></a></td>
				<td width="20%"><a title="Title: <?= htmlentities( $title ); ?>" class="help" style="height: 30px; vertical-align: middle; overflow: hidden; display: block"><?= htmlentities( $title ); ?></a></td>
				<td width="20%"><a title="Album: <?= htmlentities( $album ); ?>" class="help" style="height: 30px; vertical-align: middle; overflow: hidden; display: block"><?= htmlentities( $album ); ?></a></td>
				<td width="20%"><a title="Filename: <?= htmlentities( $filename ); ?>" class="help" style="height: 30px; vertical-align: middle; overflow: hidden; display: block"><?= htmlentities( $filename ); ?></a></td>
				<td width="6%"><a title="Collection: <?= htmlentities( $collection ); ?>" class="help" style="height: 30px; vertical-align: middle; overflow: hidden; display: block"><?= htmlentities( $collection ); ?></a></td>
				<td width="8%" align="right"><a title="Remaining time: -<?= htmlentities( $outremain ); ?>" class="help" style="height: 30px; vertical-align: middle; overflow: hidden; display: block">-<?= htmlentities( $outremain ); ?></a></td>
				<td width="6%" align="right"><a title="Started: <?= htmlentities( $outtime ); ?>" class="help" style="height: 30px; vertical-align: middle; overflow: hidden; display: block"><?= htmlentities( $outtime ); ?></a></td>
			</tr>
		</table>
	