<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shop_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_categories($menu_name = false) {
		$all_branch = $this->db->get('shop_categories')->result_array();
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

}
