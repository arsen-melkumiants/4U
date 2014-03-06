<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_menu_model extends CI_Model
{
	var $menus = array();

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_menu_items($menu_name = false) {
		if ($menu_name) {
			$this->db->where('m.name', $menu_name);
		}
		return $this->db->select('m.name as menu_name, m.ru_name, i.*')
			->from('menu_names as m')
			->join('menu_items as i', 'm.id = i.menu_id', 'left')
			->order_by('order', 'asc')
			->get()
			->result_array();
	}

	function get_one_item_menu($item_id) {
		return $this->db->where('id', $item_id)->get('menu_items')->row_array();
	}

	function get_menu_tree($all_branch, $id = 0, $padding = '', $url = '') {
		$text = !$id ? '<div class="dd tree_struct">' : '';
		$text .= '<ol class="dd-list">';
		$num = 1;
		foreach ($all_branch as $key => $item) {
			if ($item['id'] && $item['parent_id'] == $id) {
				$text .= '<li class="dd-item dd3-item" data-id="'.$item['id'].'">
					<div class="dd-handle dd3-handle">Drag</div><div class="dd3-content">'.$item['name'].'</div>';
				$text .= $this->get_menu_tree($all_branch, $item['id'], $url);
				$text .= '</li>';
			/*	$text .= '<div class="row show-grid">
					<div class="span5"><span class="label label-info">'.($num++).'</span><span class="item_name"> '.$item['name'].'</span></div>
					<div class="span2 hover">
					<a href="'.$url.'/'.$item['id'].'/edit.html" title="Редактировать"><i class="icon-pencil"></i></a>
					<a data-toggle="modal" href="#delete_popup" data-name="'.$item['name'].'" data-href="'.$url.'/'.$item['id'].'/delete.html" class="del_event" title="Удалить"><i class="icon-trash"></i></a> 
					</div>
			</div>';*/


			}
		}
		$text .= '</ol>';
		if(!$id) {
			$text .= '</div>';
		}
		return $text;
	}

	function destruct_menu_tree($tree_array = false, $parent_id = 0) {
		$return_array = array();
		foreach ($tree_array as $order => $item) {
			$return_array[] = array(
				'id'        => $item['id'],
				'parent_id' => $parent_id,
				'order'     => $order,
			);
			if(isset($item['children']) && is_array($item['children'])) {
				$return_array = array_merge($return_array, $this->destruct_menu_tree($item['children'], $item['id']));
			}
		}
		return $return_array;
	}

	function update_menu_tree($new_branch_struct = false, $menu_name = false) {
		if (empty($new_branch_struct) || empty($menu_name)) {
			return false;
		}
		$new_struct = $this->destruct_menu_tree($new_branch_struct);
		$old_struct = $this->get_menu_items($menu_name);
		if (empty($old_struct)) {
			return false;
		}

		foreach ($old_struct as $old_item) {
			foreach ($new_struct as $new_item) {
				if ($old_item['id'] != $new_item['id']) {
					continue;
				}

				if($old_item['parent_id'] != $new_item['parent_id'] || $old_item['order'] != $new_item['order']) {
					$update_array[] = $new_item;
				}
			}
		}

		if (!empty($update_array)) {
			$this->db->update_batch('menu_items', $update_array, 'id');
		}
		return true;
	}

	function delete_menu_tree($all_branch, $id) {
		$ids = '';
		foreach ($all_branch as $key => $item) {
			if ($item['parent_id'] == $id) {
				$ids .= $item['id'].',';
				$ids .= $this->delete_menu_tree($all_branch, $item['id']);
			}
		}
		return $ids;
	}

	function delete_menu_item($id = false, $menu_name = false) {
		$all_branch = $this->get_menu_items($menu_name);
		if (empty($all_branch)) {
			return false;
		}
		$ids = $this->delete_menu_tree($all_branch, $id);
		$this->db->where_in('id',explode(',', $ids.$id))->delete('menu_items');
		return true;
	}

	function add_menu_item($info, $menu_id){
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

}
