<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Special_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_spec_content($var = false) {
		if (empty($var)) {
			return false;
		}

		$info = $this->db->select('content_'.$this->config->item('lang_abbr').' as content')->where('var', $var)->get('special_content')->row_array();
		if (empty($info)) {
			return false;
		}
		return $info['content'];
	}
}
