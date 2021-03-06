<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_product_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_all_products() {
		return $this->db->get('shop_products');
	}

	function get_product_info($id) {
		return $this->db->where('id', $id)->get('shop_products')->row_array();
	}

	function get_product_categories() {
		$categories = $this->db->get('shop_categories')->result_array();
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
