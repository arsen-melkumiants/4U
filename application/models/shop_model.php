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

	function get_recomended_products($limit = 6) {
		return $this->db
			->select('p.*, c.symbol, c.code, u.username, u.phone, i.file_name')
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('users as u', 'p.author_id = u.id')
			->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
			->where(array('p.status' => 1, 'p.recommended' => 1))
			->limit(6)
			->get()
			->result_array();
	}
	
	function get_best_sales_products($limit = 6) {
		return $this->db
			->select('SUM(o.qty) as num, p.*, c.symbol, c.code, i.file_name')
			->from('shop_order_products as o')
			->join('shop_products as p', 'p.id = o.product_id')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
			->where('p.status', 1)
			->order_by('num', 'desc')
			->group_by('o.product_id')
			->limit(6)
			->get()
			->result_array();
	}

	function get_new_products($limit = 6) {
		$this->db->limit($limit);
		return $this->get_product_info();
	}

	function get_products_by_category($id) {
		$all_branch = $this->get_category_items();
		$ids = $this->get_child_category_recurcive($all_branch, $id);
		return $this->db
			->select('p.*, c.symbol, c.code, i.file_name')
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
			->where_in('p.cat_id', array_keys($ids))
			->where('p.status', 1)
			->get()
			->result_array();
	}

	function get_product_info($id = false) {
		$this->db
			->select('p.*, c.symbol, c.code, u.username, u.phone, i.file_name')
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('users as u', 'p.author_id = u.id')
			->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
			->where('p.status', 1)
			->order_by('p.id', 'desc');
		if (is_array($id)) {
			return $this->db
				->where_in('p.id', $id)
				->get()
				->result_array();
		} elseif (intval($id)) {
			return $this->db
				->where('p.id', $id)
				->get()
				->row_array();
		} else {
			return $this->db
				->get()
				->result_array();
		}
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

	//IMAGES
	function get_product_images($id) {
		return $this->db
			->where(array(
				'product_id' => $id,
			))
			->get('shop_product_images')
			->result_array();
	}

	function add_product_image($product_id, $data) {
		$this->db
			->where('id', $product_id)
			->update('shop_products', array('status' => 0));
		$insert_array = array(
			'product_id' => $product_id,
			'file_name'  => $data['file_name'],
		);
		//Choose main image
		$count = $this->db->where(array(
			'product_id' => $product_id,
			'main'       => 1
		))->count_all_results('shop_product_images');
		if (!$count) {
			$insert_array['main'] = 1;
		}

		$this->db->insert('shop_product_images', $insert_array);
		return $this->db->insert_id();
	}

	function get_image_by_user($id, $user_id = false) {
		return $this->db
			->select('i.*, p.is_locked')
			->from('shop_product_images as i')
			->join('shop_products as p', 'p.id = i.product_id')
			->where(array(
				'i.id'        => $id,
				'p.author_id' => !empty($user_id) ? $user_id : $this->data['user_info']['id'],
				'p.status <'  => 3,
			))
			->get()
			->row_array();
	}

	function delete_image($id, $info = false) {
		$info = $this->get_image_by_user($id);

		$success = false;
		if ($info['is_locked']) {
			$success = 'Редактирование данного продукта заблокированно в свзяи с выполенинем заказа по нему';
		}
		if (!empty($info) && !$info['is_locked']) {
			$success = true;
			$success = unlink(FCPATH.'uploads/gallery/'.$info['file_name']);
			$success = unlink(FCPATH.'uploads/gallery/small_thumb/'.$info['file_name']);

			if ($success) {
				$this->db
					->where('id', $id)
					->delete('shop_product_images');
				if ($info['main']) {
					$this->db
						->where('product_id', $info['product_id'])
						->limit(1)
						->update('shop_product_images', array('main' => 1));
				}
				$this->db
					->where('id', $info['product_id'])
					->update('shop_products', array('status' => 0));
			}
		}
		return $success;
	}

	//MEDIA FILES

	function refresh_product($product_id) {
		$product_info = $this->db->where('id', $product_id)->get('shop_products')->row_array();
		if ($product_info['type'] == 'licenses') {
			$amount = $this->get_license_amount($product_id);
			$update_array = array('amount' => $amount, 'status' => 0);
		} else {
			$update_array = array('status' => 0);
		}
		$this->db
			->where(array('id' => $product_id))
			->update('shop_products', $update_array);
	}

	function get_product_files($id) {
		return $this->db
			->where(array(
				'product_id' => $id,
			))
			->get('shop_product_media_files')
			->result_array();
	}

	function add_product_file($product_id, $data) {
		$insert_array = array(
			'product_id' => $product_id,
			'file_name'  => $data['file_name'],
		);
		$this->db->insert('shop_product_media_files', $insert_array);
		$file_id = $this->db->insert_id();

		$this->refresh_product($product_id);
		return $file_id;
	}

	function get_license_amount($product_id) {
		return $this->db
			->where(array(
				'product_id' => $product_id,
				'status'     => 0,
			))
			->get('shop_product_media_files')
			->num_rows();
	}

	function get_file_by_user($id, $user_id = false) {
		return $this->db
			->select('f.*, p.is_locked')
			->from('shop_product_media_files as f')
			->join('shop_products as p', 'p.id = f.product_id')
			->where(array(
				'f.id'        => $id,
				'p.author_id' => !empty($user_id) ? $user_id : $this->data['user_info']['id'],
				'p.status <'  => 3,
			))
			->get()
			->row_array();
	}

	function delete_file($id, $info = false) {
		$info = $this->get_file_by_user($id);

		$success = false;
		if ($info['is_locked']) {
			$success = 'Редактирование данного продукта заблокированно в свзяи с выполенинем заказа по нему';
		}
		if (!empty($info) && !$info['is_locked']) {
			$success = true;
			$success = unlink(FCPATH.'media_files/'.$info['file_name']);

			if ($success) {
				$this->db
					->where('id', $id)
					->delete('shop_product_media_files');
				$this->refresh_product($info['product_id']);
			}
		}
		return $success;
	}
}
