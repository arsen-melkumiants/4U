	</div>
	
	<script src="/dist/js/jquery-1.10.2.min.js"></script>
	<script src="/dist/js/bootstrap.min.js"></script>
	<script src="/dist/js/jquery.nestable.js"></script>
	<script src="/dist/js/moment.min.js"></script>
	<script src="/dist/js/bootstrap-datetimepicker.min.js"></script>
	<script src="/dist/js/bootstrap-datetimepicker.ru.js"></script>
	<link type="text/css" rel="stylesheet" href="/dist/css/bootstrap-datetimepicker.min.css" />
	<?php echo after_load('css');?>
	<?php echo after_load('js');?>

<script>
var update_tree_struct = function(e) {
	var tree = $('.tree_struct').nestable('serialize');
	$.post('<?php echo current_url();?>',{tree : tree});
};
$(function(){
	$('a').tooltip();

	if (typeof $().selectpicker === 'function') {
		$('.selectpicker').selectpicker();
	}	


	$('.tree_struct').nestable().on('change', update_tree_struct);
	$('.tree_btn_expand').on('click', function(e) {
		$('.dd').nestable('expandAll');
	});
	$('.tree_btn_collapse').on('click', function(e) {
		$('.dd').nestable('collapseAll');
	});

	$(document).on('click', '.modal-body form button[type="submit"]', function() {
		var form = $('.modal-dialog').find('form');
		var action = form.attr('action');
		//var fields = $(":input").serializeArray();
		var fields = $(this).closest('form').serializeArray();
		fields.push({ name: this.name, value: this.value });
		if (this.name == 'cancel'){
			$('#ajaxModal').modal('hide');
			return false;
		}
		$.post(action, fields, function(data) {
			data = $.trim(data);
			if(data == 'refresh') {
				window.location.reload(true);
			} else if(data == 'close') {
				$('#ajaxModal').modal('hide');
			} else {
				$('#ajaxModal .modal-content').html(data);
			}
		});
		return false;
	});
	$(document).bind('hidden.bs.modal', function () {
		$('#ajaxModal').removeData('bs.modal')
	});

	$(document).on('loaded.bs.modal', function (e) {
		var result = $.trim(e.target.innerText);
		if(result == 'refresh') {
			window.location.reload(true);
		} else if(result == 'close') {
			$('#ajaxModal').hide().modal('hide');
		}
		if (typeof $().selectpicker === 'function') {
			$('.selectpicker').selectpicker('render');
		}	
	});

});
</script>
	<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			  <div class="modal-content"></div>
		</div>
	</div>
	</body>
</html>
