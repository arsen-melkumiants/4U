<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shop_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_category_items() {
		return $this->db->where('status', 1)->get('shop_categories')->result_array();
	}

	function get_categories() {
		$all_branch = $this->db->where('status', 1)->order_by('order', 'asc')->get('shop_categories')->result_array();
		return $this->get_category_tree($all_branch, 0);
	}

	function get_category_tree($all_branch, $id = 0, $url = 'category/') {
		$text = '<ul>';
		$num = 0;
		foreach ($all_branch as $key => $item) {
			if ($item['id'] && $item['parent_id'] == $id) {
				$icon = !empty($item['custom']) ? '<i class="'.$item['custom'].'"></i>' : '';
				$text .= '<li>';
				$text .= '<a href="'.site_url($url.$item['alias']).'">'.$icon.$item['name'].'</a>';
				$text .= $this->get_category_tree($all_branch, $item['id'], $url);
				$text .= '</li>';
				$num++;
			}
		}
		$text .= '</ul>';
		return $num ? $text : ($id ? false : $text);
	}

	function get_category_info($name) {
		return $this->db
			->where(array(
				'alias'  => $name,
				'status' => 1,
			))
			->get('shop_categories')
			->row_array();
	}

	function parent_categories($id = false) {
		$all_branch = $this->get_category_items();
		if (empty($all_branch)) {
			return false;
		}
		$ids = $this->get_category_recurcive($all_branch, $id);
		return $ids;
	}

	function get_category_recurcive($all_branch, $id) {
		$ids = '';
		foreach ($all_branch as $key => $item) {
			if ($item['id'] == $id) {
				$ids[] = $item;
				$result = $this->get_category_recurcive($all_branch, $item['parent_id']);
				if(!empty($result)) {
					$ids = array_merge($ids, $result);
				}
			}
		}
		return $ids;
	}

	function get_products_by_category($id) {
		return $this->db
			->select('p.*, c.symbol, c.code')
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->where(array(
				'p.cat_id' => $id,
				'p.status' => 1,
			))
			->get()
			->result_array();
	}

	function get_product_info($id) {
		return $this->db
			->select('p.*, c.symbol, c.code, u.username, u.phone')
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('users as u', 'p.author = u.id')
			->where(array(
				'p.id' => $id,
				'p.status' => 1,
			))
			->get()
			->row_array();
	}
}
