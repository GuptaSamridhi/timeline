<!DOCTYPE html>
<?php
	$today = date('Y-m-d');
	$start = strtotime('2018-12-25');
	$curr = $start;
	$week = array('SUN','MON','TUE','WED','THU','FRI','SAT');
?>

<html lang="en">
<head>
	<!-- <meta charset="UTF-8"> -->
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
	<!-- <meta http-equiv="X-UA-Compatible" content="ie=edge"> -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
	<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
	<title>Timeline</title>
	<style>
		.rotate, #previous, #next {
			height: 140px;
			white-space: nowrap;
		}

		.rotate div, #previous div{
			transform: 
				rotate(-90deg);
			width: 30px;
		}
		#next div{
			transform: 
			rotate(90deg);
			width: 30px;
		}
		.rotate div span {
			padding: 5px 10px;
			color: #fff;
		}
		#previous div span {
			padding: 5px 50px;
		}
		#next div span{
			padding: 5px;
			margin-left: -75px;
		}
		.active-cell{
			background: #DDF2D2;
		}
		.inactive-cell{
			background: #E4E4E4;
		}
		.inactive-table-head{
			background: #C7E2FA;
		}
		.active-table-head{
			background: #2F72D5;
		}
		.hidden{
			display: none;
		}
		th:nth-child(1){
			vertical-align: middle !important;
		}
	</style>
