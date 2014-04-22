<div class="custom_block">
	<div class="title">
		<h2><?php echo $name?></h2>
		<div class="steps_block">
			<ul>
				<?php if (!empty($links)) {
				$class = ' class="active"';
				foreach ($links as $link => $item) {?>
				<li<?php echo $class?>><a href="<?php echo site_url('cart/'.$link)?>"><span><?php echo $item?></span></a></li>
				<?php if ($link == $cur_step) {$class = '';}}}?>
			</ul>
		</div>
		<div class="clear"></div>
	</div>
	<div class="cart product_list">
		<?php echo !empty($table) ? $table : '<h4>There isn\'t any product yet. We are sure you will find everything</h4>'?>
	</div>
	<div class="row">
		<div class="col-sm-6">
		<a href="<?php echo site_url('cart/information')?>" class="orange_btn">Next step</a>
		</div>
		<div class="col-sm-6">
			<div class="price_total">Total price <span><i class="c_icon_label"></i> <span><?php echo $this->cart->total();?></span> $</span></div>
		</div>
	</div>
</div>
<script>
	var over_price = 0;

	function calc_price(){
		var over_price = 0;
		$('.cart table .count input').each(function(){
			over_price = over_price + ($(this).data('price') * $(this).val());
		});
		over_price = Math.round(over_price * 100) / 100;
		$('.price_total span span').text(over_price);
	}

	function update_product(count,id){
		$.post('/update_cart', {id : id,count : count})
		.done(function(data) {
			if($.trim(data) != 'OK') {
				return false;
			}
			calc_price();
		})
	}

	function delete_product(id, dom){
		$.post('/update_cart',{id : id, count : 0})
		.done(function(data) {
			if($.trim(data) != 'OK') {
				return false;
			}
			dom.fadeOut('fast', function() {
				dom.remove();
				calc_price();
			});
		});
	}

	window.onload = function() {
		$('.delete').click(function(){
			var dom = $(this).parent().parent();
			var id = $(this).data('id');
			delete_product(id, dom);
		})
		$('.plus').click(function(){
			var count = new Number($(this).prev().val());
			var id = $(this).prev().data('id');
			count = count + 1;
			$(this).prev().val(count);
			$(this).prev().prev().removeClass('none');
			update_product(count,id);
		});
		$('.minus').click(function(){
			var count = new Number($(this).next().val())
			var id = $(this).next().data('id');
			if(count > 1){
				if(count == 2){
					$(this).addClass('none');
				}
				count = count - 1;
				$(this).next().val(count);
				update_product(count,id);
			}
		});
		$('.count input').change(function(){
			var count = Number($(this).val());
			var id = $(this).data('id');
			if(count < 1 || isNaN(count)){
				count = 1;
				$(this).val(count);
			}
			update_product(count,id);
		});
	};
</script>
