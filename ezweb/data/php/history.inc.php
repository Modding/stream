<?php
/*
 * ezweb by P. Giebel <spam@stimpyrama.org>
 *
 * Created on 25.12.2007 
 */
 
  	$query = "SELECT * FROM history ORDER BY id DESC LIMIT 50";
 	$res = $db->query( $query );
 ?>
 
 	<table width="95%" border="0" cellspacing="5">
 		<tr class="defaulttable_head">
 			<td align="center" colspan="4">HISTORY</td>
 		</tr>
 		<tr class="defaulttable_head">
 			<td>&nbsp;Track</td>
 			<td width="10%" align="right">Length&nbsp;</td>
 			<td width="10%" align="right">Started&nbsp;</td>
 		</tr>
 <?php
 	if ( $res->numRows() > 0 ) {
	 	while ( $queue = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
	 		if ( $c == "a" ) $c = "b";
	 			else $c = "a";
	 		$fid = intval( $queue["fid"] );
	 		$qid = intval( $queue["id"] );
	 		$played = $queue["played"];
	 		$query = "SELECT * FROM files WHERE id='$fid'";
	 		$res2 = $db->query( $query );
	 		$track = $res2->fetchrow( DB_FETCHMODE_ASSOC );
			$trackname = htmlentities( $track["id3_artist"] ." - ". $track["id3_title"] );
			$length = intval( $track["length"] );
			if ( $length > 3600 ) $outtime = gmdate( "H:i:s", $length );
				else $outtime = gmdate( "i:s", $length );
 ?>
	 		<tr class="defaulttable_text-<?= $c; ?>">
	 			<td>
	 				<a class="help" title="<?= htmlentities( $track["id3_artist"] ." - ". $track["id3_title"] ." (". $track["id3_album"] .")" ); ?>"><?= $trackname; ?></a>
	 			</td>
	 			<td align="right"><?= $outtime; ?></td>
	 			<td align="right"><?= date( "H:i:s", $played ); ?></td>
	 		</tr>
 <?php
 		}
 	} else {
?>
		<tr>
			<td class="defaulttable_text-a" colspan="3">No files in history</td>
		</tr>
<?php
 	}
?>
	</table>
