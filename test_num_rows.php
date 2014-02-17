<?php
	error_reporting ( E_ALL ); 
	// error_reporting(0);
	ini_set ( "display_errors", 'on' );

	include("adodb5/adodb-exceptions.inc.php");
	include("adodb5/adodb.inc.php"); // includes the adodb library
	
	function db_connect($database_type, $host, $user, $password, $database=NULL){
		$connessione = NewADOConnection($database_type);
		if(isset($database))
			$connessione->Connect($host, $user, $password, $database);
		else
			$connessione->Connect($host, $user, $password);
		return($connessione);
	}
	
	date_default_timezone_set("Europe/Rome");
	echo "TESTING<br />";
	$source_database_type = "mssqlnative";
    $source_host = "(local)\SQLEXPRESS";
    $source_user = "sa";
    $source_password = "sql_749412";
    $source_database = "biocity";
	$connessione_1 = db_connect($source_database_type, $source_host, $source_user, $source_password, $source_database);
	$rows_x_page = 2000;
	$num_rows = 2000;
	$sql="select * from chat_messages";
	$i=0;
	$cumulative =0;
	while($num_rows==$rows_x_page){
		echo "T";
		flush();
		$risultato=$connessione_1->SelectLimit($sql, $rows_x_page, $i*$rows_x_page);
		$num_rows=$risultato->RecordCount();
		$i=$i+1;
		$cumulative = $cumulative+$num_rows;
	}
	
	echo "TOTAL ROWS FETCHED: $cumulative";
	
?>
