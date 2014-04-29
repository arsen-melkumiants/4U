<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shop_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_category_items() {
		return $this->db->select('*, name_'.$this->config->item('lang_abbr').' as name')->where('status', 1)->order_by('order', 'asc')->get('shop_categories')->result_array();
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
		$active_ids = array();
		if ($this->uri->segment(1) == 'category') {
			$category_info = $this->get_category_info($this->uri->segment(2));
			$active_ids = array_keys($this->parent_categories($category_info['id'], $all_branch));
		}
		return $this->get_category_tree($all_branch, 0, 'category/', $active_ids);
	}

	function get_category_tree($all_branch, $id = 0, $url = 'category/', $active_ids = array()) {
		$text = '<ul>';
		$num = 0;
		foreach ($all_branch as $key => $item) {
			if ($item['id'] && $item['parent_id'] == $id) {
				$icon = !empty($item['custom']) ? '<i class="'.$item['custom'].'"></i>' : '';
				$sub  = $this->get_category_tree($all_branch, $item['id'], $url, $active_ids);
				$text .= '<li'.($sub ? ' class="drop'.(in_array($item['id'], $active_ids) ? ' down' : '').'"' : '').'>';
				$text .= '<a href="'.site_url($url.$item['alias']).'">'.$icon.$item['name'].'</a>';
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
			->select('*, name_'.$this->config->item('lang_abbr').' as name')
			->where(array(
				'alias'  => $name,
				'status' => 1,
			))
			->get('shop_categories')
			->row_array();
	}

	function parent_categories($id = false, $all_branch = false) {
		if (empty($all_branch)) {
			$all_branch = $this->get_category_items();
			if (empty($all_branch)) {
				return false;
			}
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
					$ids = $ids + $result;
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
					$ids = $ids + $result;
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

	function count_search_products($query) {
		$this->search_products($query);
		return $this->db
			->select('p.id')
			->get()
			->num_rows();
	}

	function get_search_products($query, $limit = false) {
		$this->search_products($query);
		if ($limit) {
			$offset = isset($_GET['page']) && intval($_GET['page']) > 1 ? (intval($_GET['page']) - 1) * $limit : 0;
			$this->db->limit($limit, $offset);
		}
		return $this->db
			->select('p.*, c.symbol, c.code, i.file_name')
			->get()
			->result_array();
	}

	function search_products($query) {
		return $this->db
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
			->like('p.name', $query)
			->or_like('p.content', $query)
			->where('p.status', 1);
	}

	function count_products_by_category($id) {
		$this->products_by_category($id);
		return $this->db
			->select('p.id')
			->get()
			->num_rows();
	}

	function get_products_by_category($id, $limit = false) {
		$this->products_by_category($id);
		if ($limit) {
			$offset = isset($_GET['page']) && intval($_GET['page']) > 1 ? (intval($_GET['page']) - 1) * $limit : 0;
			$this->db->limit($limit, $offset);
		}
		return $this->db
			->select('p.*, c.symbol, c.code, i.file_name')
			->get()
			->result_array();
	}

	function products_by_category($id) {
		$all_branch = $this->get_category_items();
		$ids = $this->get_child_category_recurcive($all_branch, $id);
		return $this->db
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
			->where_in('p.cat_id', array_keys($ids))
			->where('p.status', 1);
	}

	function get_vip_products($limit = false) {
		if (!empty($limit)) {
			$this->db->limit($limit);
		}
		return $this->get_product_info();
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
		if (!$count && preg_match('/\.(jpg|jpeg|png|gif)/iu', $data['file_name'])) {
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
			if (preg_match('/\.(jpg|jpeg|png|gif)/iu', $info['file_name'])) {
				$success = unlink(FCPATH.'uploads/gallery/small_thumb/'.$info['file_name']);
			}

			if ($success) {
				$this->db
					->where('id', $id)
					->delete('shop_product_images');
				if ($info['main']) {
					$this->db->query('UPDATE shop_product_images SET main = 1 WHERE product_id = '.$info['product_id'].' AND file_name REGEXP \'\\.(jpg|jpeg|png|gif)\' LIMIT 1;');
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
