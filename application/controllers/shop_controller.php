<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shop_controller extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model(array(
			'menu_model',
			'shop_model',
			'special_model',
		));
		$this->load->library(array(
			'session',
			'ion_auth',
			'cart',
		));
		$this->data['main_menu']  = $this->menu_model->get_menu('upper');
		$this->data['left_block'] = $this->shop_model->get_categories();
		$this->data['user_info'] = $this->ion_auth->user()->row_array();

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');

		$this->data['types'] = array(
			'default' => 6,
			'gallery' => 9,
			'list'    => 12,
		);
	}

	public function index() {
		custom_404();
		$this->data['title'] = 'Main_page';

		load_views();
	}

	public function category($name = false) {
		if (empty($name)) {
			custom_404();
		}

		$this->data['category_info'] = $this->shop_model->get_category_info($name);

		if (empty($this->data['category_info'])) {
			custom_404();
		}

		$view_mode = $this->input->cookie('view_mode');
		$this->data['view_mode'] = $view_mode = isset($this->data['types'][$view_mode]) ? $view_mode : 'default';

		//VIP
		$this->data['name'] = lang('vip_lots');
		$this->data['products']    = $this->shop_model->get_vip_products(3);
		if (!empty($this->data['products'])) {
			$this->data['right_block'] = $this->load->view('right_lots', $this->data, true);
		}

		$this->data['name']  = $this->data['category_info']['name'];
		$this->data['title'] = lang('product_category').' "'.$this->data['name'].'"';

		$this->data['per_page']     = $this->data['types'][$view_mode];
		$this->data['total']        = $this->shop_model->count_products_by_category($this->data['category_info']['id']);
		$this->data['products']     = $this->shop_model->get_products_by_category($this->data['category_info']['id'], $this->data['per_page']);
		$this->data['center_block'] = $this->load->view('category', $this->data, true);

		load_views();
	}

	public function search() {
		$query = $this->input->get('q');

		$view_mode = $this->input->cookie('view_mode');
		$this->data['view_mode'] = $view_mode = isset($this->data['types'][$view_mode]) ? $view_mode : 'default';

		//VIP
		$this->data['name'] = lang('vip_lots');
		$this->data['products']     = $this->shop_model->get_vip_products(3);
		if (!empty($this->data['products'])) {
			$this->data['right_block'] = $this->load->view('right_lots', $this->data, true);
		}

		$this->data['title'] = $this->data['name'] = lang('search').' "'.$query.'"';

		$this->data['per_page']     = $this->data['types'][$view_mode];
		$this->data['total']        = $this->shop_model->count_search_products($query);
		$this->data['products']     = $this->shop_model->get_search_products($query, $this->data['per_page']);
		$this->data['center_block'] = $this->load->view('category', $this->data, true);

		load_views();
	}

	public function product($id = false, $name = false) {
		if (empty($id)) {
			custom_404();
		}

		$this->data['product_info'] = $this->db
			->select('p.*, c.symbol, c.code, u.username, u.phone, i.file_name')
			->from('shop_products as p')
			->join('shop_currencies as c', 'p.currency = c.id')
			->join('users as u', 'p.author_id = u.id', 'left')
			->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
			->where('p.id', $id)
			->get()
			->row_array();

		if (empty($this->data['product_info'])) {
			custom_404();
		}

		if (!$this->ion_auth->is_admin()) {
			$empty_amount = ($this->data['product_info']['unlimited'] == 0 && $this->data['product_info']['amount'] == 0);
			if (!$this->ion_auth->logged_in() && ($this->data['product_info']['status'] != 1 || $empty_amount)) {
				custom_404();
			} elseif ($this->ion_auth->logged_in()) {
				if($this->data['product_info']['author_id'] != $this->data['user_info']['id'] && (!$this->data['product_info']['status'] || $empty_amount)) {
					custom_404();
				} elseif ($this->data['product_info']['status'] > 2) {
					custom_404();
				}
			}
		}


		if ($this->ion_auth->logged_in()) {
			$is_bought = $this->db
				->from('shop_order_products as op')
				->join('shop_orders as o', 'op.order_id = o.id')
				->where(array(
					'op.product_id' => $id,
					'o.status'      => 1,
					'o.user_id'     => $this->data['user_info']['id'],
				))
				->get()
				->num_rows();
			if ($is_bought) {
				set_alert(lang('product_already_bought'), false, 'warning');
			}
		}

		$status_list = array(
			'0' => 'product_on_moderate',
			'2' => 'product_rejected',
			'3' => 'product_deleted',
		);

		if (isset($status_list[$this->data['product_info']['status']])) {
			set_alert(lang($status_list[$this->data['product_info']['status']]), false, 'danger');
		}

		$alias = url_title(translitIt($this->data['product_info']['name']), 'underscore', TRUE); 
		if ($alias != $name) {
			redirect('product/'.$id.'/'.$alias, 'refresh');
		}

		$this->db->query('UPDATE shop_products SET views = views + 1 WHERE id = '.$id);

		$this->data['categories'] = $this->shop_model->parent_categories($this->data['product_info']['cat_id']);
		$this->data['images'] = $this->shop_model->get_product_images($id);

		$this->data['title']        = $this->data['product_info']['name'];
		$this->data['center_block'] = $this->load->view('product', $this->data, true);

		load_views();
	}

	public function cart($step = 'orders') {
		if ($this->ion_auth->logged_in()) {
			if ($this->data['user_info']['is_seller']) {
				redirect('profile');
			}
			$this->data['left_block'] = $this->load->view('profile/menu', $this->data, true);
		}
		$in_order = $this->cart->total_items();
		if (empty($in_order) && $step != 'orders') {
			redirect('cart/orders', 'refresh');
		}
		$session_info = $this->session->userdata('order_info'); 
		if (empty($session_info) && !in_array($step, array('orders', 'information'))) {
			redirect('cart/information', 'refresh');
		}

		$this->data['links'] = array(
			'orders'       => lang('my_cart'),
			'information'  => lang('cart_information'),
			'payment'      => lang('cart_payment'),
			'confirmation' => lang('finish'),
		);
		$this->data['cur_step'] = $step;
		if (!isset($this->data['links'][$step])) {
			custom_404();
		}

		$this->data['title'] = $this->data['name'] = $this->data['links'][$step];

		if ($step == 'orders') {
			$order_items = $this->cart->contents();
			$ids = array();
			if (!empty($order_items)) {
				foreach ($order_items as $item) {
					$ids[] = $item['id'];
					$orders[$item['id']] = $item;
				}

				$this->data['order_items'] = !empty($orders) ? $orders : '';
				$this->data['products'] = $this->shop_model->get_product_info($ids);
				$this->load->library('table');
				$this->data['table'] = $this->table
					->text('name', array(
						'title' => 'Name',
						'width' => '60%',
						'func'  => function($row, $params, $that, $CI) {
							$row['no_show_commission'] = true;
							return $CI->load->view('profile/item', $row, true);
						}
				))
					->text('id', array(
						'title' => 'Price',
						'width' => '20%',
						'func'  => function($row, $params, $that, $CI) {
							$row['price'] += $CI->shop_model->product_commission($row);
							return '<div class="count">
								<span class="minus none">–</span>
								<input data-id="'.$CI->data['order_items'][$row['id']]['rowid'].'" data-price="'.$row['price'].'" type="text" value="'.$CI->data['order_items'][$row['id']]['qty'].'"/>
								<span class="plus">+</span>
								</div>';
						}
				))
					->text('price', array(
						'title' => 'Price',
						'width' => '20%',
						'func'  => function($row, $params, $that, $CI) {
							$row['price'] += $CI->shop_model->product_commission($row);
							return '<div class="price"><i class="c_icon_label"></i>'.floatval($row['price']).' '.$row['symbol'].'</div>';
						}
				))
					->btn(array(
						'func'  => function($row, $params, $html, $this, $CI) {
							return '<a href="#" data-id="'.$CI->data['order_items'][$row['id']]['rowid'].'" class="delete" title="Delete"></a>';
						}
				))
					->create($this->data['products'], array('no_header' => 1, 'class' => 'table'));
			}

			$this->data['center_block'] = $this->load->view('cart/orders', $this->data, true);
		} elseif ($step == 'information') {
			$fields = array(
				'login'    => '',
				'username' => '',
				'email'    => '',
				'company'  => '',
				'address'  => '',
				'city'     => '',
				'state'    => '',
				'country'  => '',
				'zip'      => '',
				'phone'    => '',
				'url'      => '',
			);
			foreach ($fields as $key => $item) {
				if (isset($session_info[$key])) {
					$fields[$key] = $session_info[$key];
					continue;
				}
				if (isset($this->data['user_info'][$key])) {
					$fields[$key] = $this->data['user_info'][$key];
					continue;
				}
			}

			$this->load->library('form');
			if (!$this->ion_auth->logged_in()) {
				$this->form->text('login', array('valid_rules' => 'required|trim|xss_clean|max_length[150]|alpha_dash',  'label' => lang('cart_login'), 'value' => $fields['company']));
			}

			$this->data['center_block'] = $this->form
				->text('username', array('valid_rules' => 'required|trim|xss_clean|max_length[150]', 'label' => lang('cart_name'), 'value' => $fields['username']))
				->text('email', array('valid_rules' => 'required|trim|xss_clean|max_length[150]|valid_email', 'label' => lang('cart_email'), 'value' => $fields['email']))
				->text('company', array('valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => lang('cart_company'), 'value' => $fields['company']))
				->text('address', array('valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => lang('cart_address'), 'value' => $fields['address']))
				->text('city', array('valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => lang('cart_city'), 'value' => $fields['city']))
				->text('state', array('valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => lang('cart_state'), 'value' => $fields['state']))
				->text('country', array('valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => lang('cart_country'), 'value' => $fields['country']))
				->text('zip', array('valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural', 'label' => lang('cart_zip'), 'value' => $fields['zip']))
				->text('phone', array('valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural', 'label' => lang('cart_phone'), 'value' => $fields['phone']))
				->text('url', array('valid_rules' => 'trim|xss_clean|max_length[100]',  'label' => lang('cart_url'), 'value' => $fields['url']))
				->func(function($params) {
					return '<button type="submit" class="orange_btn">'.lang('next_step').'</button>';
				})
					->create(array('action' => current_url(), 'error_inline' => 'true'));
			if ($this->form_validation->run() != false) {
				foreach ($fields as $key => $item) {
					$item = $this->input->post($key);
					if (!empty($item)) {
						$fields[$key] = $item;
					}
				}
				$this->session->set_userdata(array('order_info' => $fields));
				redirect('cart/payment', 'refresh');
			}

			$this->data['center_block'] = $this->load->view('cart/info', $this->data, true);
		} elseif($step == 'payment') {
			$this->load->library('form');
			$this->data['center_block'] = $this->form
				->hidden('confirm', 1)
				->func(function($params) {
					return '<button type="submit" class="orange_btn">'.lang('finish').'</button>';
				})
					->create(array('action' => site_url('cart/confirmation')));
			$this->data['info'] = $this->special_model->get_spec_content('payment_cart_step_3', $session_info);
			$this->data['center_block'] = $this->load->view('cart/payment', $this->data, true);
		} elseif($step == 'confirmation') {
			$finish = $this->input->post('confirm');
			if (empty($finish)) {
				redirect('cart/payment', 'refresh');
			}

			$order_items = $this->cart->contents();
			$ids = array();
			if(!empty($order_items)){
				foreach ($order_items as $item){
					$ids[$item['id']] = $item['id'];
					$orders[$item['id']] = $item;
				}
			}

			$this->data['order_items'] = !empty($orders) ? $orders : '';
			$this->data['products'] = $this->shop_model->get_product_info($ids);

			$order_id = $this->confirm_order($this->data);

			$replace = array(
				'%site_name' => SITE_NAME,
				'%order_id'  => '<a href="'.site_url('profile/order_view/'.$order_id).'">'.lang('order').' №'.$order_id.'</a>',
			);

			$this->data['center_block'] = str_replace(array_keys($replace), $replace, '<h4>'.lang('cart_congratulations').'</h4>');
			$this->data['center_block'] .= str_replace('%pay_btn', '<a class="orange_btn" href="'.site_url('profile/do_payment/'.$order_id).'">'.lang('here').'</a>', '<h4>'.lang('cart_pay_advice').'</h4>');
			$this->data['center_block'] = $this->load->view('cart/confirm', $this->data, true);
		}        
		load_views();
	}

	public function add_to_cart() {
		if (!empty($this->data['user_info']['is_seller'])) {
			echo 'is_seller';
			exit;
		}
		$id = intval($this->input->post('id'));
		if (empty($id)) {
			return false;
		}
		$product_info = $this->shop_model->get_product_info($id);
		if (empty($product_info)) {
			return false;
		}

		if ($product_info['amount'] < 1 && !$product_info['unlimited']) {
			echo 'Noqty';
			exit;
		}

		$prods = $this->cart->contents();
		$count = 1;
		if(!empty($prods)){
			foreach ($prods as $item){
				if($item['id'] == $product_info['id']){
					$count = $item['qty'] + 1;
					$rowid = $item['rowid'];
					break;
				}
			}
		}

		if ($product_info['amount'] < $count && !$product_info['unlimited']) {
			echo 'Noqty';
			exit;
		}

		$data = array(
			'id'      => $id,
			'qty'     => $count,
			'price'   => $product_info['price'] + $this->shop_model->product_commission($product_info),
			'name'    => $id,
			'options' => array(),
		);

		if($count == 1){
			$this->cart->insert($data);
		}else{
			$updata = array(
				'rowid'   => $rowid,
				'qty'     => $count,
			);
			$this->cart->update($updata);
		}
		echo 'OK';
	}

	public function update_cart(){
		$rowid = $this->input->post('id');
		$qty = intval($this->input->post('count'));
		$cart_products = $this->cart->contents();
		if (!isset($cart_products[$rowid])) {
			echo 'KO';
			exit;
		}

		$product_info = $this->db->where('id', $cart_products[$rowid]['id'])->get('shop_products')->row_array();

		$updata = array(
			'rowid' => $rowid,
			'qty'   => $qty,
		);

		if ($product_info['amount'] < $qty && !$product_info['unlimited']) {
			if ($cart_products[$rowid]['qty'] != $product_info['amount']) {
				$updata['qty'] = $qty = $product_info['amount'];
				$this->cart->update($updata);
			}
			echo $cart_products[$rowid]['qty'];
			exit;
		}

		echo $this->cart->update($updata) ? 'OK' : $cart_products[$rowid]['qty'];
	}

	private function confirm_order($all_data) {
		$user_data = $this->session->all_userdata();
		$id = 0;
		$auto_reg = false;
		if (!$this->ion_auth->logged_in()) {
			if (!$this->ion_auth->identity_check($user_data['order_info']['login'])) {
				$username = $user_data['order_info']['username'];
				$password = $user_data['order_info']['email'];
				$email    = $user_data['order_info']['email'];
				$additional_data = array(
					'login'     => $user_data['order_info']['login'],
					'company'   => $user_data['order_info']['company'],
					'phone'     => $user_data['order_info']['phone'],
					'country'   => $user_data['order_info']['country'],
					'state'     => $user_data['order_info']['state'],
					'city'      => $user_data['order_info']['city'],
					'zip'       => $user_data['order_info']['zip'],
					'address'   => $user_data['order_info']['address'],
					'url'       => $user_data['order_info']['url'],
					'is_seller' => 0,
				);								
				$id = $this->ion_auth->register($username, $password, $email, $additional_data);
				$auto_reg = true;
			} else {
				$by_email = $this->db->where('email', $user_data['order_info']['email'])->get('users')->row_array();
				$id = $by_email['id'];
			}
		} else {
			$user_identify = $this->ion_auth->user()->row_array();
			$id = $user_identify['id'];
		}

		if(empty($id)){
			return false; 
		}

		$total_price = $this->cart->total();

		$info = array(
			'total_price' => $total_price,
			'clear_price' => $this->cart->total(),
			'user_id'     => $id,
			'currency'    => 1,
			'username'    => $user_data['order_info']['username'],
			'email'       => $user_data['order_info']['email'],
			'company'     => $user_data['order_info']['company'],
			'phone'       => $user_data['order_info']['phone'],
			'country'     => $user_data['order_info']['country'],
			'state'       => $user_data['order_info']['state'],
			'city'        => $user_data['order_info']['city'],
			'zip'         => $user_data['order_info']['zip'],
			'address'     => $user_data['order_info']['address'],
			'add_date'    => time(),
			'status'      => 0,
		);

		$this->db->trans_begin();

		$this->db->insert('shop_orders',$info);
		$order_id = intval($this->db->insert_id());
		if (!empty($order_id)) {            
			foreach ($all_data['products'] as $item){
				$product_info[$item['id']] = $item;
			}
			$order_products = array();
			foreach ($this->cart->contents() as $item){
				$order_products[$item['rowid']] = array(
					'order_id'   => $order_id,
					'product_id' => $item['id'],
					'name'       => $product_info[$item['id']]['name'],
					'qty'        => $item['qty'],
					'price'      => $item['price'],
					'currency'   => $product_info[$item['id']]['currency'],
					'cat_id'     => $product_info[$item['id']]['cat_id'],
					'type'       => $product_info[$item['id']]['type'],
				);
			}

			$prep_files = $this->db
				->select('COUNT(*) as num, product_id')
				->where_in('product_id', array_keys($product_info))
				->where('status', 0)
				->group_by('product_id')
				->get('shop_product_media_files')
				->result_array();
			$license_files = false;
			if (!empty($prep_files)) {
				foreach ($prep_files as $key => $file) {
					$license_files[$file['product_id']] = $file['num'];
				}
			}

			foreach ($order_products as $rowid => $item) {
				if ($item['type'] == 'licenses') {
					if (!isset($license_files[$item['product_id']]) || $license_files[$item['product_id']] < $item['qty']) {
						$this->session->set_flashdata('danger', lang('orders_danger_message_pat1').' "'.$item['name'].'" '.lang('orders_danger_message_pat2'));
						$this->cart->update(array('rowid' => $rowid, 'qty'   => isset($license_files[$item['product_id']]) ? $license_files[$item['product_id']] : 0));
						redirect('cart', 'refresh');
						break;
					}
				}
			}

			$this->db->insert_batch('shop_order_products', $order_products);

			//$this->db->trans_rollback();
			//return $order_id;
			$this->db->trans_commit();

			$email_info = array(
				'order_id'  => $order_id,
				'auto_reg'  => $auto_reg,
				'email'     => $user_data['order_info']['email'],
			);
			$this->cart->destroy();

			$this->shop_model->send_mail($info['email'], 'orders_success_message', 'create_order', $email_info);

			return $order_id;
		}
	}

	private function pay_service($id = false, $type = 'lift_up') {
		$prices = array(
			'lift_up'  => LIFT_UP_PRICE,
			'mark'     => MARK_PRICE,
			'make_vip' => VIP_PRICE,
		);

		$duration = array(
			'lift_up'  => lang('unlimited'),
			'mark'     => MARK_DAYS.' '.lang('days'),
			'make_vip' => VIP_DAYS.' '.lang('days'),
		);

		if (empty($id) || !isset($prices[$type])) {
			custom_404();
		}

		$this->data['product_info'] = $this->db->from('shop_products')->where('id', $id)->get()->row_array();
		if (empty($this->data['product_info'])) {
			custom_404();
		}

		if ($this->input->is_ajax_request() && !isset($_POST['pay'])) {
			$this->data['title'] = $this->data['header'] = lang('facilities_pay_header').' "'.lang('finance_'.$type).'"';
			$payment_info = array(
				'price' => $prices[$type].' $',
				'days'  => $duration[$type],
			);
			$this->data['center_block'] = $this->special_model->get_spec_content($type.'_info', $payment_info);
			$this->load->library('form');
			$this->data['center_block'] .= $this->form
				->btn(array('name' => 'cancel', 'value' => lang('cancel'), 'class' => 'btn-default', 'modal' => 'close'))
				->btn(array('name' => 'pay', 'value' => lang('pay'), 'class' => 'btn-danger'))
				->create(array('action' => current_url(), 'btn_offset' => 3));
			echo $this->load->view('ajax', $this->data, true);
			exit;
		}


		if (empty($this->data['user_info']['is_seller'])) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect(product_url($id, $this->data['product_info']['name']), 'refresh');
		}

		if (!defined('LIFT_UP_PRICE') || LIFT_UP_PRICE <= 0) {
			$this->session->set_flashdata('danger', lang('facilities_disabled'));
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect(product_url($id, $this->data['product_info']['name']), 'refresh');
		}

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('danger', lang('need_auth'));
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect(product_url($id, $this->data['product_info']['name']), 'refresh');
		}
		$this->data['user_info'] = $this->ion_auth->user()->row_array();

		if ($this->data['user_info']['id'] != $this->data['product_info']['author_id']) {
			$this->session->set_flashdata('danger', lang('product_not_yours'));
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect(product_url($id, $this->data['product_info']['name']), 'refresh');
		}

		$user_balance = $this->shop_model->get_user_balance($this->data['user_info']['id']);
		if ($user_balance[0]['amount'] < $prices[$type]) {
			$this->session->set_flashdata('danger', lang('finance_no_money_message').' $'.$prices[$type]);
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect(product_url($id, $this->data['product_info']['name']), 'refresh');
		}

		$this->db->trans_begin();
		if ($type == 'mark') {
			$date_array = array('marked_date' => time() + (MARK_DAYS * 86400));
		} elseif ($type == 'make_vip') {
			$date_array = array('vip_date' => time() + (VIP_DAYS * 86400));
		} else {
			$date_array = array('sort_date' => time());
		}
		$this->db->where('id', $id)->update('shop_products', $date_array);
		$this->shop_model->log_payment($this->data['user_info']['id'], $type, $id, -$prices[$type]);
		$this->db->trans_commit();

		$this->session->set_flashdata('success', lang('facilities_paid'));
		if ($this->input->is_ajax_request()) {
			echo 'refresh';exit;
		}
		redirect(product_url($id, $this->data['product_info']['name']), 'refresh');

	}

	public function lift_up($id = false) {
		$this->pay_service($id, 'lift_up');
	}

	public function mark($id = false) {
		$this->pay_service($id, 'mark');
	}

	public function make_vip($id = false) {
		$this->pay_service($id, 'make_vip');
	}
}
