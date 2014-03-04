	</div>
	
	<script src="/dist/js/jquery-1.10.2.min.js"></script>
	<script src="/dist/js/bootstrap.min.js"></script>
	<script src="/dist/js/bootstrap-datetimepicker.min.js"></script>
	<script src="/dist/js/bootstrap-datetimepicker.ru.js"></script>
	<link type="text/css" rel="stylesheet" href="/dist/css/datetimepicker.css" />
	
	
	
	<script src='/dist/calendar/jquery-ui.custom.min.js'></script>
	<script src='/dist/calendar/fullcalendar.min.js'></script>
	<script src='/dist/calendar/calendar.js'></script>


	<script type="text/javascript">
		$(".date_time").datetimepicker({format: 'yyyy-mm-dd hh:ii', language: 'ru', todayBtn: true});
		$(document).on('focus', '.date_time', function(){
			$(this).datetimepicker({format: 'yyyy-mm-dd hh:ii', language: 'ru', todayBtn: true});
		});
	</script>      
	
	<script>
		$(function(){
			$('a').tooltip();
		});
	</script>
	
	
	
	
	</body>
	<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
</html>