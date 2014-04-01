<div class="container main_block">
	<div class="row">
		<?php echo get_alerts();?>
	</div>
	<?php if(!empty($header)){?>
	<div class="header_block">
		<h1><?php echo $header?></h1>
		<?php if(!empty($header_descr)){?>
			<h5><?php echo $header_descr?></h5>
		<?php }?>
	</div>
	<?php }?>
	<div class="row">
	<?php if(!empty($left_block)){?>
		<div class="col-md-3 left_block">
			<div class="search_block">
				<form>
					<input type="text" name="q" placeholder="What looking for?"/>
					<input type="submit" value=""/>
				</form>
			</div>
			<?php echo $left_block;?>
		</div>
		<div class="col-md-9"><?php echo !empty($center_block) ? '<div class="center_block"><hr />'.$center_block.'</div>' : '';?></div>
	<?php }elseif(!empty($center_block)){?>
		<div class="col-md-12"><div class="center_block"><hr /><?php echo $center_block;?></div></div>
	<?php }?>
	</div>
</div>
