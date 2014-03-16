<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_content_model extends CI_Model {
	var $menus = array();

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_all_content() {
		return $this->db->get('content');
	}

	function get_content_categories() {
		$query = $this->db->get('content_categories');
		foreach ($query->result_array() as $row) {
			$result[$row['id']] = $row;
		}
		return $result;
	}

	function get_content_info($id) {
		return $this->db->where('id', $id)->get('content')->row_array();
	}

	function add_content($info) {
		$this->db->insert('content', $info); 
	}

	function update_content($info, $id) {
		$this->db->where('id', $id)->update('content', $info);
	}

}
