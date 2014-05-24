<script>
var update_commission = function() {
	$(document).on('keyup', '.withdraw_amount', function() {
		var percent = <?php echo WITHDRAWAL_COMMISSION?>;
		var amount = Number($(this).val());
		if (typeof amount != 'number') {
			return false;
		}
		var commission = Math.round(amount * percent) / 100;
		$('.withdraw_total').find('.commis_value').text(commission);
		$('.withdraw_total').find('input').val(amount + commission);
	});
}

<?php echo ($this->IS_AJAX) ? 'update_commission();' : 'window.onload = function() {update_commission();}';?>
</script>
