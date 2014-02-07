<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_menu_model extends CI_Model
{
	var $menus = array();
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function get_menu_info($name = false){
		if($name){
			return $this->db->where('name',$name)->get('menu_names');
		}else{
			return false;
		}
	}
	
	function get_all_menu($id){
		return $this->db->where('parent_menu', $id)->order_by('order','asc')->get('menu_items');
	}
	
	function get_one_item_menu($menu_id, $item_id){
		return $this->db->where(array('parent_menu' => $menu_id, 'id' => $item_id))->get('menu_items');
	}
	
	function get_menu_tree($all_branch, $id = 0, $padding = '', $url = ''){
		$text='';
		$num = 1;
			for($i=0; $i<count($all_branch); $i++){
				if ($all_branch[$i]->parent_id == $id){
					$text.='<div class="row show-grid" style="padding-left:'.$padding.'px;">
        			<div class="span5"><span class="label label-info">'.($num++).'</span><span class="item_name"> '.$all_branch[$i]->name.'</span></div>
					<div class="span2 hover">
						<a href="'.$url.'/'.$all_branch[$i]->id.'/edit.html" title="Редактировать"><i class="icon-pencil"></i></a>
						<a data-toggle="modal" href="#delete_popup" data-name="'.$all_branch[$i]->name.'" data-href="'.$url.'/'.$all_branch[$i]->id.'/delete.html" class="del_event" title="Удалить"><i class="icon-trash"></i></a> 
						<a href="'.$url.'/'.$all_branch[$i]->id.'-'.$all_branch[$i]->parent_id.'-'.$all_branch[$i]->order.'/move_up.html" title="Вверх"><i class="icon-arrow-up"></i></a> 
						<a href="'.$url.'/'.$all_branch[$i]->id.'-'.$all_branch[$i]->parent_id.'-'.$all_branch[$i]->order.'/move_down.html" title="Вниз"><i class="icon-arrow-down"></i></a>
					</div>
					</div>';
					$text.=$this->get_menu_tree($all_branch, $all_branch[$i]->id, $padding + 20, $url);
				}
			 }
		return $text;
	}
	
	function delete_menu_tree($all_branch, $id){
		$ids = '';
		for($i=0; $i<count($all_branch); $i++){
			if ($all_branch[$i]->parent_id == $id){
				$ids .= $all_branch[$i]->id.',';
				$ids .= $this->delete_menu_tree($all_branch, $all_branch[$i]->id);
			}
		}
		return $ids;
	}
	
	function add_menu_item($info,$menu_id){
		$order = $this->db->where(array('parent_id' => $info['parent_id'], 'parent_menu' => $menu_id))->order_by('order','desc')->get('menu_items')->row();
		$info['order'] = !empty($order) ? $order->order+1 : 1;
		$this->db->insert('menu_items', $info); 
	}
	
	function update_menu_item($new_info, $old_info, $menu_id){
		if($new_info['parent_id'] == $old_info->parent_id){
			$this->db->where('id',$old_info->id)->update('menu_items', $new_info);
		}else{
			$data = $this->db->where(array('parent_id' => $new_info['parent_id'], 'parent_menu' => $menu_id))->order_by('order','desc')->get('menu_items')->row();
			if(empty($data)){
				$new_info['order'] = 1;
				$this->db->where('id',$old_info->id)->update('menu_items', $new_info);
			}else{
				$new_info['order'] = $data->order + 1;
				$this->db->where('id',$old_info->id)->update('menu_items', $new_info);
			}
		}
	}
	
	function delete_menu_item($all_branch, $id){
		$ids = $this->delete_menu_tree($all_branch, $id);
		$this->db->where_in('id',explode(',', $ids.$id))->delete('menu_items');
	}
	
	function move_up_menu_item($info, $menu_id){
		list($id, $parent_id, $order) = explode('-',$info);		
		$data = $this->db->where(array('parent_id' => $parent_id, 'order <' => $order, 'parent_menu' => $menu_id))->order_by('order','desc')->get('menu_items')->row();
		if(empty($data)){
			return false;
		}else{
			$this->db->where('id',$data->id)->update('menu_items',array('order' => $data->order + 1));
			$this->db->where('id',$id)->update('menu_items',array('order' => $data->order));
		}
		
	}
	
	function move_down_menu_item($info, $menu_id){
		list($id, $parent_id, $order) = explode('-',$info);		
		$data = $this->db->where(array('parent_id' => $parent_id, 'order >' => $order, 'parent_menu' => $menu_id))->order_by('order','asc')->get('menu_items')->row();
		if(empty($data)){
			return false;
		}else{
			$this->db->where('id',$data->id)->update('menu_items',array('order' => $data->order - 1));
			$this->db->where('id',$id)->update('menu_items',array('order' => $data->order));
		}	
	}
	
}