</head>
<body>
	<div class="container mt-5">
		<table class="table table-bordered" id="timeline">
			<thead>
				<tr>
					<th rowspan='2'>Task</th>
					<th id="previous" class="inactive-table-head" rowspan='2'><div><span>Previous</span></div></th>
					<?php for($i=0;$i<15;$i++){ ?>
						<th class="rotate active-table-head"><div><span><?php echo date('Y-m-d',$curr); $curr = strtotime("+1 day", $curr);?></span></div></th>
					<?php } 
						$curr = $start;
					?>
					<th id="next" class="inactive-table-head" rowspan='2'><div><span>Next</span></div></th>
				</tr>
				<tr>
					<?php for($i=0;$i<15;$i++){ ?>
						<th class="inactive-table-head"><?php echo $week[date('w', $curr)]; $curr = strtotime("+1 day", $curr);?></th>
					<?php } ?>
				<tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<div class="text-right">
			<button class="btn btn-primary" id="exit">EXIT</button>
			<button class="btn btn-primary" id="save">SAVE</button>
		</div>
	</div>
	
	<div class="modal fade" id="remark-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel">remark</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
				<div class="modal-body">
					<textarea class="col-md-12" id="remark-value"></textarea>
					</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" id="save-remark">Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<script>
		var start_ts = <?php echo $start*1000?>;
		start_ts = new Date(start_ts);
		var st_date = start_ts.getFullYear()+'-'+((start_ts.getMonth()+1<10)?'0'+(start_ts.getMonth()+1):start_ts.getMonth()+1)+'-'+((start_ts.getDate()<10)?'0'+start_ts.getDate():start_ts.getDate());
		var end_ts = <?php echo strtotime("+14 days",$start)*1000?>;
		end_ts = new Date(end_ts);
		var end_date = end_ts.getFullYear()+'-'+((end_ts.getMonth()+1<10)?'0'+(end_ts.getMonth()+1):end_ts.getMonth()+1)+'-'+((end_ts.getDate()<10)?'0'+end_ts.getDate():end_ts.getDate());
		var data;
		var week = ['SUN','MON','TUE','WED','THU','FRI','SAT'];
		var empId;

		function arrangeData(data){
			var response = {};
			data.forEach(function(element) {
				if(!(element['tst_prj_id'] in response)){
					response[element['tst_prj_id']] = {};
				}
			});
			

			data.forEach(function(element) {
				if(!(element['tst_task_id'] in response[element['tst_prj_id']])){
					response[element['tst_prj_id']][element['tst_task_id']] = {};
				}
			});

			data.forEach(function(element) {
				if(!(element['tst_date'] in response[element['tst_prj_id']][element['tst_task_id']])){
					response[element['tst_prj_id']][element['tst_task_id']][element['tst_date']] = {'tst_hours':element['tst_hours'],'tst_remark':element['tst_remark']};
					empId = element['tst_emp_id'];
				}
			});
			return response;
		}
	</script>

	<script>
		function mergeNewCol(result,col){
			col.forEach(function(element) {
				result[element['tst_prj_id']][element['tst_task_id']][element['tst_date']] = {'tst_hours':element['tst_hours'],'tst_remark':element['tst_remark']};
			});
			return result;
		}
	</script>

	<script>
		$(document).ready(function(){ 
			$.ajax({
			  	url: 'http://hmsdev.dsssoft.com/index_ws01.php?action=352527&token=RRR&cred={"sub_id":"HUB190700007","usr_id":"u0002"}&data={"tst_emp_id":"EMP_001","tst_st_date":"'+st_date+'","tst_end_date":"'+end_date+'","tst_file_handler":"FH03"}', 
				type: 'GET',
				success: function(response){
					data = arrangeData(response['result']);
			
					var projects = Object.keys(data);
					var table = $("#timeline tbody");
					projects.forEach(function(project) {
						var projectRow;
						var projectCells = "<td class='inactive-cell' colspan='2'>"+project+"</td>";
						for(var i=0;i<16;i++){
							projectCells += "<td class='inactive-cell'></td>";
						}
						projectRow = "<tr class="+project+">"+projectCells+"</tr>";
						table.append(projectRow);
						var tasks = Object.keys(data[project]);
						tasks.forEach(function(task) {
							var taskRow;
							var taskCells = "<td colspan='2'>"+task+"</td>";
							var tmp_date = new Date(start_ts);
							var dates = Object.keys(data[project][task]);

							for(i=0;i<15;i++){
								var ymd = tmp_date.getFullYear()+'-'+((tmp_date.getMonth()+1<10)?'0'+(tmp_date.getMonth()+1):tmp_date.getMonth()+1)+'-'+((tmp_date.getDate()<10)?'0'+tmp_date.getDate():tmp_date.getDate());
								
								if(dates.includes(ymd)){
								
									taskCells += "<td class='active-cell'>"
												+"<span class='hours' contenteditable>"+data[project][task][ymd]['tst_hours']+"</span>"
												+"<span class='hidden remark'>"+data[project][task][ymd]['tst_remark']+"</span>"
											+"</td>";	
								}
								else{
									taskCells += "<td class='active-cell'>"
												+"<span class='hours' contenteditable></span>"
												+"<span class='hidden remark'></span>"
											+"</td>";
								}
								tmp_date.setDate(tmp_date.getDate()+1)
							}
							taskCells += "<td></td>";
							taskRow = "<tr class='"+project+" "+task+"'>"+taskCells+"</tr>";
							table.append(taskRow);
						});
					});
					
			    }
			});
		});
	</script>

	<script>
		$(document).on('click','#previous',function(){
			colData = [];
			$("#timeline tbody").find('tr').each(function(){
				var classes = $(this).attr("class").toString().split(' ');
				if(classes.length == 2){
					var cellData = {};
					if($(this).find('td').eq(15).find('.hours').text()!=""){
						cellData.tst_emp_id = empId;
						cellData.tst_prj_id = classes[0];
						cellData.tst_task_id = classes[1];
						cellData.tst_date = end_date;
						cellData.tst_hours = $(this).find('td').eq(15).find('.hours').text();
						cellData.tst_remark = $(this).find('td').eq(15).find('.remark').text();
						cellData.tst_file_handler = "FH03";
						colData.push(cellData);
					}
				}
			});
			colData = JSON.stringify(colData);
			if(Object.keys(JSON.parse(colData)).length > 0){
				$.ajax({
					url: 'http://hmsdev.dsssoft.com/index_ws01.php?action=352526&token=RRR&cred={"sub_id":"HUB190700007","usr_id":"u0001"}&data='+colData, 
					type: 'GET'
				});
				var date_ts = new Date(end_ts);

				date = date_ts.getFullYear()+'-'+((date_ts.getMonth()+1<10)?'0'+(date_ts.getMonth()+1):date_ts.getMonth()+1)+'-'+((date_ts.getDate()<10)?'0'+date_ts.getDate():date_ts.getDate());
				
				var projects = Object.keys(data);
				projects.forEach(function(project) {
					tasks = Object.keys(data[project]);
					tasks.forEach(function(task) {
						var dates = Object.keys(data[project][task]);
						if(dates.includes(date)){
							delete data[project][task][date];
						}
					});
				});
			}
			

			start_ts.setDate(start_ts.getDate()-1);
			end_ts.setDate(end_ts.getDate()-1);
			st_date = start_ts.getFullYear()+'-'+((start_ts.getMonth()+1<10)?'0'+(start_ts.getMonth()+1):start_ts.getMonth()+1)+'-'+((start_ts.getDate()<10)?'0'+start_ts.getDate():start_ts.getDate());
			end_date = end_ts.getFullYear()+'-'+((end_ts.getMonth()+1<10)?'0'+(end_ts.getMonth()+1):end_ts.getMonth()+1)+'-'+((end_ts.getDate()<10)?'0'+end_ts.getDate():end_ts.getDate());
			
			$.ajax({
			  	url: 'http://hmsdev.dsssoft.com/index_ws01.php?action=352527&token=RRR&cred={"sub_id":"HUB190700007","usr_id":"u0002"}&data={"tst_emp_id":"EMP_001","tst_st_date":"'+st_date+'","tst_end_date":"'+st_date+'","tst_file_handler":"FH03"}', 
				type: 'GET',
				success: function(response){
					$("#timeline thead").find('tr').find('th').eq(1).after('<th class="rotate active-table-head"><div><span>'+st_date+'</span></div></th>');
					$("#timeline thead").find('tr').find('th').eq(17).remove();

					$("#timeline thead").find('tr').eq(1).find('th').eq(0).before('<th class="inactive-table-head">'+week[start_ts.getDay()]+'</th>')
					$("#timeline thead").find('tr').eq(1).find('th').eq(15).remove();
					
					


					if(jQuery.isEmptyObject(response['result'])){
						$('#timeline tbody').find('tr').each(function(){
							if($(this).find('td').eq(2).hasClass('inactive-cell')){
								$(this).find('td').eq(1).after("<td class='inactive-cell'></td>");
							}
							else{
								$(this).find('td').eq(0).after("<td class='active-cell'>"
																	+"<span contenteditable='true' class='hours'></span>"
																	+"<span class='hidden remark'></span>"
																+"</td>");
							}
							
							$(this).find('td').eq(16).remove();
					
						});
					}
					else{
						
						
								$("#timeline tbody").html('');
								data = mergeNewCol(data,response['result']);
								
								var projects = Object.keys(data);
								var table = $("#timeline tbody");
								projects.forEach(function(project) {
									var projectRow;
									var projectCells = "<td class='inactive-cell' colspan='2'>"+project+"</td>";
									for(var i=0;i<16;i++){
										projectCells += "<td class='inactive-cell'></td>";
									}
									projectRow = "<tr class="+project+">"+projectCells+"</tr>";
									table.append(projectRow);
									var tasks = Object.keys(data[project]);
									tasks.forEach(function(task) {
										var taskRow;
										var taskCells = "<td colspan='2'>"+task+"</td>";
										var tmp_date = new Date(start_ts);
										var dates = Object.keys(data[project][task]);

										for(i=0;i<15;i++){
											var ymd = tmp_date.getFullYear()+'-'+((tmp_date.getMonth()+1<10)?'0'+(tmp_date.getMonth()+1):tmp_date.getMonth()+1)+'-'+((tmp_date.getDate()<10)?'0'+tmp_date.getDate():tmp_date.getDate());
											
											if(dates.includes(ymd)){
											
												taskCells += "<td class='active-cell'>"
															+"<span class='hours' contenteditable>"+data[project][task][ymd]['tst_hours']+"</span>"
															+"<span class='hidden remark'>"+data[project][task][ymd]['tst_remark']+"</span>"
														+"</td>";	
											}
											else{
												taskCells += "<td class='active-cell'>"
															+"<span class='hours' contenteditable></span>"
															+"<span class='hidden remark'></span>"
														+"</td>";
											}
											tmp_date.setDate(tmp_date.getDate()+1)
										}
										taskCells += "<td></td>";
										taskRow = "<tr class='"+project+" "+task+"'>"+taskCells+"</tr>";
										table.append(taskRow);
									});
								});
							
					}
					
				}
			});
		});
	</script>

	<script>
		$(document).on('click','#next',function(){
			colData = [];
			$("#timeline tbody").find('tr').each(function(){
				var classes = $(this).attr("class").toString().split(' ');
				if(classes.length == 2){
					var cellData = {};
					if($(this).find('td').eq(1).find('.hours').text()!=""){
						cellData.tst_emp_id = empId;
						cellData.tst_prj_id = classes[0];
						cellData.tst_task_id = classes[1];
						cellData.tst_date = st_date;
						cellData.tst_hours = $(this).find('td').eq(1).find('.hours').text();
						cellData.tst_remark = $(this).find('td').eq(1).find('.remark').text();
						cellData.tst_file_handler = "FH03";
						colData.push(cellData);
					}
				}
			});
			colData = JSON.stringify(colData);
			if(Object.keys(JSON.parse(colData)).length > 0){
				$.ajax({
					url: 'http://hmsdev.dsssoft.com/index_ws01.php?action=352526&token=RRR&cred={"sub_id":"HUB190700007","usr_id":"u0001"}&data='+colData, 
					type: 'GET'
				});
				var date_ts = new Date(start_ts);
				
				date = date_ts.getFullYear()+'-'+((date_ts.getMonth()+1<10)?'0'+(date_ts.getMonth()+1):date_ts.getMonth()+1)+'-'+((date_ts.getDate()<10)?'0'+date_ts.getDate():date_ts.getDate());
				
				var projects = Object.keys(data);
				projects.forEach(function(project) {
					tasks = Object.keys(data[project]);
					tasks.forEach(function(task) {
						var dates = Object.keys(data[project][task]);
						
						if(dates.includes(date)){
							delete data[project][task][date];	
						}
					});
				});
			}
			

			start_ts.setDate(start_ts.getDate()+1);
			end_ts.setDate(end_ts.getDate()+1);
			st_date = start_ts.getFullYear()+'-'+((start_ts.getMonth()+1<10)?'0'+(start_ts.getMonth()+1):start_ts.getMonth()+1)+'-'+((start_ts.getDate()<10)?'0'+start_ts.getDate():start_ts.getDate());
			end_date = end_ts.getFullYear()+'-'+((end_ts.getMonth()+1<10)?'0'+(end_ts.getMonth()+1):end_ts.getMonth()+1)+'-'+((end_ts.getDate()<10)?'0'+end_ts.getDate():end_ts.getDate());
			
			$.ajax({
			  	url: 'http://hmsdev.dsssoft.com/index_ws01.php?action=352527&token=RRR&cred={"sub_id":"HUB190700007","usr_id":"u0002"}&data={"tst_emp_id":"EMP_001","tst_st_date":"'+end_date+'","tst_end_date":"'+end_date+'","tst_file_handler":"FH03"}', 
				type: 'GET',
				success: function(response){
					$("#timeline thead").find('tr').find('th').eq(16).after('<th class="rotate active-table-head"><div><span>'+end_date+'</span></div></th>');
					$("#timeline thead").find('tr').find('th').eq(2).remove();

					$("#timeline thead").find('tr').eq(1).find('th').eq(14).after('<th class="inactive-table-head">'+week[end_ts.getDay()]+'</th>')
					$("#timeline thead").find('tr').eq(1).find('th').eq(0).remove();
					
					

					if(jQuery.isEmptyObject(response['result'])){
						$('#timeline tbody').find('tr').each(function(){
							if($(this).find('td').eq(2).hasClass('inactive-cell')){
								$(this).find('td').eq(16).after("<td class='inactive-cell'></td>");
							}
							else{
								$(this).find('td').eq(15).after("<td class='active-cell'>"
																	+"<span contenteditable='true' class='hours'></span>"
																	+"<span class='hidden remark'></span>"
																+"</td>");
							}
							$(this).find('td').eq(1).remove();
						});
					}
					else{
								$("#timeline tbody").html('');
								data = mergeNewCol(data,response['result']);
								var projects = Object.keys(data);
								var table = $("#timeline tbody");
								projects.forEach(function(project) {
									var projectRow;
									var projectCells = "<td class='inactive-cell' colspan='2'>"+project+"</td>";
									for(var i=0;i<16;i++){
										projectCells += "<td class='inactive-cell'></td>";
									}
									projectRow = "<tr class="+project+">"+projectCells+"</tr>";
									table.append(projectRow);
									var tasks = Object.keys(data[project]);
									tasks.forEach(function(task) {
										var taskRow;
										var taskCells = "<td colspan='2'>"+task+"</td>";
										var tmp_date = new Date(start_ts);
										var dates = Object.keys(data[project][task]);

										for(i=0;i<15;i++){
											var ymd = tmp_date.getFullYear()+'-'+((tmp_date.getMonth()+1<10)?'0'+(tmp_date.getMonth()+1):tmp_date.getMonth()+1)+'-'+((tmp_date.getDate()<10)?'0'+tmp_date.getDate():tmp_date.getDate());
											
											if(dates.includes(ymd)){
											
												taskCells += "<td class='active-cell'>"
															+"<span class='hours' contenteditable>"+data[project][task][ymd]['tst_hours']+"</span>"
															+"<span class='hidden remark'>"+data[project][task][ymd]['tst_remark']+"</span>"
														+"</td>";	
											}
											else{
												taskCells += "<td class='active-cell'>"
															+"<span class='hours' contenteditable></span>"
															+"<span class='hidden remark'></span>"
														+"</td>";
											}
											tmp_date.setDate(tmp_date.getDate()+1)
										}
										taskCells += "<td></td>";
										taskRow = "<tr class='"+project+" "+task+"'>"+taskCells+"</tr>";
										table.append(taskRow);
									});
								});
							
					}
					
				}
			});
		});
	</script>

	<script>
		var cell;
		$(document).on('dblclick','.active-cell',function(){
			if($(this).find(".hours").text() != ''){
				$("#remark-value").val($(this).find(".remark").text());
				$("#remark-modal").modal('show');
				cell = $(this);
			}
		});
		$(document).on('click','#save-remark',function(event){
			event.preventDefault();
			var remark = $("#remark-value").val();
			cell.find('.remark').text(remark);
		});
	</script>

	<script>
		$(document).on('click','#save',function(){
			colData = [];
			$("#timeline tbody").find('tr').each(function(){
				var classes = $(this).attr("class").toString().split(' ');
				if(classes.length == 2){
					var date_ts = new Date(start_ts);
					$(this).find("td").each(function(index){
						var cellData = {};
						if(index!=0){
		
							date = date_ts.getFullYear()+'-'+((date_ts.getMonth()+1<10)?'0'+(date_ts.getMonth()+1):date_ts.getMonth()+1)+'-'+((date_ts.getDate()<10)?'0'+date_ts.getDate():date_ts.getDate());
							date_ts.setDate(date_ts.getDate()+1);
							if($(this).find('.hours').text()!=""){
								cellData.tst_emp_id = empId;
								cellData.tst_prj_id = classes[0];
								cellData.tst_task_id = classes[1];
								cellData.tst_date = date;
								cellData.tst_hours = $(this).find('.hours').text();
								cellData.tst_remark = $(this).find('.remark').text();
								cellData.tst_file_handler = "FH03";
								colData.push(cellData);
							}
						}
					});
				}
			});
			colData = JSON.stringify(colData);
			if(Object.keys(JSON.parse(colData)).length > 0){

				$.ajax({
					url: 'http://hmsdev.dsssoft.com/index_ws01.php?action=352526&token=RRR&cred={"sub_id":"HUB190700007","usr_id":"u0001"}&data='+colData, 
					type: 'GET'
				});
			}
		});
	</script>

	<script>
		$("#exit").click(function(){
			var conf=confirm("Are you sure, you want to close this tab?");
			if(conf==true){
				open(location, '_self');
				close();
			}
		});
	</script>

	<script>
		$(document).on('blur','.hours',function(){
			if($(this).text()==''){
				$(this).text('0.0');
			}
		});
	</script>
</body>
</html>