<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_product_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_all_products($status = false) {
		if ($status !== false) {
			if ($status == 0) {
				$this->db->join('shop_product_media_files as mf', 'mf.product_id = p.id');
			}
			$this->db->where('p.status', $status);
		} else {
			$this->db->where('p.status <', 3);
		}
		return $this->db
			->select('p.*, u.username')
			->from('shop_products as p')
			->join('users as u', 'p.author_id = u.id')
			->order_by('id', 'desc')
			->get();
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

	function set_period_statistic($period = 'all') {
		if ($period == 'daily') {
			$day = strtotime('today UTC');	
			$this->db->where('o.paid_date >', $day);
		} elseif (is_array($period)) {
			$this->db->where('o.paid_date >', $period['from']);
			$this->db->where('o.paid_date <', $period['to']);
		}
	}

	function get_paid_product_amount($period = 'all') {
		$this->set_period_statistic($period);
		$result = $this->db
			->select('SUM(op.qty) as qty')
			->from('shop_order_products as op')
			->join('shop_orders as o', 'op.order_id = o.id')
			->where('o.status = 1')
			->get()
			->row_array();
		return $result['qty'] ?: 0;
	}

	function get_total_income_amount($period = 'all') {
		$this->set_period_statistic($period);
		$result = $this->db
			->select('op.*')
			->from('shop_order_products as op')
			->join('shop_orders as o', 'op.order_id = o.id')
			->where('o.status = 1')
			->get()
			->result_array();
		if (empty($result)) {
			return 0;
		}

		$total = 0;
		foreach ($result as $item) {
			$total += $this->shop_model->product_commission($item) * $item['qty']; 
		}
		return ($total ?: 0).' $';
	}

	function get_sellers_income_amount($period = 'all') {
		$this->set_period_statistic($period);
		$result = $this->db
			->select('op.*')
			->from('shop_order_products as op')
			->join('shop_orders as o', 'op.order_id = o.id')
			->where('o.status = 1')
			->get()
			->result_array();
		if (empty($result)) {
			return 0;
		}

		$total = 0;
		foreach ($result as $item) {
			$total += ($item['price'] - $this->shop_model->product_commission($item)) * $item['qty']; 
		}
		return ($total ?: 0).' $';
	}
}
