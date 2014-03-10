<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_menu_model extends CI_Model {
	var $menus = array();

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_menu_name($id) {
		$data = $this->db->where('id', $id)->get('menu_names')->row_array();
		return $data['name'];
	}

	function get_menu_info($name) {
		return $this->db->where('name', $name)->get('menu_names')->row_array();
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

	function get_one_menu_item($item_id) {
		return $this->db->where('id', $item_id)->get('menu_items')->row_array();
	}

	function get_menu_tree($all_branch, $id = 0, $url = '', $menu_name = false) {
		$text = !$id ? '
			<a data-toggle="modal" data-target="#ajaxModal" href="'.site_url($url.$menu_name.'/add').'" class="btn btn-primary"> Добавить</a>
			<button class="tree_btn_collapse btn btn-info" type="button"><i class="icon-minus"></i></button>
			<button class="tree_btn_expand btn btn-info" type="button"><i class="icon-plus"></i></button>
			<br />
			<div class="dd tree_struct">' : '';
		$text .= '<ol class="dd-list">';
		$num = 0;
		foreach ($all_branch as $key => $item) {
			if ($item['id'] && $item['parent_id'] == $id) {
				$text .= '<li class="dd-item dd3-item" data-id="'.$item['id'].'">
					<div class="dd-handle dd3-handle">Drag</div><div class="dd3-content">'.$item['name'].'
					<a data-toggle="modal" data-target="#ajaxModal" href="'.site_url($url.$item['id'].'/delete').'" title="Удалить"><i class="icon-trash"></i></a>
					<a data-toggle="modal" data-target="#ajaxModal" href="'.site_url($url.$item['id'].'/edit').'" title="Редактировать"><i class="icon-pencil"></i></a>
					</div>';
				$text .= $this->get_menu_tree($all_branch, $item['id'], $url, $menu_name);
				$text .= '</li>';
				$num++;
			}
		}
		$text .= '</ol>';
		if(!$id) {
			$text .= '</div>';
		}
		return $num ? $text : ($id ? false : $text);
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

	function add_menu_item($info){
		$this->db->insert('menu_items', $info); 
	}

	function update_menu_item($info, $id){
		$this->db->where('id', $id)->update('menu_items', $info);
	}

}
