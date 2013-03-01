<?php
	error_reporting ( E_ALL ); 
	// error_reporting(0);
	ini_set ( "display_errors", 'on' );
	include('_commonvars.php');

	include("adodb5/adodb-exceptions.inc.php");
	include("adodb5/adodb.inc.php"); // includes the adodb library
	
	function get_view_definition($view_name){
		global $connessione_1;
		$sql="select * from INFORMATION_SCHEMA.views where TABLE_NAME ='$view_name'";
		$risultato=$connessione_1->Execute($sql);
		$view_definition = $risultato->fields['VIEW_DEFINITION'];
		// ELIMINATING THE CREATE VIEW STATEMENT
		$view_definition = substr($view_definition, strpos($view_definition, "SELECT"));
		$table_schema = $risultato->fields['TABLE_SCHEMA'];
		// ELIMINATING THE SCHEMA.TABLE NOTATION (like dbo.users)
		$view_definition = str_replace($table_schema.".","",$view_definition);
		// REPLACING THE ISNULL FUNCTION (IF PRESENT) WITH coalesce (coalesce can also be used in MSSQL..)
		$view_definition = str_replace("ISNULL(","coalesce(",$view_definition);
		// REPLACING MSSQL KEYWORD-LIKE COLUMN NAMES IN POSTGRE STYLE (replacing [] with ""..)
		$view_definition = str_replace("[","\"",$view_definition);
		$view_definition = str_replace("]","\"",$view_definition);
		return($view_definition);
	}
	
	function db_connect($database_type, $host, $user, $password, $database=NULL){
		$connessione = NewADOConnection($database_type);
		if(isset($database))
			$connessione->Connect($host, $user, $password, $database);
		else
			$connessione->Connect($host, $user, $password);
		return($connessione);
	}
	
	$source_database_type = $adodb_params['database_type'];
    $source_host = $adodb_params['host'];
    $source_user = $adodb_params['user'];
    $source_password = $adodb_params['password'];
    $source_database = $adodb_params['database_name'];
	
	$connessione_1 = db_connect($source_database_type, $source_host, $source_user, $source_password, $source_database);
	$connessione_1->SetFetchMode(ADODB_FETCH_ASSOC);
	
	$views=Array();
	foreach($connessione_1->MetaTables('VIEWS') as $key => $value){
		$views[]= Array(
			"name" => $value,
			"definition" => get_view_definition($value),
			"dependencies" => Array()
		);
	}
	
	echo "UNSORTED VIEWS<br />";
	foreach($views as $v)
		echo $v['name']."<br />";
	// SORT THE VIEWS BY NAME LENGTH (string length)
	// THIS IS NEEDED TO CREATE THE DEPENDENCY TABLE, IN ORDER TO AVOID FALSE POSITIVES
	function cmp_len($a, $b){ 
	   return (strlen($a['name'])  < strlen($b['name']));
	} 
	usort($views, "cmp_len"); 
	
	echo "<br />SORTED VIEWS<br />";
	foreach($views as $v)
		echo $v['name']."<br />";
	
	echo "<br /><br />";
	// BUILD THE DEPENDENCY TABLE
	foreach($views as $key => $view){
		foreach($views as $depends_from_view){
			if(strpos($views[$key]['definition'], $depends_from_view['name'])===false){
			} else {
				$views[$key]['definition'] = str_replace($depends_from_view['name'],"#####",$views[$key]['definition']);
				$views[$key]['dependencies'][]=$depends_from_view['name'];
			}
		}		
		print_r($view);
		echo "<br />";
	}
	
	echo "<br /><br /><br /><br />";
	print_r($views);
	echo "<br /><br /><br /><br />";
	// SORT THE VIEWS IN AN ORDER THAT MAKES THEM CREABLE WITH DEPENDENCIES RESOLVED
	$sorted_views = Array();
	$i=0;
	while(count($views)>0&&$i<5){
		echo $i."<br />";
		foreach($views as $key => $cur_view){
			echo $key."<br />";
			print_r($cur_view);
			foreach($cur_view['dependencies'] as $index => $dependency){
				echo "FOUND DEPENDENCY: $dependency<br />";
				if(in_array($dependency,$sorted_views)){
					unset($views[$key]['dependencies'][$index]);
					echo "UNSETTING DEPENDENCY FROM $dependency<br /><br />";		
				}
			}
			if(count($views[$key]['dependencies'])==0){
				$sorted_views[]=$cur_view['name'];
				echo "MUST UNSET CUR VIEW<br />";
				unset($views[$key]);
			}
		}
		$i=$i+1;
	}
	
	// PRINT THE SORTED VIEWS ON SCREEN
	foreach($sorted_views as $view){
		print_r($view);
		echo "<br />";
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
?>