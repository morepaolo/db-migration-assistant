<?php
class conversion_table {

	private $conversion_table = array( array( "source_database_type" => "mssqlnative", 
											  "dest_database_type" => "postgres9",
											  "driver" => "mssqlnative_2_postgres9" 
											)
									);
	
	public $driver_name="";
	public $driver;
	
	public function __construct($source, $dest){
		foreach($this->conversion_table as $item){
			if($item['source_database_type']==$source && $item['dest_database_type']==$dest)
				$this->driver_name=$item['driver'];
		}
		if($this->driver_name=="")
			throw new Exception("CONVERSION DRIVER NOT FOUND");
		else {
			include('./classes/conversion_drivers/'.$this->driver_name.".php");
			$this->driver = new $this->driver_name();
		}
	}
	
}
?>