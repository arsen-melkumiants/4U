<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shop_controller extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->model(array(
			'menu_model',
			'shop_model',
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
    }

	public function index() {
		show_404();
		$this->data['title'] = 'Main_page';

		load_views();
	}

	public function category($name = false) {
		if (empty($name)) {
			show_404();
		}

		$this->data['category_info'] = $this->shop_model->get_category_info($name);

		if (empty($this->data['category_info'])) {
			show_404();
		}

		$this->data['title']        = 'Категория "'.$this->data['category_info']['name'].'"';
		$this->data['products']     = $this->shop_model->get_products_by_category($this->data['category_info']['id']);
		$this->data['center_block'] = $this->load->view('category', $this->data, true);

		load_views();
	}
	
	public function product($id = false, $name = false) {
		if (empty($id)) {
			show_404();
		}

		$this->data['product_info'] = $this->shop_model->get_product_info($id);

		if (empty($this->data['product_info'])) {
			show_404();
		}

		$alias = url_title(translitIt($this->data['product_info']['name']), 'underscore', TRUE); 
		if ($alias != $name) {
			redirect('product/'.$id.'/'.$alias, 'refresh');
		}

		$this->data['categories'] = $this->shop_model->parent_categories($this->data['product_info']['cat_id']);
		$this->data['images'] = $this->shop_model->get_product_images($id);

		$this->data['title']        = $this->data['product_info']['name'];
		$this->data['center_block'] = $this->load->view('product', $this->data, true);

		load_views();
	}

	public function cart($step = 'orders') {
		$in_order = $this->cart->total_items();
		if (empty($in_order) && $step != 'orders') {
			redirect('cart/orders', 'refresh');
		}
		$session_info = $this->session->userdata('order_info'); 
		if (empty($session_info) && !in_array($step, array('orders', 'information'))) {
			redirect('cart/information', 'refresh');
		}

		$this->data['links'] = array(
			'orders'       => 'My cart',
			'information'  => 'Information',
			'payment'      => 'Payment',
			'confirmation' => 'Finish',
		);
		$this->data['cur_step'] = $step;
		if (!isset($this->data['links'][$step])) {
			show_404();
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
							return $CI->load->view('profile/item', $row, true);
						}
					))
					->text('id', array(
						'title' => 'Price',
						'width' => '20%',
						'func'  => function($row, $params, $that, $CI) {
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
						'func'  => function($row, $params) {
							return '<div class="price"><i class="c_icon_label"></i>'.$row['price'].' '.$row['symbol'].'</div>';
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
				'username' => '',
				'email'    => '',
				'company'  => '',
				'address'  => '',
				'city'     => '',
				'state'    => '',
				'country'  => '',
				'zip'      => '',
				'phone'    => '',
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
			$this->data['center_block'] = $this->form
				->text('username', array('valid_rules' => 'required|trim|xss_clean|max_length[150]', 'label' => 'Name', 'value' => $fields['username']))
				->text('email', array('valid_rules' => 'required|trim|xss_clean|max_length[150]', 'label' => 'Email', 'value' => $fields['email']))
				->text('company', array('valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => 'Company', 'value' => $fields['company']))
				->text('address', array('valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => 'Address', 'value' => $fields['address']))
				->text('city', array('valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => 'City', 'value' => $fields['city']))
				->text('state', array('valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => 'State', 'value' => $fields['state']))
				->text('country', array('valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => 'Country', 'value' => $fields['country']))
				->text('zip', array('valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural', 'label' => 'Zip', 'value' => $fields['zip']))
				->text('phone', array('valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => 'Phone', 'value' => $fields['phone']))
				->func(function($params) {
					return '<button type="submit" class="orange_btn">Next step</button>';
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
			$this->data['center_block'] = $this->load->view('cart/payment', $this->data, true);
		} elseif($step == 'confirmation') {
		}        
		load_views();
	}
	
	public function add_to_cart() {
		$id = intval($this->input->post('id'));
		if (empty($id)) {
			return false;
		}
		$product_info = $this->shop_model->get_product_info($id);
		if(empty($product_info)){
			return false;
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
		$data = array(
			'id'      => $id,
			'qty'     => $count,
			'price'   => $product_info['price'],
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
        $updata = array(
            'rowid'   => $this->input->post('id'),
            'qty'     => intval($this->input->post('count')),
		);
		echo $this->cart->update($updata) ? 'OK' : 'KO';
    }
}
