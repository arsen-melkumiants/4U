	</div>
	
	<script src="/dist/js/jquery-1.10.2.min.js"></script>
	<script src="/dist/js/bootstrap.min.js"></script>
	<script src="/dist/js/bootstrap-datetimepicker.min.js"></script>
	<script src="/dist/js/bootstrap-datetimepicker.ru.js"></script>
	<script src="/dist/js/jquery.nestable.js"></script>
	<link type="text/css" rel="stylesheet" href="/dist/css/datetimepicker.css" />
	
<script>
var update_tree_struct = function(e) {
	var tree = $('.tree_struct').nestable('serialize');
	$.post('<?php echo current_url();?>',{tree : tree});
};
$(function(){
	$('a').tooltip();
	$('.tree_struct').nestable().on('change', update_tree_struct);
});
</script>
	
	<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
	</body>
</html>
