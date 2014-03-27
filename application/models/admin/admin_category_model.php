<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_category_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_category_items() {
		return $this->db->get('shop_categories')->result_array();
	}

	function get_category_info($item_id) {
		return $this->db->where('id', $item_id)->get('shop_categories')->row_array();
	}

	function get_category_tree($all_branch, $id = 0, $url = '') {
		$text = !$id ? '
			<a data-toggle="modal" data-target="#ajaxModal" href="'.site_url($url.'/add').'" class="btn btn-primary"> Добавить</a>
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
					<a data-toggle="modal" data-target="#ajaxModal" href="'.site_url($url.'delete/'.$item['id']).'" title="Удалить"><i class="icon-trash"></i></a>
					<a data-toggle="modal" data-target="#ajaxModal" href="'.site_url($url.'edit/'.$item['id']).'" title="Редактировать"><i class="icon-pencil"></i></a>
					</div>';
				$text .= $this->get_category_tree($all_branch, $item['id'], $url);
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

	function destruct_category_tree($tree_array = false, $parent_id = 0) {
		$return_array = array();
		foreach ($tree_array as $order => $item) {
			$return_array[] = array(
				'id'        => $item['id'],
				'parent_id' => $parent_id,
				'order'     => $order,
			);
			if(isset($item['children']) && is_array($item['children'])) {
				$return_array = array_merge($return_array, $this->destruct_category_tree($item['children'], $item['id']));
			}
		}
		return $return_array;
	}

	function update_category_tree($new_branch_struct = false) {
		if (empty($new_branch_struct)) {
			return false;
		}
		$new_struct = $this->destruct_category_tree($new_branch_struct);
		$old_struct = $this->get_category_items();
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
			$this->db->update_batch('shop_categories', $update_array, 'id');
		}
		return true;
	}

	function delete_category_tree($all_branch, $id) {
		$ids = '';
		foreach ($all_branch as $key => $item) {
			if ($item['parent_id'] == $id) {
				$ids .= $item['id'].',';
				$ids .= $this->delete_category_tree($all_branch, $item['id']);
			}
		}
		return $ids;
	}

	function delete_category($id = false) {
		$all_branch = $this->get_category_items();
		if (empty($all_branch)) {
			return false;
		}
		$ids = $this->delete_category_tree($all_branch, $id);
		$this->db->where_in('id',explode(',', $ids.$id))->delete('shop_categories');
		return true;
	}

}
