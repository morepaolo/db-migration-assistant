<?php

error_reporting ( E_ALL ); 
// error_reporting(0);
ini_set ( "display_errors", 'on' );

date_default_timezone_set("Europe/Rome");
	

$admin_email="morepaolo@gmail.com";

// DA QUA IN POI TESTO ADODB
$adodb_params = Array(
	"database_type" => "mssqlnative",
	"host" => "(local)\SQLEXPRESS",
	"user" => "sa",
	"password" => "sql_749412",
	"database_name" => "biocity"
);

?>