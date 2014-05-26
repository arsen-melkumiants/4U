<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Menu_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_menu_name($id) {
		$data = $this->db->where('id', $id)->get('menu_names')->row_array();
		return $data['name'];
	}

	function get_menu_info($name) {
		return $this->db->where('name', $name)->get('menu_names')->row_array();
	}

	function get_menu($menu_name = false) {
		if ($menu_name) {
			$this->db->where('m.name', $menu_name);
		}
		$all_branch = $this->db->select('m.name as menu_name, m.ru_name, i.*')
			->from('menu_names as m')
			->join('menu_items as i', 'm.id = i.menu_id', 'left')
			->where('i.status', 1)
			->order_by('order', 'asc')
			->get()
			->result_array();
		return $this->get_menu_tree($all_branch, 0);
	}

	function get_one_menu_item($item_id) {
		return $this->db->select('m.name as menu_name, m.ru_name, i.*')
			->from('menu_items as i')
			->join('menu_names as m', 'm.id = i.menu_id', 'left')
			->where('i.id', $item_id)
			->get()
			->row_array();
	}

	function get_menu_tree($all_branch, $id = 0, $url = '') {
		$text = '<ul>';
		$num = 0;
		foreach ($all_branch as $key => $item) {
			if ($item['id'] && $item['parent_id'] == $id) {
				$icon = !empty($item['custom']) ? '<i class="'.$item['custom'].'"></i>' : '';
				$modal = !empty($item['modal']) ? ' data-toggle="modal" data-target="#ajaxModal"' : '';
				if($item['type'] == 'auth') {
					if (is_object($this->ion_auth) && $this->ion_auth->logged_in()) { 
						if(in_array($item['item_id'], array('login', 'registration'))) {
							continue;
						}
					} else {
						if (!in_array($item['item_id'], array('login', 'registration'))) {
							continue;
						}
					}

					if ($item['item_id'] == 'profile') {
						$link = $item['item_id'];
					} elseif ($item['item_id'] == 'balance') {
						if (!is_object($this->ion_auth) || !$this->ion_auth->logged_in()) { 
							return false;
						}
						$link = 'profile/finance';
						$user_info = $this->ion_auth->user()->row_array();
						$this->load->model('shop_model');
						$balance = $this->shop_model->get_user_balance($user_info['id']);
						$item['name_'.$this->config->item('lang_abbr')] = $item['name_'.$this->config->item('lang_abbr')].': '.floatval($balance[0]['amount']).' '.$balance[0]['symbol'];
					} else {
						$link = 'personal/'.$item['item_id'];
					}
				} else {
					$link = $url.$item['alias'];
				}

				//For seller
				if (is_object($this->ion_auth) && $this->ion_auth->logged_in()) {
					if ($item['item_id'] == 'cart' && $this->ion_auth->user()->row()->is_seller) {
						continue;
					}
				}

				if ($item['type'] != 'external' || $link != '#') {
					if ($link == '/' && $this->config->item('lang_abbr') != 'en') {
						$link = site_url();
					} elseif ($link == '/' && $this->config->item('lang_abbr') == 'en') {
						$link = base_url();
					} else {
						$link = site_url($link);
					}
				}


				$text .= '<li>';
				$text .= '<a'.$modal.' href="'.$link.'">'.$icon.$item['name_'.$this->config->item('lang_abbr')].'</a>';
				$text .= $this->get_menu_tree($all_branch, $item['id'], $url);
				$text .= '</li>';
				$num++;
			}
		}
		$text .= '</ul>';
		return $num ? $text : ($id ? false : $text);
	}

}
