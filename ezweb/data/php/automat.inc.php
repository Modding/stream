<?php
/*
 * ezweb by P. Giebel <spam@stimpyrama.org>
 *
 * Created on 25.12.2007 
 */
 
 	$daysarr = array( 1 => "SUN", 2 => "MON", 3 => "TUE", 4 => "WED", 5 => "THU", 6 => "FRI", 7 => "SAT" );
 	
 	if ( $_SESSION["autoedit"] ) {
 		$aid = intval( $_SESSION["autoedit"] );
 		$query = "SELECT * FROM automat WHERE id='$aid'";
 		$res = $db->query( $query );
 		$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
 		$start = intval( $row["starttime"] );
 		$stop = intval( $row["endtime"] );
 		$starth = intval( floor( $start/60/60 ) );
 		if ( $starth*60*60 < $start )
 			$startm = $start-$starth*60*60;
 		else
 			$startm = "00";
 		if ( $stop < 999999 ) {
	 		$stoph = intval( floor( $stop/60/60 ) );
	 		if ( $stoph*60*60 < $stop )
	 			$stopm = $stop-$stoph*60*60;
	 		else
	 			$stopm = "00";
 		} else {
 			$stoph = "HH";
 			$stopm = "MM";
 		}
 		
 		$days = unserialize( $row["days"] );
 		$cid = intval( $row["cid"] );
 		$edit = true;
 		unset( $_SESSION["autoedit"] );
 	} else {
 		$starth = "HH";
 		$startm = "MM";
 		$stoph = "HH";
 		$stopm = "MM";
 		$days = array( 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1 );
 		$cid = 0;
 		$edit = false;
 	}
 	
 	if ( $_SESSION["automat"] == "save" ) {
 		if ( $_SESSION["starth"] != "HH" ) {
 			$start = intval( $_SESSION["starth"] )*60*60;
 			$start += intval( $_SESSION["startm"] )*60;
 			if ( $_SESSION["stoph"] != "HH" ) {
 				$stop = intval( $_SESSION["stoph"] )*60*60;
 				$stop += intval( $_SESSION["stopm"] )*60;
 				if ( $stop < $start ) $stop += 86400;
 			} else {
 				$stop = 999999;
 			}
 			$days = array();
 			for ( $i = 1; $i <= 7; $i++ ) {
 				if ( intval( $_SESSION["day".$i] ) == 1 ) $days[$i] = "1";
 					else $days[$i] = "0";
 				unset( $_SESSION["day".$i] );
 			}
 			$days = serialize( $days );
 			$autocollection = intval( $_SESSION["autocollection"] );

 			$aid = intval( $_SESSION["aid"] );	
 			
 			unset( $_SESSION["starth"] );
 			unset( $_SESSION["startm"] );
  			unset( $_SESSION["stoph"] );
 			unset( $_SESSION["stopm"] );
 			unset( $_SESSION["autocollection"] );
 			unset( $_SESSION["automat"] );
 			unset( $_SESSION["aid"] );
 			
 			if ( $aid > 0 )
 				$query = "UPDATE automat SET starttime='$start', endtime='$stop', days='$days', cid='$autocollection' WHERE id='$aid'";
 			else
 				$query =	"INSERT INTO automat ( starttime, endtime, days, cid ) VALUES ( '$start', '$stop', '$days', '$autocollection' )";
 			$res = $db->query( $query );
	 		$starth = "HH";
	 		$startm = "MM";
	 		$stoph = "HH";
	 		$stopm = "MM";
	 		$days = array( 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1 );
	 		$cid = 0;
	 		$edit = false;
 		}
 	}
 	
 	if ( $_SESSION["autodel"] ) {
 		$aid = intval( $_SESSION["autodel"] );
 		$query = "DELETE FROM automat WHERE id='$aid'";
 		$res = $db->query( $query );
 	}
