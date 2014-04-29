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
}
