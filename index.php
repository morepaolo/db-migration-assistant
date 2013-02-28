<html>
	<head>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<style type="text/css">
			span.error{font-weight:bold;color:#ff0000;font-size:12px;font-weight:bold;}
			span.success{font-weight:bold;color:#339933;font-size:12px;font-weight:bold;}
			span.table_name{font-weight:bold;font-size:13px;}
		</style>
		<script type="text/javascript">
			var connection_values;
			var must_stop=false;
			var tables = [];
			var views = [];
			var rows_spooler = [];
			var rows_x_request = 2000;
			$(function() {
				$("#source_db #database_type option[value='mssqlnative']").attr("selected", "true");
				$("#source_db #host").val("(local)\\SQLEXPRESS");
				$("#source_db #user").val("sa");
				$("#source_db #password").val("sql_749412");
				$("#source_db #database").val("biocity");
				
				$("#dest_db #database_type option[value='postgres9']").attr("selected", "true");
				$("#dest_db #host").val("127.0.0.1");
				$("#dest_db #user").val("postgres");
				$("#dest_db #password").val("lenny85lgl");
				$("#dest_db #database").val("biocity");
				$("#ferma").click(
					function(){
						must_stop=true;
					}
				);
				$("#get_tables").click(
					function(){
						connection_values='source_database_type=#SOURCE_DATABASE_TYPE#&source_host=#SOURCE_HOST#&source_user=#SOURCE_USER#&source_password=#SOURCE_PASSWORD#&source_database=#SOURCE_DATABASE#&dest_database_type=#DEST_DATABASE_TYPE#&dest_host=#DEST_HOST#&dest_user=#DEST_USER#&dest_password=#DEST_PASSWORD#&dest_database=#DEST_DATABASE#';

						connection_values=connection_values.replace(/#SOURCE_DATABASE_TYPE#/gi, $("#source_db #database_type").val());
						connection_values=connection_values.replace(/#SOURCE_HOST#/gi, $("#source_db #host").val());
						connection_values=connection_values.replace(/#SOURCE_USER#/gi, $("#source_db #user").val());
						connection_values=connection_values.replace(/#SOURCE_PASSWORD#/gi, $("#source_db #password").val());
						connection_values=connection_values.replace(/#SOURCE_DATABASE#/gi, $("#source_db #database").val());
						connection_values=connection_values.replace(/#DEST_DATABASE_TYPE#/gi, $("#dest_db #database_type").val());
						connection_values=connection_values.replace(/#DEST_HOST#/gi, $("#dest_db #host").val());
						connection_values=connection_values.replace(/#DEST_USER#/gi, $("#dest_db #user").val());
						connection_values=connection_values.replace(/#DEST_PASSWORD#/gi, $("#dest_db #password").val());
						connection_values=connection_values.replace(/#DEST_DATABASE#/gi, $("#dest_db #database").val());
						$.post(
							"_index.php?action=test_connection",
							connection_values,
							function(data){
								$("div#tables").html(data);
								$.post(
									"_index.php?action=fetch_tables_informations",
									connection_values,
									function(data){
										var result = $.parseJSON(data);
										console.log(result);
										var result_table = $('<table cellpadding="5" cellspacing="0" id="tables"> \
																<tr> \
																	<td colspan="3" style="background-color:#66CCFF;">TABLES</td> \
																</tr> \
																<tr id="tables"> \
																	<td></td><td><input type="checkbox" name="select_all_structure" id="select_all_structure" checked />IMPORT TABLE STRUCTURE</td><td><input type="checkbox" name="select_all_data" id="select_all_data" checked />IMPORT TABLE DATA</td> \
																</tr> \
																<tr> \
																	<td colspan="3" style="background-color:#66CCFF;">VIEWS</td> \
																</tr> \
																<tr id="views"> \
																	<td></td><td><input type="checkbox" name="select_all_view_definition" id="select_all_view_definition" checked />TRY TO TRANSLATE VIEW</td><td></td> \
																</tr> \
															</table>');
										var table_row = '<tr> \
															<td style="border-bottom:1px solid #acacac;">#NAME#</td> \
															<td style="border-bottom:1px solid #acacac;"><input type="checkbox" class="check_structure" name="structure" id="structure" checked /></td> \
															<td style="border-bottom:1px solid #acacac;" class="success"><input type="checkbox" class="check_data" name="data" id="data" checked />#NUM_ROWS# rows</td> \
														</tr>';
										var view_row = '<tr> \
															<td style="border-bottom:1px solid #acacac;">#NAME#</td> \
															<td style="border-bottom:1px solid #acacac;"><input type="checkbox" class="check_definition" name="definition" id="definition" checked /></td> \
															<td style="border-bottom:1px solid #acacac;" class="success">#NUM_ROWS# rows</td> \
														</tr>';
										$.each(result.tables, function(key, item){
											tables.push(
												{
													name: item.name,
													num_rows: item.num_rows,
													import_structure: true,
													import_data: true
												}
											);
											var temp = table_row;
											temp = temp.replace(/#NAME#/gi, item.name);
											temp = temp.replace(/#NUM_ROWS#/gi, item.num_rows);
											temp = $(temp);
											temp.find("#structure").click(
												function(){
													var cur_checkbox=$(this);												
													$.each(tables, function(key, table){
														if(table.name==item.name){
															table.import_structure=cur_checkbox.is(":checked")
															console.log(table);
															console.log("test");
														}
													});
													//console.log(tables);
												}
											);
											temp.find("#data").click(
												function(){
													var cur_checkbox=$(this);												
													$.each(tables, function(key, table){
														if(table.name==item.name){
															table.import_data=cur_checkbox.is(":checked")
															console.log(table);
															console.log("test");
														}
													});
													//console.log(tables);
												}
											);
											result_table.find("#tables").after(temp);
										});
										result_table.find("#select_all_structure").click(
											function(){
												var cur_checkbox=$(this);												
												$.each(tables, function(key, table){
													table.import_structure=cur_checkbox.is(":checked");
												});
												$(".check_structure").attr('checked', cur_checkbox.is(":checked"));
												//console.log(tables);
											}
										);
										result_table.find("#select_all_data").click(
											function(){
												var cur_checkbox=$(this);												
												$.each(tables, function(key, table){
													table.import_data=cur_checkbox.is(":checked");
												});
												$(".check_data").attr('checked', cur_checkbox.is(":checked"));
												//console.log(tables);
											}
										);
										$.each(result.views, function(key, item){
											views.push(
												{
													name: item.name,
													import_structure: true
												}
											);
											var temp = view_row;
											temp = temp.replace(/#NAME#/gi, item.name);
											temp = temp.replace(/#NUM_ROWS#/gi, item.num_rows);
											temp = $(temp);
											temp.find("#definition").click(
												function(){
													var cur_checkbox=$(this);												
													$.each(views, function(key, view){
														if(view.name==item.name){
															view.import_structure=cur_checkbox.is(":checked")
															console.log(view);
															console.log("test");
														}
													});
													//console.log(tables);
												}
											);
											result_table.find("#views").after(temp);
										});
										result_table.find("#select_all_view_definition").click(
											function(){
												var cur_checkbox=$(this);												
												$.each(views, function(key, view){
													console.log(view);
													view.import_structure=cur_checkbox.is(":checked");
												});
												$(".check_definition").attr('checked', cur_checkbox.is(":checked"));
												//console.log(tables);
											}
										);
										$("div#tables").append(result_table);
										$("#avvia").show();
									}
								);
							}
						);
					}
				);
				$("#avvia").click(
					function(){
						rows_spooler = [];
						
						$.each(tables, function(key, table){
							if(table.import_data){
								var offset=0;
								while(offset<=table.num_rows){
									rows_spooler.push(
										{
											table: table.name,
											num_rows: table.num_rows,
											offset: offset
										}
									);
									offset+=rows_x_request;
								}
							}
						});
						console.log(rows_spooler);
						console.log(views);
						$("#avvia").hide();
						$("#ferma").show();
						connection_values='source_database_type=#SOURCE_DATABASE_TYPE#&source_host=#SOURCE_HOST#&source_user=#SOURCE_USER#&source_password=#SOURCE_PASSWORD#&source_database=#SOURCE_DATABASE#&dest_database_type=#DEST_DATABASE_TYPE#&dest_host=#DEST_HOST#&dest_user=#DEST_USER#&dest_password=#DEST_PASSWORD#&dest_database=#DEST_DATABASE#';

						connection_values=connection_values.replace(/#SOURCE_DATABASE_TYPE#/gi, $("#source_db #database_type").val());
						connection_values=connection_values.replace(/#SOURCE_HOST#/gi, $("#source_db #host").val());
						connection_values=connection_values.replace(/#SOURCE_USER#/gi, $("#source_db #user").val());
						connection_values=connection_values.replace(/#SOURCE_PASSWORD#/gi, $("#source_db #password").val());
						connection_values=connection_values.replace(/#SOURCE_DATABASE#/gi, $("#source_db #database").val());
						connection_values=connection_values.replace(/#DEST_DATABASE_TYPE#/gi, $("#dest_db #database_type").val());
						connection_values=connection_values.replace(/#DEST_HOST#/gi, $("#dest_db #host").val());
						connection_values=connection_values.replace(/#DEST_USER#/gi, $("#dest_db #user").val());
						connection_values=connection_values.replace(/#DEST_PASSWORD#/gi, $("#dest_db #password").val());
						connection_values=connection_values.replace(/#DEST_DATABASE#/gi, $("#dest_db #database").val());
						$.post(
							"_index.php?action=test_connection",
							connection_values,
							function(data){
								$("div#result").html(data);
								var chain = $.Deferred(); // Create the root of the chain.
								var promise; // Placeholder for the promise
								var temp_tables = JSON.parse(JSON.stringify(tables)); 
								// Build the chain
								for(var i = 0; i < tables.length; i++)
								{
									if(i == 0) promise = chain;
								 
									// Pipe the response to the "next" function
									promise = promise.pipe(function(response)
									{
										if(must_stop)
											return(true);
										var table = this.shift(); // Get the current part
										
										if(table.import_structure){
											var temp_message = createMessage(result.code, "COPYING TABLE STRUCTURE <span class='table_name'>"+table.name+"</span>");
											$("div#result").append(temp_message);
											$("div#result").scrollTop($("div#result")[0].scrollHeight);
											return $.ajax({
												type: 'POST',
												url: "_index.php?action=import_table_structure&table_name="+table.name,
												data: connection_values,
												context: this,
												success: function(data){
													//console.log(data);
													var result = $.parseJSON(data);
													if(result.code==0){
														temp_message.find("#result").addClass("success");
														temp_message.find("#result").html("OK");
													} else {
														temp_message.find("#result").addClass("error");
														temp_message.find("#result").html("ERROR: "+result.text);
													}
												}
											});
										} else
											return(true);
									})
								}
								 
								promise.done(function(response){
									console.log(rows_spooler);
									if(must_stop){
										var temp_message = createMessage(result.code, "IMPORT INTERRUPTED BY USER");
										$("div#result").append(temp_message);
										must_stop=false;
										$("#avvia").show();
										$("#ferma").hide();
									} else {
										var temp_message = createMessage(result.code, "ALL TABLES STRUCTURES IMPORTED");
										$("div#result").append(temp_message);
										/* QUESTA SECONDA CHAIN COPIA I DATI DELLE TABELLE */
										var chain_copy_table_data = $.Deferred(); // Create the root of the chain.
										var promise_copy_table_data; // Placeholder for the promise
										 
										// Build the chain
										promise_copy_table_data = chain_copy_table_data;
										for(var i = 0; i < rows_spooler.length; i++)
										{
										 
											// Pipe the response to the "next" function
											promise_copy_table_data = promise_copy_table_data.pipe(function(response)
											{
												if(must_stop)
													return(true);
												var row_block = this.shift(); // Get the current part
												
												var temp_message = createMessage(result.code, "EXPORTING TABLE DATA  <span class='table_name'>"+row_block.table+"</span> "+(row_block.offset+rows_x_request)+"/"+row_block.num_rows);
												temp_message.find("#result").append('<img src="images/loader.gif" style="height:20px;" />');
												$("div#result").append(temp_message);
												$("div#result").scrollTop($("div#result")[0].scrollHeight);
												return $.ajax({
														type: 'POST',
														url: "_index.php?action=import_table_data&table_name="+row_block.table+"&offset="+row_block.offset+"&rows_x_request="+rows_x_request,
														data: connection_values,
														context: this,
														success: function(data){
															var result = $.parseJSON(data);
															if(result.code==0){
																
																temp_message.find("#result").addClass("success");
																temp_message.find("#result").html("OK");
															} else {
																temp_message.find("#result").addClass("error");
																temp_message.find("#result").html("ERROR: "+result.text);
															}
														}
													}
												);
											})
										}
										 
										promise_copy_table_data.done(function(response){
											if(must_stop){
												var temp_message = createMessage(result.code, "IMPORT INTERRUPTED BY USER");
												$("div#result").append(temp_message);
												must_stop=false;
												$("#avvia").show();
												$("#ferma").hide();
											}else{
												var temp_message = createMessage(result.code, "ALL TABLES DATA IMPORTED");
												$("div#result").append(temp_message);
												/* LA TERZA CHAIN IMPORTA LE DEFINIZIONI DELLE VIEWS */
												var chain_copy_view_definition = $.Deferred(); // Create the root of the chain.
												var promise_copy_view_definition; // Placeholder for the promise
												var temp_views = JSON.parse(JSON.stringify(views)); 
												 
												// Build the chain
												promise_copy_view_definition = chain_copy_view_definition;
												for(var i = 0; i < views.length; i++)
												{
													// Pipe the response to the "next" function
													promise_copy_view_definition = promise_copy_view_definition.pipe(function(response)
													{
														if(must_stop)
															return(true);
														var view_def = this.shift(); // Get the current part
														
														var temp_message = createMessage(result.code, "EXPORTING VIEW DEFINITION <span class='table_name'>"+view_def.name+"</span>");
														temp_message.find("#result").append('<img src="images/loader.gif" style="height:20px;" />');
														$("div#result").append(temp_message);
														$("div#result").scrollTop($("div#result")[0].scrollHeight);
														return $.ajax({
																type: 'POST',
																url: "_index.php?action=import_view_definition&view_name="+view_def.name,
																data: connection_values,
																context: this,
																success: function(data){
																	var result = $.parseJSON(data);
																	if(result.code==0){
																		
																		temp_message.find("#result").addClass("success");
																		temp_message.find("#result").html("OK");
																	} else {
																		temp_message.find("#result").addClass("error");
																		temp_message.find("#result").html("ERROR: "+result.text);
																	}
																}
															}
														);
													})
												}
												 
												promise_copy_view_definition.done(function(response){
													if(must_stop)
														var temp_message = createMessage(result.code, "IMPORT INTERRUPTED BY USER");
													else
														var temp_message = createMessage(result.code, "ALL VIEW DEFINITIONS IMPORTED");
													$("div#result").append(temp_message);
													must_stop=false;
													$("#avvia").show();
													$("#ferma").hide();
												});
												 
												chain_copy_view_definition.resolveWith(temp_views); // Execute the chain
											}											
										});
										 
										chain_copy_table_data.resolveWith(rows_spooler); // Execute the chain
									}
								});
								 
								chain.resolveWith(temp_tables); // Execute the chain
							
							}
						);
					
					}
				);
			});
			function createMessage(code, message){
				var message_box = '<div class="message_#CODE#"> \
										#MESSAGE#&nbsp;<span id="result"></span> \
									</div>';
				message_box = message_box.replace(/#CODE#/gi, code);
				message_box = message_box.replace(/#MESSAGE#/gi, message);
				return($(message_box));
			}		
		</script>
	</head>
	<body>
		<table cellpadding="4" cellspacing="0">
			<tr>
				<td>SOURCE DB</td>
				<td id="source_db" style="border-right:1px solid black;">
					<table cellpadding="4" cellspacing="0">
						<tr>
							<td>DATABASE TYPE</td>
							<td>
								<?php include("_database_types_select.php"); ?>
							</td>
						</tr>
						<tr>
							<td>HOST</td>
							<td><input type="text" name="host" id="host" value="" size="30" /></td>
						</tr>
						<tr>
							<td>USER</td>
							<td><input type="text" name="user" id="user" value="" size="30" /></td>
						</tr>
						<tr>
							<td>PASSWORD</td>
							<td><input type="text" name="password" id="password" value="" size="30" /></td>
						</tr>
						<tr>
							<td>DATABASE</td>
							<td><input type="text" name="database" id="database" value="" size="30" /></td>
						</tr>
					</table>
				</td>
				<td>DEST DB</td>
				<td id="dest_db">
					<table cellpadding="4" cellspacing="0">
						<tr>
							<td>DATABASE TYPE</td>
							<td>
								<?php include("_database_types_select.php"); ?>
							</td>
						</tr>
						<tr>
							<td>HOST</td>
							<td><input type="text" name="host" id="host" value="" size="30" /></td>
						</tr>
						<tr>
							<td>USER</td>
							<td><input type="text" name="user" id="user" value="" size="30" /></td>
						</tr>
						<tr>
							<td>PASSWORD</td>
							<td><input type="text" name="password" id="password" value="" size="30" /></td>
						</tr>
						<tr>
							<td>DATABASE</td>
							<td><input type="text" name="database" id="database" value="" size="30" /></td>
						</tr>
					</table>
				</td>
			</tr>	
			<tr>
				<td colspan="4" style="text-align:center;">
					<input type="button" name="get_tables" id="get_tables" value="EXTRACT TABLE STRUCTURE" style="width:200px;height:40px;"/>
				</td>
			</tr>
			<tr>
				<td colspan="4">
					<div id="tables" style="width:800px;height:300px;overflow:auto;"></div>
				</td>
			</tr>
			<tr>
				<td colspan="4" style="text-align:center;">
					<input type="button" name="avvia" id="avvia" value="START IMPORT" style="width:200px;height:40px;display:none;"/>
					<input type="button" name="ferma" id="ferma" value="STOP" style="width:200px;height:40px;display:none;"/>
				</td>
			</tr>
			<tr>
				<td colspan="4">
					<div id="result" style="width:800px;height:300px;overflow:auto;font-size:11px;font-family:courier, helvetica, sans-serif;"></div>
				</td>
			</tr>
		</table>
	</body>
</html>