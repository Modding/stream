<?php
/*
 * ezweb by P. Giebel <spam@stimpyrama.org>
 *
 * Created on 24.12.2007 
 */
 
 
 	if ( $_SESSION["addqueue"] ) {
 		$fid = intval( $_SESSION["addqueue"] );
 		$query = "SELECT filename FROM files WHERE id='$fid'";
 		$res = $db->query( $query );
 		$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
 		$filename = $db->escapeSimple( $row["filename"] );
 		$query = "SELECT pos FROM queue ORDER BY pos DESC";
  		$res = $db->query( $query );
  		if ( $res->numRows() > 0 ) {
 			$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
 			$pos = intval( $row["pos"] );
 			$pos++;
  		} else {
 			$pos = 1;
  		}
 		
 		$query = "INSERT INTO queue ( filename, fid, pos ) VALUES ( '$filename', '$fid', '$pos' )";
 		$res = $db->query( $query );
 		
 		unset( $_SESSION["addqueue"] );
 	}
 	
 	if ( $_SESSION["weight"] ) {
 		list( $direction, $fid ) = split( "-", $_SESSION["weight"] );
 		$query = "SELECT weight FROM files WHERE id='$fid'";
 		$res = $db->query( $query );
 		$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
 		$weight = intval( $row["weight"] );
 		if ( $direction == "up" ) $weight ++;
 			else $weight--;
 			
 		if ( $weight > 10 ) {
 			$weight = 10;
 		} elseif ( $weight < 0 ) {
 			$weight = 0;
 		} else {
 			$query = "UPDATE files SET weight='$weight' WHERE id='$fid'";
 			$res = $db->query( $query );
 		}
 		
 		unset( $_SESSION["weight"] );
 	}
 	
 	if ( $_SESSION["deldb"] ) {
 		$fid = intval( $_SESSION["deldb"] );
 		$query = "SELECT filename FROM files WHERE id='$fid'";
 		$res = $db->query( $query );
 		$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
 		$filename = $row["filename"];
 		$query = "DELETE FROM files WHERE id='$fid'";
		$res = $db->query( $query );
 		unset( $_SESSION["deldb"] );
 		if ( is_writable( $filename ) ) {
?>
			<div id="fw_infobox">
				Also delete file:<br /><strong><?= htmlentities( $filename ); ?></strong>?<br /><br />
				<a class="btn" href="?delfile=<?= $fid; ?>">YES</a> - <a class="btn" href="?deldbonly=<?= $fid; ?>">NO</a>
			</div>
<?php
 		}
 	}
 	
 	if ( $_SESSION["addcollection"] ) {
?>
		<div id="fw_infobox" align="center" style="color: #000000">
			<h3>Add new collection</h3>
			<form action="#" enctype="multipart/form-data" method="post">
				<table width="80%" align="center" border="0">
					<tr>
						<td width="20%" align="right">Name:</td>
						<td width="80%" align="left"><input title="Name of the new collection" type="text" name="newcollname" style="width: 80%" /></td>
					</tr>
					<tr>
						<td align="right">Path:</td>
						<td align="left"><input title="path to the new collection (relative to <?= $cfg["mp3dir"]; ?>)" type="text" name="newcollpath" style="width: 80%" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left"><span style="font-size: 11px">relative to <strong>&quot;<?= $cfg["mp3dir"]; ?>&quot;</strong></span></td>
					</tr>
					<tr>
						<td colspan="2" align="right">
							<a class="btn" title="do not create a new collection (abort)" href="index.php" style="padding: 1px">abort</a>&nbsp;&nbsp;&nbsp;
							<input title="save this new collection" type="submit" name="newcollsubmit" class="btn" />
						</td>
					</tr>
				</table>
			</form>
		</div>
<?php
		unset( $_SESSION["addcollection"] );
 	}
 	
	if ( $_SESSION["newcollsubmit"] ) {
		$newcollname = strval( $_SESSION["newcollname"] );
		$newcollpath = strval( $_SESSION["newcollpath"] );
		$query = "INSERT INTO collections ( name, directory ) VALUES ";
		$query .= "( '$newcollname', '$newcollpath' )";
		$res = $db->query( $query );
		unset( $_SESSION["newcollname"] );
		unset(  $_SESSION["newcollpath"] );
		unset(  $_SESSION["newcollsubmit"] );
	}
 	
 	if ( $_SESSION["delfile"] ) {
 		$fid = intval( $_SESSION["delfile"] );
  		$query = "SELECT filename FROM files WHERE id='$fid'";
 		$res = $db->query( $query );
 		$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
 		$filename = $row["filename"];
 		unset( $_SESSION["delfile"] );
 		$query = "DELETE FROM files WHERE id='$fid'";
 		$res = $db->query( $query );
 		$query = "DELETE FROM queue WHERE fid='$fid'";
 		$res = $db->query( $query );
 		if ( is_writable( $filename ) ) unlink( $filename );
 	}
 
  	if ( $_SESSION["deldbonly"] ) {
 		$fid = intval( $_SESSION["deldbonly"] );
 		$query = "DELETE FROM files WHERE id='$fid'";
 		$res = $db->query( $query );
 		$query = "DELETE FROM queue WHERE fid='$fid'";
 		$res = $db->query( $query );
 		unset( $_SESSION["deldbonly"] );
 	}
 
	$collection = intval( $_SESSION["collection"] );
	if ( !$collection ) $collection = 0;
	if ( $_SESSION["dbsearch"] ) {
		$dbsearch = str_replace( " ", "%", $_SESSION["dbsearch"] );
	}
	$query = "SELECT id, name FROM collections ORDER BY name";
	$res = $db->query( $query );
	$oldsearch = str_replace( "%", " ", $dbsearch );
