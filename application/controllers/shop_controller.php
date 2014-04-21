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
				->text('phone', array('valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural', 'label' => 'Phone', 'value' => $fields['phone']))
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
			$this->load->library('form');
			$this->data['center_block'] = $this->form
				->hidden('confirm', 1)
				->func(function($params) {
					return '<button type="submit" class="orange_btn">Finish</button>';
				})
				->create(array('action' => site_url('cart/confirmation')));
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
            
			$this->confirm_order($this->data);
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
	
	private function confirm_order($all_data) {
        $user_data = $this->session->all_userdata();
        $id = 0;
        $auto_reg = false;
        if (!$this->ion_auth->logged_in()) {
            if (!$this->ion_auth->email_check($user_data['user_info']['email'])) {
                $username = $user_data['user_info']['username'];
                $password = $user_data['user_info']['email'];
                $email = $user_data['user_info']['email'];
                $additional_data = array(
					'company' => $user_data['order_info']['company'],
					'phone'   => $user_data['order_info']['phone'],
					'country' => $user_data['order_info']['country'],
					'state'   => $user_data['order_info']['state'],
					'city'    => $user_data['order_info']['city'],
					'zip'     => $user_data['order_info']['zip'],
					'address' => $user_data['order_info']['address'],
				);								
                $id = $this->ion_auth->register($username, $password, $email, $additional_data);
                $auto_reg = true;
            } else {
                $by_email = $this->db->where('email', $user_data['user_info']['email'])->get('users')->row_array();
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
        
        $this->db->insert('shop_orders',$info);
        $order_id = intval($this->db->insert_id());
      	print_r($all_data['products']); 
		if (!empty($order_id)) {            
			foreach ($all_data['products'] as $item){
				$product_info[$item['id']] = $item;
			}
			$order_products = array();
			foreach ($this->cart->contents() as $item){
				$order_products[] = array(
					'order_id'   => $order_id,
					'product_id' => $item['id'],
					'name'       => $product_info[$item['id']]['name'],
					'qty'        => $item['qty'],
					'price'      => $item['price'],
					'currency'   => $product_info[$item['id']]['currency'],
					'content'    => $product_info[$item['id']]['content'],
					'cat_id'     => $product_info[$item['id']]['cat_id'],
					'type'       => $product_info[$item['id']]['type'],
				);
			}
			$this->db->insert_batch('shop_order_products', $order_products);

			$email_info = array(
				'order_id'  => $order_id,
				'auto_reg'  => $auto_reg,
				'email'     => $user_data['order_info']['email'],
			);
			$this->cart->destroy();

			$this->load->library('email');

			$this->email->from(SITE_EMAIL, SITE_NAME);
			$this->email->to($info['email']); 
			$this->email->cc(SITE_EMAIL); 

			$this->email->subject('Заказ успешно принят');
			$this->email->message($this->load->view('email/create_order', $email_info ,true));

			$this->email->send();
		}
    }
}
