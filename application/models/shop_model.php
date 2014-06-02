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

		$result = $this->get_struct_categories($result);

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

	function get_struct_categories($all_branch, $id = 0, $padding = '') {
		$ids = array();
		foreach ($all_branch as $key => $item) {
			if ($item['parent_id'] == $id) {
				$item['name'] = $padding.$item['name'];
				$ids[$item['id']] = $item;
				$result = $this->get_struct_categories($all_branch, $item['id'], $padding.'-&nbsp;');
				if(!empty($result)) {
					$ids = $ids + $result;
				}
			}
		}
		return $ids;
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
			->select('p.*, c.symbol, c.code, u.username, u.phone, i.file_name, i.folder')
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('users as u', 'p.author_id = u.id')
			->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
			->where(array('p.status' => 1, 'p.recommended' => 1))
			->where('(p.unlimited = 1 OR (p.unlimited = 0 AND p.amount > 0))')
			->limit(6)
			->get()
			->result_array();
	}

	function get_best_sales_products($limit = 6) {
		return $this->db
			->select('SUM(o.qty) as num, p.*, c.symbol, c.code, i.file_name, i.folder')
			->from('shop_order_products as o')
			->join('shop_products as p', 'p.id = o.product_id')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
			->where('p.status', 1)
			->where('(p.unlimited = 1 OR (p.unlimited = 0 AND p.amount > 0))')
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
			->select('p.id, p.status, p.unlimited, p.amount')
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
			->select('p.*, c.symbol, c.code, i.file_name, i.folder')
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
			->order_by('p.sort_date', 'desc')
			->order_by('p.id', 'desc')
			->having('p.status = 1')
			->having('(p.unlimited = 1 OR (p.unlimited = 0 AND p.amount > 0))')
			;
	}

	function count_products_by_category($id) {
		$this->products_by_category($id);
		return $this->db
			->select('p.id, p.status, p.unlimited, p.amount')
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
			->select('p.*, c.symbol, c.code, i.file_name, i.folder')
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
			->where('(p.unlimited = 1 OR (p.unlimited = 0 AND p.amount > 0))')
			->where('p.status', 1)
			->order_by('p.sort_date', 'desc')
			->order_by('p.id', 'desc')
			;
	}

	function get_vip_products($limit = false) {
		if (!empty($limit)) {
			$this->db->limit($limit);
		}
		$this->db->where('vip_date >=', time());
		$this->db->order_by('p.id', 'random');
		return $this->get_product_info();
	}

	function get_product_info($id = false) {
		$this->db
			->select('p.*, c.symbol, c.code, u.username, u.phone, i.file_name, i.folder')
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('users as u', 'p.author_id = u.id', 'left')
			->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
			->where('p.status', 1)
			->where('(p.unlimited = 1 OR (p.unlimited = 0 AND p.amount > 0))')
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
			->order_by('file_name', 'asc')
			->order_by('id', 'asc')
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
		if (isset($data['folder'])) {
			$insert_array['folder'] = $data['folder'];
		}
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
			@unlink(FCPATH.'uploads/gallery/'.$info['folder'].$info['file_name']);
			if (preg_match('/\.(jpg|jpeg|png|gif)/iu', $info['file_name'])) {
				@unlink(FCPATH.'uploads/gallery/'.$info['folder'].'small_thumb/'.$info['file_name']);
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
			->order_by('file_name', 'asc')
			->order_by('id', 'asc')
			->get('shop_product_media_files')
			->result_array();
	}

	function add_product_file($product_id, $data) {
		$insert_array = array(
			'product_id' => $product_id,
			'file_name'  => $data['file_name'],
		);
		if (isset($data['folder'])) {
			$insert_array['folder'] = $data['folder'];
		}
		if (isset($data['order'])) {
			$insert_array['order'] = $data['order'];
		}
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
			@unlink(FCPATH.'media_files/'.$info['folder'].$info['file_name']);

			if ($success) {
				$this->db
					->where('id', $id)
					->delete('shop_product_media_files');
				$this->refresh_product($info['product_id']);
			}
		}
		return $success;
	}


	//PAYMENTS

	function log_payment($user_id, $type_name, $type_id = 0, $amount, $currency = 1) {
		if (empty($user_id) || empty($type_name) || empty($amount) || empty($currency)) {
			return false;
		}
		
		$payment_info = array(
			'user_id'   => $user_id,
			'type_name' => $type_name,
			'type_id'   => $type_id,
			'amount'    => $amount,
			'currency'  => $currency,
			'date'      => time(),
		);
		$this->db->insert('shop_user_payment_logs', $payment_info);

		if ($type_name == 'fill_up') {
			$this->send_mail($this->data['user_info']['email'], 'mail_account_reffiled', 'account_reffiled', $payment_info);
		} elseif ($type_name == 'lift_up') {
			$this->send_mail($this->data['user_info']['email'], 'mail_services_lift_up_product', 'services_lift_up_product', $payment_info);
		} elseif ($type_name == 'mark') {
			$this->send_mail($this->data['user_info']['email'], 'mail_services_mark_product', 'services_mark_product', $payment_info);
		} elseif ($type_name == 'make_vip') {
			$this->send_mail($this->data['user_info']['email'], 'mail_services_vip_product', 'services_vip_product', $payment_info);
		} elseif ($type_name == 'income_product') {
			$this->send_mail($this->data['user_info']['email'], 'mail_product_purchased', 'product_purchased', $payment_info);
		}
		
		return $this->db->insert_id();
	}

	function send_mail($email, $subject, $mail_view, $email_info){
		$this->load->library('email');
		$this->email->from(SITE_EMAIL, SITE_NAME);
		$this->email->to($email); 
		$this->email->cc(SITE_EMAIL); 
		$this->email->subject(lang($subject));
		$this->email->message($this->load->view('email/'.$mail_view, $email_info ,true));
		$this->email->send();
	}
	
	function get_user_balance($user_id = false) {
		if (empty($user_id)) {
			$user_id = $this->data['user_info']['id'];
		}

		$user_balance = $this->db
			->select('SUM(l.amount) as amount, l.currency, c.symbol, c.code')
			->from('shop_user_payment_logs as l')
			->join('shop_currencies as c', 'l.currency = c.id')
			->where('l.user_id', $user_id)
			->group_by('l.currency')
			->get()
			->result_array();

		if (empty($user_balance)) {
			$user_balance[0] = array(
				'amount' => 0,
				'symbol' => '$',
			);
		}

		return $user_balance;
	}

	function pay_order($id = false, $order_info = false, $admin_payment = false) {
		if (empty($id) || empty($order_info)) {
			return false;
		}

		$order_products = $this->db
			->select('op.*, p.amount, p.author_id, p.commission, p.type_commission, p.unlimited')
			->from('shop_order_products as op')
			->join('shop_products as p', 'p.id = op.product_id')
			->where('op.order_id', $id)
			->order_by('op.id', 'desc')
			->get()
			->result_array();

		if(empty($order_products)) {
			return false;
		}

		foreach ($order_products as $item) {

			if (!$item['unlimited']) {
				$update_array[$item['product_id']] = array(
					'id'     => $item['product_id'],
					'amount' => $item['amount'] - $item['qty'],
				);
			}

			$user_profit[] = array(
				'user_id'    => $item['author_id'],
				'amount'     => $item['qty'] * ($item['price'] - $this->product_commission($item)),
				'product_id' => $item['product_id'],
			);

			//Default commission
			$order_products_update[$item['id']] = array(
				'id'              => $item['id'],
				'type_commission' => defined('TYPE_SALE_COMMISSION') ? TYPE_SALE_COMMISSION : 'fixed',
				'commission'      => defined('SALE_COMMISSION') ? SALE_COMMISSION : 0,
			);
			if (!empty($item['type_commission'])) {
				$order_products_update[$item['id']]['type_commission'] = $item['type_commission'];
				$order_products_update[$item['id']]['commission'] = $item['commission'];
			}

			if ($item['type'] == 'licenses') {
				$license_products = $this->db
					->where(array('product_id' => $item['product_id'], 'status' => 0))
					->limit($item['qty'])
					->get('shop_product_media_files')
					->result_array();
				if (empty($license_products) || count($license_products) != $item['qty']) {
					$this->session->set_flashdata('danger', lang('product_danger_message_key_is_not_available').' "'.$item['name'].'"');
					redirect('profile/order_view/'.$id, 'refresh');
				}

				foreach ($license_products as $file) {
					$license_files[$file['id']] = array(
						'id'     => $file['id'],
						'status' => 1,
					);
				}

				$order_products_update[$item['id']]['file_ids'] = implode(',', array_keys($license_files));
			}
		}

		$this->db->trans_begin();

		if (!empty($license_files)) {
			$this->db->update_batch('shop_product_media_files', $license_files, 'id');
		}

		$this->db->update_batch('shop_order_products', $order_products_update, 'id');
		if (!empty($update_array)) {
			$this->db->update_batch('shop_products', $update_array, 'id');
		}
		$this->db->where('id', $id)->update('shop_orders', array('status' => 1, 'paid_date' => time()));

		foreach ($user_profit as $item) {
			$this->shop_model->log_payment($item['user_id'], 'income_product', $item['product_id'], $item['amount']);
		}

		if (!$admin_payment) {
			$this->shop_model->log_payment($this->data['user_info']['id'], 'pay_order', $order_info['id'], -$order_info['total_price']);
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('success', lang('orders_payment_success'));
	}

	function rollback_order($id = false, $admin_payment = false) {
		if (empty($id)) {
			return false;
		}

		$order_products = $this->db
			->select('op.*, p.amount, p.author_id, p.unlimited')
			->from('shop_order_products as op')
			->join('shop_products as p', 'p.id = op.product_id')
			->where('op.order_id', $id)
			->order_by('op.id', 'desc')
			->get()
			->result_array();

		if(empty($order_products)) {
			return false;
		}

		foreach ($order_products as $item) {
			if (!$item['unlimited']) {
				$update_array[$item['product_id']] = array(
					'id'     => $item['product_id'],
					'amount' => $item['amount'] + $item['qty'],
				);
			}

			$user_profit[] = array(
				'user_id'    => $item['author_id'],
				'amount'     => $item['qty'] * ($item['price'] - $this->product_commission($item)),
				'product_id' => $item['product_id'],
			);

			if ($item['type'] == 'licenses' && !empty($item['file_ids'])) {

				foreach ((array)explode(',', $item['file_ids']) as $file) {
					$license_files[$file] = array(
						'id'     => $file,
						'status' => 0,
					);
				}

				$order_products_update[$item['id']] = array(
					'id'       => $item['id'],
					'file_ids' => '',
				);
			}
		}

		$this->db->trans_begin();

		if (!empty($license_files)) {
			$this->db->update_batch('shop_product_media_files', $license_files, 'id');
			$this->db->update_batch('shop_order_products', $order_products_update, 'id');
		}

		if (!empty($update_array)) {
			$this->db->update_batch('shop_products', $update_array, 'id');
		}
		$this->db->where('id', $id)->update('shop_orders', array('status' => 0, 'paid_date' => 0));

		foreach ($user_profit as $item) {
			$this->db->where(array(
				'user_id'   => $item['user_id'],
				'type_name' => 'income_product',
				'type_id'   => $item['product_id'],
				'amount'    => $item['amount'],
			))
			->limit(1)
			->delete('shop_user_payment_logs');
		}

		if (!$admin_payment) {
			$this->db->where(array(
				'type_name' => 'pay_order',
				'type_id'   => $id,
			))
			->delete('shop_user_payment_logs');
		}

		//$this->db->trans_rollback();
		$this->db->trans_commit();
		$this->session->set_flashdata('success', lang('orders_payment_success'));
	}

	function product_commission($product_info = false) {
		if (empty($product_info)) {
			return false;
		}

		if (empty($product_info['type_commission'])) {
			if(!defined('TYPE_SALE_COMMISSION')) {
				return false;
			}

			$product_info['type_commission'] = TYPE_SALE_COMMISSION;
			$product_info['commission']      = SALE_COMMISSION;
		}

		if ($product_info['type_commission'] == 'fixed') {
			return $product_info['commission'];
		} elseif ($product_info['type_commission'] == 'percent') {
			return round($product_info['price'] / 100 * $product_info['commission'], 2);
		}

		return false;
	}

	function get_payment_requests($type = false, $user_id = false) {
		if (empty($user_id)) {
			$user_id = $this->data['user_info']['id'];
		}

		if (!empty($type)) {
			$this->db->where('r.type', $type);
		}

		return $this->db
			->select('r.*, c.symbol, c.code')
			->from('shop_user_payment_requests as r')
			->join('shop_currencies as c', 'r.currency = c.id')
			->where(array(
				'r.user_id' => $user_id,
				'r.status'  => 0
			))
			->get();
	}
}