?>

	<div style="float: left; width: 25px; height: 100%; text-align: center" class="defaulttable_head">A<br />U<br />T<br />O<br />M<br />A<br />T</div>
	<div style="margin-left: 30px; height: 100%; padding: 0; overflow: scroll">
		<form action="#" enctype="multipart/form-data" method="post">
			<table width="99%" border="0" cellspacing="5" align="left" style="margin-bottom: 20px">
				<tr class="defaulttable_head">
					<td width="10%">&nbsp;Start</td>
					<td width="10%">&nbsp;Stop</td>
					<td width="35%">&nbsp;Days</td>
					<td width="35%">&nbsp;Collection</td>
					<td width="10%">&nbsp;</td>
				</tr>
				<tr class="defaulttable_text-a">
					<td>
						<input title="Start Time (hour)" type="text" name="starth" value="<?= $starth; ?>" size="3" maxlength="2" />&nbsp;:&nbsp;<input title="Start Time (minutes)" type="text" name="startm" value="<?= $startm; ?>" size="3" maxlength="2" />
					</td>
					<td>
						<input title="End Time (hour) - leave empty for a single event" type="text" name="stoph" value="<?= $stoph; ?>" size="3" maxlength="2" />&nbsp;:&nbsp;<input title="End Time (minutes) - leave empty for a single event" type="text" name="stopm" value="<?= $stopm; ?>" size="3" maxlength="2" />
					</td>
					<td>
<?php
						for ( $i = 1; $i <= 7; $i++ ) {
							if ( $days[$i] == 1 ) $add = " checked=\"checked\"";
								else $add = ""
?>
							<input title="activate event on <?= htmlentities( $daysarr[$i] ); ?>" type="checkbox" id="day<?= $i; ?>" name="day<?= $i; ?>" value="1"<?= $add; ?> />
							<label for="day<?= $i; ?>" title="activate event on <?= htmlentities( $daysarr[$i] ); ?>"><?= htmlentities( $daysarr[$i] ); ?></label>
<?php
						}
?>
					</td>
					<td>
<?php
						$query = "SELECT id, name FROM collections ORDER BY name";
						$res = $db->query( $query );
?>
						<select title="Which collection to play" name="autocollection" size="1" style="width:90%">
							<option value="0" selected="selected">all</option>
<?php
							while ( $row = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
								if ( $cid == $row["id"] ) $add = " selected=\"selected\"";
									else $add = "";
?>
								<option value="<?= $row["id"]; ?>"<?= $add; ?>><?= $row["name"]; ?></option>
<?php
							}	
?>
						</select>
					</td>
					<td align="center">
<?php
						if ( $edit ) {
?>
							<input type="hidden" name="aid" value="<?= $aid; ?>" />
<?php
						}
?>
						<input title="save event" class="btn" type="submit" name="automat" id="automat" value="save" />
					</td>
				</tr>
<?php
				$query = "SELECT * FROM automat ORDER BY starttime ASC";
				$res = $db->query( $query );
		 if ( PEAR::isError( $res ) ) {
		     die( $res->getMessage() );
		 }
				while ( $row = $res->fetchrow( DB_FETCHMODE_ASSOC ) ) {
					if ( $c == "a" ) $c = "b";
						else $c = "a";
					
					$starttime = intval( $row["starttime"] );
					$outstart = gmdate( "H:i:s", $starttime );
					$endtime = intval( $row["endtime"] );
					if ( $endtime != 999999 ) $outstop = gmdate( "H:i:s", $endtime );
						else $outstop  = false;
					$days = unserialize( $row["days"] );
					$aid = intval( $row["id"] );
					$cid = intval( $row["cid"] );
					$query = "SELECT name FROM collections WHERE id='$cid'";
					$res2 = $db->query( $query );
					
		 if ( PEAR::isError( $res2 ) ) {
		     die( $res2->getMessage() );
		 }
					$row2 = $res2->fetchrow( DB_FETCHMODE_ASSOC );
					$cname = $row2["name"];
					
?>
					<tr class="defaulttable_text-<?= $c; ?>">
						<td><?= $outstart; ?></td>
						<td>
<?php
						if ( $outstop ) echo $outstop;
							else echo "single";
?>
						</td>
						<td>
<?php
							for ( $i = 1; $i <= 7; $i++ ) {
								if ( $days[$i] == 1 ) $spanstyle = " style=\"font-weight: bold; color: #000000\"";
									else $spanstyle = "";
?>
								<span style="color: #444444">
									<span<?= $spanstyle; ?>><?= $daysarr[$i]; ?></span>
<?php
									if ( $i < 7 ) echo " - ";
?>
								</span>
<?php
							}
?>
						</td>
						<td><?= htmlentities( $cname ); ?></td>
						<td align="center">
							<a class="smallbtn" title="delete" href="?autodel=<?= $aid; ?>">&otimes;</a>
							<a class="smallbtn" title="edit" href="?autoedit=<?= $aid; ?>">&isin;</a>
						</td>
					</tr>
<?php
				}
?>
			</table>
		</form>
		<br /><br />
		
	</div>