?>
	<form action="#" enctype="multipart/form-data" method="post">
		Search: <input type="text" name="dbsearch" style="width: 35%" value="<?= $oldsearch; ?>" />&nbsp;&nbsp;&nbsp;
		<select name="collection" size="1" style="width:35%">
<?php
		if ( $collection == 0 ) $add = " selected=\"selected\"";
			else $add = "";
?>
		<option value="0"<?= $add; ?>>all</option>
<?php
		while ( $row = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
			if ( $row["id"] == $collection ) $add = " selected=\"selected\"";
				else $add = "";
?>
			<option value="<?= $row["id"]; ?>"<?= $add; ?>><?= $row["name"]; ?></option>
<?php
		}	
?>
		</select>
		&nbsp;&nbsp;&nbsp;
		<input type="submit" name="ccsubmit" value="ok" class="btn" />&nbsp;&nbsp;&nbsp;<a href="?addcollection=1" title="add new collection" class="btn" style="padding: 1px">add</a>
	</form>
	
	<hr style="margin-bottom: 1px; margin-top: 1px" />
<?php
	$query = "SELECT id, filename, length, id3_artist, id3_title, id3_album, lastplayed, weight FROM files WHERE id>'0'";
	if ( $dbsearch ) $query .= " AND ( filename LIKE '%$dbsearch%' OR id3_artist LIKE '%$dbsearch%' OR id3_title LIKE '%$dbsearch%' )";
	if ( $collection > 0 ) $query .= " AND collection='$collection'";
	$dbpage = intval( $_SESSION["dbpage"] );
	if ( !$dbpage ) $dbpage = 1;
	$dbstart = $dbpage*12;
	$res = $db->query( $query );
	$maxfiles = intval( $res->numRows() );
	$maxpages = floor( $maxfiles/12 );
	if ( $dbsearch ) {
		// $dbstart = 1;
		$dbpage = "all";
		$_SESSION["dbpage"] = "all";
		unset( $_SESSION["ccsubmit"] );
	}
	if ( $_SESSION["dbpage"] != "all" ) $query .= " LIMIT $dbstart, 12";
	$res = $db->query( $query );
	$dbnextpage = $dbpage+1;
	if ( $dbnextpage > $maxpages ) $dbnextpage = false;
	$dbprevpage = $dbpage-1;
	if ( $dbprevpage < 1 ) $dbprevpage = false;
?>
	<div id="collection_tracks">
		<table width="95%" border="0" cellspacing="5">
			<tr>
				<td width="10%" align="left">
<?php
					if ( ( $dbprevpage ) AND ( $_SESSION["dbpage"] != "all" ) ) {
?>
						<a href="?dbpage=1" title="first page" class="smallbtn">&lt;&lt;</a>&nbsp;&nbsp;
						<a href="?dbpage=<?= $dbprevpage; ?>" title="previous page" class="smallbtn">&lt;</a>
<?php
					}
?>
				</td>
				<td colspan="3" align="center">
<?php
					if ( $_SESSION["dbpage"] == "all" ) {
?>
						<a href="?dbpage=1" title="show pages" class="smallbtn">show pages</a>
<?php						
					} else {
?>
						<a href="?dbpage=all" title="show all" class="smallbtn">show all</a>
<?php
					}
?>
				</td>
				<td align="right">
<?php
					if ( ( $dbnextpage ) AND ( $_SESSION["dbpage"] != "all" ) ) {
?>
						<a href="?dbpage=<?= $dbnextpage; ?>" title="next page" class="smallbtn">&gt;</a>&nbsp;&nbsp;
						<a href="?dbpage=<?= $maxpages; ?>" title="last page" class="smallbtn">&gt;&gt;</a>
<?php
					}
?>
				</td>
			</tr>
			<tr class="defaulttable_head">
				<td colspan="5" align="center">ARCHIVE</td>
			</tr>
			<tr class="defaulttable_head">
				<td colspan="2" width="70%">&nbsp;Filename</td>
				<td width="10%" align="center">Weight</td>
				<td width="10%" align="right">Length&nbsp;</td>
				<td width="10%">&nbsp;</td>
			</tr>
<?php
			while ( $row = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
				if ( $c == "a" ) $c = "b";
					else $c = "a";
				if ( $row["lastplayed"] < 1 ) $lastplayed = "never";
					else $lastplayed = date( "Y-m-d H:i:s", $row["lastplayed"] );
				$trackname = htmlentities( $row["id3_artist"] ." - ". $row["id3_title"] );
				$length = intval( $row["length"] );
				if ( $length > 3600 ) $outtime = gmdate( "H:i:s", $length );
					else $outtime = gmdate( "i:s", $length );
?>
				<tr class="defaulttable_text-<?= $c; ?>">
					<td colspan="2" style="overflow: hidden">
						<a style="height: 16px; vertical-align: middle; overflow: hidden; display: block" class="help" title="<?= htmlentities( $row["id3_artist"] ." - ". $row["id3_title"] ." (". $row["id3_album"] .") - last played: ". $lastplayed ); ?>"><?= $trackname; ?></a>
					</td>
					<td align="center"><?= $row["weight"]; ?></td>
					<td align="right"><?= $outtime; ?></td>
					<td align="center">
						<a class="smallbtn" title="add to queue" href="?addqueue=<?= $row["id"]; ?>">+</a>
		 				<a class="smallbtn" title="rate up" href="?weight=up-<?= $row["id"]; ?>">&uarr;</a>
		 				<a class="smallbtn" title="rate down" href="?weight=down-<?= $row["id"]; ?>">&darr;</a>
		 				<a class="smallbtn" title="delete from db" href="?deldb=<?= $row["id"]; ?>">&otimes;</a>
					</td>
				</tr>
<?php
			}
?>
		</table>
	</div>
