<?php
	
	error_reporting ( E_ALL ); 
	// error_reporting(0);
	ini_set ( "display_errors", 'on' );

	include("adodb5/adodb-exceptions.inc.php");
	include("adodb5/adodb.inc.php"); // includes the adodb library
	include("classes/generic_driver.php");
	include("classes/conversion_table.php");
	include("classes/Encoding.php");
	
	date_default_timezone_set("Europe/Rome");
	
	$source_database_type = $_POST['source_database_type'];
    $source_host = $_POST['source_host'];
    $source_user = $_POST['source_user'];
    $source_password = $_POST['source_password'];
    $source_database = $_POST['source_database'];
    $dest_database_type = $_POST['dest_database_type'];
    $dest_host = $_POST['dest_host'];
    $dest_user = $_POST['dest_user'];
    $dest_password = $_POST['dest_password'];
    $dest_database = $_POST['dest_database'];
	
	

	if($_GET['action']=="test_connection"){	
		try{
			$conversion_table = new conversion_table($source_database_type, $dest_database_type);
			try{
				$connessione_1 = db_connect($source_database_type, $source_host, $source_user, $source_password, $source_database);
				echo "SUCCESFULLY CONNECTED TO $source_host DATABASE $source_database, driver $source_database_type<br />";
			} catch (Exception $e){
				print_r($e->getMessage());
			}
		} catch (Exception $e){
			print_r($e->getMessage());
		}
		
		try{
			$connessione_2 = db_connect($dest_database_type, $dest_host, $dest_user, $dest_password, $dest_database);
			echo "SUCCESFULLY CONNECTED TO $dest_host DATABASE $dest_database, driver $dest_database_type\r\n";
		} catch (ADODB_Exception $e){
			if($e->getCode()==-1){
				echo "ERROR CONNECTING TO $dest_host, driver $dest_database_type: DATABASE $dest_database NOT EXISTING. CREATING IT...\r\n"; 
				try{
					$temp = db_connect($dest_database_type, $dest_host, $dest_user, $dest_password);
					$dict = NewDataDictionary($temp);
					$sql = $dict->CreateDatabase($dest_database);
					if (2 == $dict->ExecuteSQLArray($sql)){
						$connessione_2 = db_connect($dest_database_type, $dest_host, $dest_user, $dest_password, $dest_database);
						echo "DATABASE $dest_database SUCCESSFULLY CREATED\r\n";
					} else {
						echo "UNABLE TO CREATE DATABASE $dest_database\r\n";
					}
				} catch (Exception $e){
					print_r("EXCEPTION");
				}
			}
		}
	} elseif ($_GET['action']=="fetch_tables_informations"){
		$connessione_1 = db_connect($source_database_type, $source_host, $source_user, $source_password, $source_database);
		$connessione_1->SetFetchMode(ADODB_FETCH_ASSOC);
		$connessione_2 = db_connect($dest_database_type, $dest_host, $dest_user, $dest_password, $dest_database);
		$connessione_2->SetFetchMode(ADODB_FETCH_ASSOC);
		$conversion_table = new conversion_table($source_database_type, $dest_database_type);
		$conversion_table->driver->set_source_dest($connessione_1, $connessione_2);
		$result = Array();
		if(count($connessione_1->MetaTables('TABLES'))>0){
			$result['code'] = 0;
			$result['tables'] = Array();
			foreach($connessione_1->MetaTables('TABLES') as $key => $value){
				$temp = Array(
					"name" => $value,
					"num_rows" => $conversion_table->driver->count_rows($value)
				);
				$result['tables'][] = $temp;
			}
			// EXTRACTING THE DB VIEWS IN AN ORDER THAT RESOLVES DEPENDENCIES, ALLOWING FOR SEQUENTIAL CREATION
			$temp = $conversion_table->driver->sort_views_by_dependency();
			foreach($temp as $key => $value){
				$temp = Array(
					"name" => $value,
					"num_rows" => $conversion_table->driver->count_rows($value)
				);
				$result['views'][] = $temp;
			}
		} else {
			$result['code'] = 1;
			$result['message'] = "Cannot extract table informations";
		}
		echo json_encode($result);
	} elseif ($_GET['action']=="import_table_structure"){
		$conversion_table = new conversion_table($source_database_type, $dest_database_type);
		$connessione_1 = db_connect($source_database_type, $source_host, $source_user, $source_password, $source_database);
		$connessione_1->SetFetchMode(ADODB_FETCH_ASSOC);
		$connessione_2 = db_connect($dest_database_type, $dest_host, $dest_user, $dest_password, $dest_database);
		$connessione_2->SetFetchMode(ADODB_FETCH_ASSOC);
		$conversion_table->driver->set_source_dest($connessione_1, $connessione_2);
		$table_name = $_GET['table_name'];
		try{
			$text = $conversion_table->driver->clone_table($table_name);
			$result = Array();
			$result['code']=0;
			$result['text']=$text;
			$num_rows = $conversion_table->driver->count_rows($table_name);
			$result['rows_found'] = $num_rows;
		} catch(Exception $e){
			$result = Array();
			$result['code']=1;
			$result['text']=$e->getCode()." ".$e->getMessage();
		}
		echo json_encode($result);
	} elseif($_GET['action']=="import_table_data"){
		$table_name = $_GET['table_name'];
		$offset = $_GET['offset'];
		$rows_x_request = $_GET['rows_x_request'];
		
		$conversion_table = new conversion_table($source_database_type, $dest_database_type);
		$connessione_1 = db_connect($source_database_type, $source_host, $source_user, $source_password, $source_database);
		$connessione_1->SetFetchMode(ADODB_FETCH_ASSOC);
		$connessione_2 = db_connect($dest_database_type, $dest_host, $dest_user, $dest_password, $dest_database);
		$connessione_2->SetFetchMode(ADODB_FETCH_ASSOC);
		$conversion_table->driver->set_source_dest($connessione_1, $connessione_2);
		
		$conversion_table->driver->import_rows($table_name, $rows_x_request, $offset);
		
		$result = Array();
		$result['code']=0;
		echo json_encode($result);
	} elseif ($_GET['action']=="import_view_definition"){
		$conversion_table = new conversion_table($source_database_type, $dest_database_type);
		$connessione_1 = db_connect($source_database_type, $source_host, $source_user, $source_password, $source_database);
		$connessione_1->SetFetchMode(ADODB_FETCH_ASSOC);
		$connessione_2 = db_connect($dest_database_type, $dest_host, $dest_user, $dest_password, $dest_database);
		$connessione_2->SetFetchMode(ADODB_FETCH_ASSOC);
		$conversion_table->driver->set_source_dest($connessione_1, $connessione_2);
		$view_name = $_GET['view_name'];
		try{
			$text = $conversion_table->driver->clone_view($view_name);
			$result = Array();
			$result['code']=0;
			$result['text']=$text;
		} catch(Exception $e){
			$result = Array();
			$result['code']=1;
			$result['text']=$e->getCode()." ".$e->getMessage();
		}
		echo json_encode($result);
	}
	
	
	
	function db_connect($database_type, $host, $user, $password, $database=NULL){
		$connessione = NewADOConnection($database_type);
		if(isset($database))
			$connessione->Connect($host, $user, $password, $database);
		else
			$connessione->Connect($host, $user, $password);
		return($connessione);
	}
	
?>