<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_product_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_all_products($status = false) {
		if ($status !== false) {
			$this->db->where('status', $status);
		} else {
			$this->db->where('status <', 3);
		}
		return $this->db->order_by('id', 'desc')->get('shop_products');
	}

	function get_product_info($id) {
		return $this->db->where(array('id' => $id, 'status <' => 3))->get('shop_products')->row_array();
	}

	function get_product_categories() {
		$categories = $this->db->select('*, name_ru as name')->get('shop_categories')->result_array();
		if (empty($categories)) {
			return false;
		}
		foreach ($categories as $item) {
			$result[$item['id']] = $item;
		}

		return $result;
	}

	function get_currencies() {
		return $this->db->where('status', 1)->get('shop_currencies')->result_array();
	}

	function get_orders() {
		return $this->db
			->select('o.*, c.symbol, c.code, u.username')
			->from('shop_orders as o')
			->join('shop_currencies as c', 'o.currency = c.id')
			->join('users as u', 'o.user_id = u.id')
			->order_by('o.id', 'desc')
			->get();
	}

	function get_order_info($id) {
		return $this->db
			->select('op.*, c.symbol, c.code')
			->from('shop_order_products as op')
			->join('shop_currencies as c', 'op.currency = c.id')
			->where('op.order_id', $id)
			->order_by('op.id', 'desc')
			->get();
	}
}
