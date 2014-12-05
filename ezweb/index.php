<?php
/*
 * ezweb by P. Giebel <spam@stimpyrama.org>
 *
 * Created on 24.12.2007 
 */

session_name("ezweb");
session_start();
if ( !@include( "data/include/config.inc.php" ) ) die( "error loading config-file" );
if ( !@include( "data/include/unit_main.inc.php" ) ) die( "error loading main unit" );
get_vars();

if ( !@require_once( "DB.php" ) ) die( "error loading PEAR:DB - please install using pear..." );

$dsn = $cfg["sql"]["type"] ."://". $cfg["sql"]["user"] .":". $cfg["sql"]["pass"] ."@". $cfg["sql"]["host"] ."/". $cfg["sql"]["db"];

$db =& DB::connect( $dsn, $cfg["sql"]["options"] );
if ( PEAR::isError( $db ) ) {
     die( $db->getMessage() );
}

$query = "SELECT fid, played FROM history ORDER BY id DESC LIMIT 1";
$res = $db->query( $query );
$row = $res->fetchrow( DB_FETCHMODE_ASSOC );
$fid = intval( $row["fid"] );
$started = $row["played"];

$query = "SELECT * FROM files WHERE id='$fid'";
$res = $db->query( $query );
$row = $res->fetchrow( DB_FETCHMODE_ASSOC );

$end = $started+intval( $row["length"] );
$remain = $end-intval( time() )+5;

if ( ( count( $_POST ) > 0 ) OR ( count( $_GET ) > 0 ) ) {
	$reload = "<meta http-equiv=\"refresh\" content=\"0;URL=/\" />\n";
	$r = false;
} else {
	$reload =  "<meta http-equiv=\"refresh\" content=\"". $remain .";URL=/\" />\n";
	$r = true; 
}

echo "<?xml version=\"1.0\"?>\r\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>ezWeb</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=iso-8859-15" />
	<link rel="stylesheet" type="text/css" href="<?php echo $basepath; ?>data/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $basepath; ?>data/css/framework.css" />
	<?= $reload; ?>
</head>
<body>
<?php
	if ( $r ) {
		
	
?>
		<div id="fw_toppart">
			<div id="fw_nowplaying">
<?php
					if ( !@include( "data/php/nowplaying.inc.php" ) ) echo "error loading now playing window" ;
?>
			</div>
			<div id="fw_currtime">
<?php
					if ( !@include( "data/php/currtime.inc.php" ) ) echo "error loading now current time window" ;
?>
			</div>
		</div>
		<div id="fw_midpart">
			<div id="fw_rightcol">
				<div id="fw_collections">
<?php
					if ( !@include( "data/php/collections.inc.php" ) ) echo "error loading collections window" ;
?>
				</div>
			</div>
			<div id="fw_leftcol">
				<div id="fw_queue">
<?php
					if ( !@include( "data/php/queue.inc.php" ) ) echo "error loading queue window" ;
?>
				</div>
				<div id="fw_history">
<?php
					if ( !@include( "data/php/history.inc.php" ) ) echo "error loading history window" ;
?>
				</div>
			</div>
		</div>
		<div id="fw_automat">
<?php
				if ( !@include( "data/php/automat.inc.php" ) ) echo "error loading automat window" ;
?>
		</div>
<?php
	} else {
		echo "reloading...";
	}
?>

</body>
</html>
