DB Migration Assistant v0.1
===========================

DB Migration Assistant is a webapp written in php which allows to migrate data from one DB system to another.
It uses a slightly modified version of the popular DB Wrapper class ADODB, and currently allows migration from mssqlnative (Microsoft SQL Server in IIS7.5) to postgres9 (The latest version of postgre)

It can recreate table structures, copy data from tables (the mapping of datatypes are enclosed in special classes in the classes/conversion_drivers folder) and can try copying views. Unfortunately, now it's not possible to convert stored procedures

A better documentation for writing your driver is coming soon!
Right now, in order to test it and maybe create your own driver, you'll have to change the conversion_table.php file, adding to the conversion_table Array  an item with the characteristics of your conversion driver, as:

Array(    "source_database_type" => "mssqlnative", 
		  "dest_database_type" => "postgres9",
		  "driver" => "mssqlnative_2_postgres9" 
		) 
		
Then, you can create your driver in conversion_drivers folder, following the mssqlnative_2_postgres9.php guidelines

Have fun!