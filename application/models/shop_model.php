<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shop_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_category_items() {
		return $this->db->where('status', 1)->order_by('order', 'asc')->get('shop_categories')->result_array();
	}

	function get_product_categories() {
		$categories = $this->get_category_items();
		if (empty($categories)) {
			return false;
		}
		foreach ($categories as $item) {
			$result[$item['id']] = $item;
		}

		return $result;
	}

	function get_categories() {
		$all_branch = $this->get_category_items();
		return $this->get_category_tree($all_branch, 0);
	}

	function get_category_tree($all_branch, $id = 0, $url = 'category/') {
		$text = '<ul>';
		$num = 0;
		foreach ($all_branch as $key => $item) {
			if ($item['id'] && $item['parent_id'] == $id) {
				$icon = !empty($item['custom']) ? '<i class="'.$item['custom'].'"></i>' : '';
				$text .= '<li>';
				$sub  = $this->get_category_tree($all_branch, $item['id'], $url);
				$text .= '<a '.($sub ? 'class="drop"' : '').' href="'.site_url($url.$item['alias']).'">'.$icon.$item['name'].'</a>';
				$text .= $sub;
				$text .= '</li>';
				$num++;
			}
		}
		$text .= '</ul>';
		return $num ? $text : ($id ? false : $text);
	}

	function get_category_info($name) {
		return $this->db
			->where(array(
				'alias'  => $name,
				'status' => 1,
			))
			->get('shop_categories')
			->row_array();
	}

	function parent_categories($id = false) {
		$all_branch = $this->get_category_items();
		if (empty($all_branch)) {
			return false;
		}
		$ids = $this->get_parent_category_recurcive($all_branch, $id);
		return $ids;
	}

	function get_parent_category_recurcive($all_branch, $id) {
		$ids = array();
		foreach ($all_branch as $key => $item) {
			if ($item['id'] == $id) {
				$ids[$item['id']] = $item;
				$result = $this->get_parent_category_recurcive($all_branch, $item['parent_id']);
				if(!empty($result)) {
					$ids = array_merge($ids, $result);
				}
			}
		}
		return $ids;
	}
	
	function get_child_category_recurcive($all_branch, $id) {
		$ids = array();
		foreach ($all_branch as $key => $item) {
			if ($item['id'] == $id) {
				$ids[$item['id']] = $item;
			} elseif ($item['parent_id'] == $id) {
				$ids[$item['id']] = $item;
				$result = $this->get_child_category_recurcive($all_branch, $item['id']);
				if(!empty($result)) {
					$ids = array_merge($ids, $result);
				}
			}
		}
		return $ids;
	}

	function get_products_by_category($id) {
		$all_branch = $this->get_category_items();
		$ids = $this->get_child_category_recurcive($all_branch, $id);
		return $this->db
			->select('p.*, c.symbol, c.code')
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->where_in('p.cat_id', array_keys($ids))
			->where('p.status', 1)
			->get()
			->result_array();
	}

	function get_product_info($id) {
		return $this->db
			->select('p.*, c.symbol, c.code, u.username, u.phone')
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('users as u', 'p.author_id = u.id')
			->where(array(
				'p.id'     => $id,
				'p.status' => 1,
			))
			->get()
			->row_array();
	}

	function get_product_by_user($id, $user_id) {
		return $this->db
			->where(array(
				'id'        => $id,
				'author_id' => $user_id,
			))
			->get('shop_products')
			->row_array();
	}

	function get_product_images($id) {
		return $this->db
			->where(array(
				'product_id' => $id,
			))
			->get('shop_product_images')
			->result_array();
	}

	function add_product_image($id, $data) {
		$insert_array = array(
			'product_id' => $id,
			'image'      => $data['file_name'],
		);
		//Choose main image
		$count = $this->db->where(array(
			'product_id' => $id,
			'main'       => 1
		))->count_all_results('shop_product_images');
		if (!$count) {
			$insert_array['main'] = 1;
		}

		$this->db->insert('shop_product_images', $insert_array);
		return $this->db->insert_id();
	}

	function get_image_by_user($id, $user_id) {
		return $this->db
			->select('i.*')
			->from('shop_product_images as i')
			->join('shop_products as p', 'p.id = i.product_id')
			->where(array(
				'i.id'        => $id,
				'p.author_id' => $user_id,
				'p.status <'  => 3,
			))
			->get()
			->row_array();
	}

	function delete_image($id, $info = false) {
		if (empty($info)) {
			$info = $this->db
				->select('i.*')
				->from('shop_product_images as i')
				->join('shop_products as p', 'p.id = i.product_id')
				->where(array(
					'i.id'        => $id,
					'p.author_id' => $this->data['user_info']['id'],
					'p.status <'  => 3,
				))
				->get()
				->row_array();
		}

		$success = false;
		if (!empty($info)) {
			$success = true;
			$success = unlink(FCPATH.'uploads/gallery/'.$info['image']);
			$success = unlink(FCPATH.'uploads/gallery/small_thumb/'.$info['image']);

			if ($success) {
				$this->db
					->where('id', $id)
					->delete('shop_product_images');
			}
		}
		return $success;
	}
}
