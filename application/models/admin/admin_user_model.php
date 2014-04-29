<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_user_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_all_users($active = false) {
		if ($active !== false) {
			$this->db->where('active', $active);
		}
		return $this->db->select('*, active as status')->get('users');
	}

	function get_user_info($id) {
		return $this->db->where('id', $id)->get('users')->row_array();
	}

}
