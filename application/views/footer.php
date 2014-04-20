		<script src="/dist/js/jquery-1.10.2.min.js"></script>
		<script src="/dist/js/bootstrap.min.js"></script>
		<script src="/js/notify/pnotify.custom.min.js"></script>
		<link href="/js/notify/pnotify.custom.min.css" media="all" rel="stylesheet" type="text/css" />
		<?php echo after_load('css');?>
		<?php echo after_load('js');?>
		<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
		<!--[if (gte IE 8)&(lt IE 10)]>
		<script src="/js/upload/cors/jquery.xdr-transport.js"></script>
		<![endif]-->

		<script>
		$(function(){
			$('a').tooltip();

			if (typeof $().selectpicker === 'function') {
				$('.selectpicker').selectpicker();
			}	

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

			//SHOPPING CART METHODS
			$('.delete_from_cart').on('click', function() {
				var row = $(this).parent().parent();
				var id = $(this).data('id');
				if (typeof id === 'undefined') {
					return false;
				}
				$.post('/update_cart/', {id : id, count : 0}).done(function(data) {
					if ($.trim(data) == 'OK') {
						row.fadeOut();
					}
				});
			});

			$('.add_to_cart').on('click', function() {
				var id = $(this).data('id');
				if (typeof id === 'undefined') {
					return false;
				}
				var name = $(this).data('name');
				var link = $(this).data('href');
				if (typeof name !== 'undefined') {
					if (typeof link !== 'undefined') {
						name = ' "<a href="' + link + '">' + name + '</a>"';
					} else {
						name = ' "' + name + '"';
					}
				}

				$.post('/add_to_cart/', {id : id}).done(function(data) {
					//data;
				});
				new PNotify({
					title : 'Товар добавлен в корзину',
					text  : 'Товар' + name + ' успешно добавлен в <a href="<?php echo site_url('cart')?>">корзину</a>',
					icon  : 'icon-shopping-cart',
					type  : 'success',
					delay : 3000,
				});
			});
		});
		</script>

		<div class="footer_block">
			<div class="container">
				<div class="row">
					<div class="col-md-3">
						<div class="copyright">Copyright © 2014</div>
					</div>
					<div class="col-md-9">
						<div class="menu">
							<?php echo !empty($main_menu) ? $main_menu : '';?>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	
		<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				  <div class="modal-content"></div>
			</div>
		</div>
	</body>
</html>
