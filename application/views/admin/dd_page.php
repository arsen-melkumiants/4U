<?php 
if (!empty($dd_list)) {?>
<div class="row">
	<div class="col-sm-5">
		<div class="btn-group">
			<?php foreach ($types as $key => $item) {?>
			<a href="<?php echo site_url($this->MAIN_URL.($key == 'all' ? '' : $key))?>" class="btn btn-primary<?php echo $key == $period || ($key == 'period' && is_array($period))? ' active' : ''?>"><?php echo $item?></a>
			<?php }?>
		</div>
	</div>
	<div class="col-sm-5"<?php if(!is_array($period)){?>style="display: none;"<?php }?>>
		<form action="<?php echo site_url($this->MAIN_URL.'period')?>" class="form-horizontal date_range" method="get">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-sm-2 control-label">От:</label>
						<div class="col-sm-10">
							<div class="input-group date" >
								<input type="text" class="form-control" id="date_from" name="from" value="<?php echo !empty($_GET['from']) ? $_GET['from'] : ''?>"/>
								<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-sm-2 control-label">До:</label>
						<div class="col-sm-10">
							<div class="input-group date" >
								<input type="text" class="form-control" id="date_to" name="to" value="<?php echo !empty($_GET['to']) ? $_GET['to'] : ''?>"/>
								<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
	window.onload = function() {

		$('#date_from').datetimepicker({language: 'ru', pickTime: false});
		$('#date_to').datetimepicker({language: 'ru', pickTime: false, showToday: true});
		$('#date_from').on('dp.change',function (e) {
			$('#date_to').data('DateTimePicker').setMinDate(e.date);
			if ($('#date_to').val().length > 0) {
			console.log('submit');
				$('.date_range').submit();
			}
		});
		$('#date_to').on('dp.change',function (e) {
			$('#date_from').data('DateTimePicker').setMaxDate(e.date);
			if ($('#date_from').val().length > 0) {
			console.log('submit');
				$('.date_range').submit();
			}
		});
	};
</script>
<br />
<br />
<dl class="dl-horizontal">
	<?php foreach ($dd_list as $name => $value) {?>
	<dt><?php echo $name?>:</dt>
	<dd><?php echo $value?></dd>
	<?php }?>
</dl>
<?php }?>
