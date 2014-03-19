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

	function add_product($info) {
		$this->db->insert('shop_products', $info);
	}

	function update_product($info, $id) {
		$this->db->where('id', $id)->update('shop_products', $info);
	}

	function delete_product($id) {
		$this->db->where('id', $id)->delete('shop_products');
	}

	function get_product_categories() {
		return $this->db->get('shop_categories')->result_array();
	}
}
