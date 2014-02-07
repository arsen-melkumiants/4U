<div class="modal-dialog">
  <div class="modal-content">
	<div class="modal-header">
	  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	  <?php echo !empty($this->data['header']) ? '<h4 class="modal-title">'.$this->data['header'].'</h4>' : '';?>
	</div>
	<div class="modal-body">
		<?php echo get_alerts();?>
		<?php echo $this->data['center_block']?>
	</div>
	<script>
		$(function(){
			$(document).on('click', '.modal-body form button', function(){
				var form = $('.modal-dialog').find('form');
				var action = form.attr('action');
				//var fields = $(":input").serializeArray();
				var fields = $(this).closest('form').serializeArray();
				fields.push({ name: this.name, value: this.value });
				if(this.name == 'cancel'){
					return false;
				}
				$.post(action, fields, function(data){
					data = $.trim(data);
					if(data == 'refresh'){
						window.location.reload(true);
					}else if(data == 'close'){
						$('#ajaxModal').modal('hide');
					}else{
						$('#ajaxModal').html(data);
					}
				});
				return false;
			});
			$(document).bind('hidden.bs.modal', function () {
				$('#ajaxModal').removeData('bs.modal')
			});
		});
	</script>  
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->