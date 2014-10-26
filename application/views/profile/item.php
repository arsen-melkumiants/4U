<a href="<?php echo product_url($id, $name)?>">
	<div class="image"<?php echo empty($file_name) ? ' style="min-height: 90px;"' : ''?>>
		<?php echo !empty($file_name) ? '<img src="/uploads/gallery/'.$folder.'small_thumb/'.$file_name.'" />' : '';?>
	</div>
</a>
<div class="info">
	<a class="name" href="<?php echo product_url($id, $name)?>"><?php echo $name ?></a>
	<span><?php echo !empty($add_date) ? date('d.m.Y', $add_date) : ''?></span>
	<div><?php echo !empty($sold_qty) ? lang('sold_products_amount').': '.$sold_qty : ''?></div>

	<?php if (!empty($commission) && empty($no_show_commission)) {?>
	<div><?php echo lang('commission').': '.$commission.' $'?></div>
	<?php }?>
	<div><?php echo !empty($facilities) ? $facilities : ''?></div>

	<?php echo !empty($is_vip) ? '<div><i class="c_icon_star"></i></div>' : '' ?>
	<?php if (!empty($files_list)) {?>
	<div class="files_list">
		<?php foreach ($files_list as $item) {
		$ext = strtolower(end(explode('.', $item['file_name'])));
		$new = $last_update_date - 86400 < $item['add_date'] && $add_date + 86400 < $item['add_date'] ? '<span class="new">'.lang('new').'</span>' : '';
		?>
		<li><i><span><?php echo $ext?></span></i><a href="<?php echo base_url('media_files/'.$item['id'])?>"><?php echo $item['file_name']?></a><?php echo $new?></li>
		<?php }?>
		<?php $archive_file = glob(FCPATH.'media_files/'.$id.'/'.$id.'-*.zip');
		if (!empty($archive_file[0])) {?>
		<div class="archive_link"><i><span>zip</span></i><a href="<?php echo base_url('media_files/'.$id.'/archive')?>"><?php echo lang('file_in_arc')?></a></div>
		<?php }?>
	</div>
	<?php }?>
</div>
