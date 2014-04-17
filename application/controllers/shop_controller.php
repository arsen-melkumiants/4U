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

	public function basket($step = 'orders') {
		$in_order = $this->cart->total_items();
		if (empty($in_order) && $step != 'orders') {
			redirect('basket/orders', 'refresh');
		}

		$this->data['links'] = array(
			'orders'       => 'My cart',
			'contacts'     => 'Information',
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
				$this->data['products'] = $this->product_model->get_products_info($ids);
				$this->data['table'] = $this->table
					->text('name', array(
						'title' => 'Name',
						'width' => '60%',
						'func'  => function($row, $params, $that, $CI) {
							return $CI->load->view('profile/item', $row, true);
						}
					))
					->text('price', array(
						'title' => 'Price',
						'width' => '20%',
						'func'  => function($row, $params) {
							return '<div class="price"><i class="c_icon_label"></i>'.$row['price'].$row['symbol'].'</div>';
						}
					))
					->btn(array(
						'link'  => 'profile/delete_product/%d',
						'class' => 'delete',
						'title' => 'Delete',
						'modal' => true,
					))
					->create($this->data['products'], array('no_header' => 1, 'class' => 'table'));
			}

			$this->data['center_block'] = $this->load->view('basket/orders', $this->data, true);
		} elseif ($step == 'contacts') {

		}elseif($step == 'delivery_payment'){
		}elseif($step == 'confirmation'){
		}        
		load_views();
	}
	
	public function add_to_basket($id = false){
		$id = intval($id);
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
					$count = $item['qty']+1;
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
}
