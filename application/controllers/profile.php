<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library(array(
			'ion_auth',
			'form',
			'form_validation',
		));
		$this->load->helper('url');

		// Load MongoDB library instead of native db driver if required
		if (!$this->ion_auth->logged_in()) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect('personal/login', 'refresh');
		}

		$this->lang->load('auth');
		$this->load->helper('language');

		$this->load->model(array(
			'menu_model',
			'shop_model',
		));
		$this->data['main_menu']  = $this->menu_model->get_menu('upper');
		$this->data['left_block'] = $this->load->view('profile/menu', $this->data, true);
		$this->data['user_info'] = $this->ion_auth->user()->row_array();

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	//redirect if needed, otherwise display the user list
	function index() {
		$this->data['title'] = $this->data['name'] = lang('my_profile');

		$user_id = $this->data['user_info']['id'];

		$sold_stats = $this->db
			->select('SUM(op.qty) as amount, SUM(op.qty * op.price) as price')	
			->from('shop_order_products as op')
			->join('shop_products as p', 'p.id = op.product_id')
			->join('shop_orders as o', 'o.id = op.order_id')
			->where('o.status', 1)
			->where('p.author_id', $user_id)
			->get()
			->row_array();

		$bought_stats = $this->db
			->select('SUM(op.qty) as amount, SUM(op.qty * op.price) as price')	
			->from('shop_order_products as op')
			->join('shop_orders as o', 'o.id = op.order_id')
			->where('o.status', 1)
			->where('o.user_id', $user_id)
			->get()
			->row_array();

		if ($this->data['user_info']['is_seller']) {
			$this->data['stats'] = array(
				'active_sales'            => $this->db->where(array('author_id' => $user_id, 'status' => 1))->get('shop_products')->num_rows(),
				'sold_products_amount'    => !empty($sold_stats['amount']) ? floatval($sold_stats['amount']) : 0,
				'sold_products_profit'    => (!empty($sold_stats['price']) ? floatval($sold_stats['price']) : 0).' $',
			);
		} else {
			$this->data['stats'] = array(
				'bought_products_amount'  => !empty($bought_stats['amount']) ? floatval($bought_stats['amount']) : 0,
				'bought_products_expense' => (!empty($bought_stats['price']) ? floatval($bought_stats['price']) : 0).' $',
			);
		}

		$allowed_fields = array(
			'username' => lang('create_user_fname_label'),
			'email'    => lang('create_user_email_label'),
			'company'  => lang('create_user_company_label'),
			'address'  => lang('create_user_address_label'),
			'city'     => lang('create_user_city_label'),
			'state'    => lang('create_user_state_label'),
			'country'  => lang('create_user_country_label'),
			'zip'      => lang('create_user_zip_label'),
			'phone'    => lang('create_user_phone_label'),
			'url'      => lang('create_user_url_label'),
		);

		foreach ($this->data['user_info'] as $key => $field) {
			if(!isset($allowed_fields[$key])) {
				unset($this->data['user_info'][$key]);
			}
		}

		$this->data['labels'] = $allowed_fields;
		$this->data['center_block'] = $this->load->view('profile/info', $this->data, true);

		load_views();
	}

	function products($type = 'active') {
		if (!$this->data['user_info']['is_seller']) {
			redirect('profile');
		}
		$this->data['title'] = $this->data['name'] = lang('my_products');
		$this->data['type_list'] = array(
			'active'      => array(1),
			'moderate'    => array(0,2),
			'sold'        => '',
		);
		$type = isset($this->data['type_list'][$type]) ? $type : 'active';
		$this->data['type'] = $type;

		$this->load->library('table');
		$this->table
			->text('name', array(
				'title' => lang('product_name'),
				'width' => '50%',
				'func'  => function($row, $params, $that, $CI) {
					$row['is_vip'] = (!defined('VIP_DAYS') || !VIP_DAYS || ($row['vip_date'] + VIP_DAYS * 86400) > time());
					return $CI->load->view('profile/item', $row, true);
				}
		))
			->text('price', array(
				'title' => lang('product_price'),
				'width' => '20%',
				'func'  => function($row, $params) {
					return '<div class="price"><i class="c_icon_label"></i>'.floatval($row['price']).' '.$row['symbol'].'</div>';
				}
		));
		if ($type == 'sold') {
			$this->table
				->text('qty', array(
					'title' => lang('product_amount'),
					'width' => '10%',
					'func'  => function($row, $params) {
						return '<div class="price">'.$row['qty'].' '.lang('product_items').'</div>';
					}
			));
		}

		if ($type == 'moderate') {
			$this->table
				->btn(array(
					'func' => function($row, $params, $html, $that, $CI) {
						if ($row['status'] == 0) {
							return '<span class="label label-default">'.lang('pending').'</span>';
						} elseif ($row['status'] == 2) {
							return '<span class="label label-danger">'.lang('rejected').'</span>';
						}
					}
			));
		}

		$this->table
			->btn(array(
				'link'  => 'profile/edit_product/%d',
				'class' => 'edit',
				'title' => lang('edit'),
			))
			->btn(array(
				'link'  => 'profile/delete_product/%d',
				'class' => 'delete',
				'title' => lang('delete'),
				'modal' => true,
			))
			;
		$this->data['table'] = $this->table
			->create(function($CI) {
				if ($CI->data['type'] != 'sold') {
					return $CI->db
						->select('p.*, c.symbol, c.code, i.file_name, i.folder')
						->from('shop_products as p')
						->join('shop_currencies as c', 'p.currency = c.id')
						->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
						->where(array(
							'p.author_id' => $CI->data['user_info']['id'],
						))
						->where_in('p.status', $CI->data['type_list'][$CI->data['type']])
						->order_by('p.sort_date', 'desc')
						->order_by('p.id', 'desc')
						->get();
				} else {
					return $CI->db
						->select('p.*, c.symbol, c.code, i.file_name, i.folder, SUM(op.price * op.qty) as price, SUM(op.qty) as qty')
						->from('shop_products as p')
						->join('shop_currencies as c', 'p.currency = c.id')
						->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
						->join('shop_order_products as op', 'p.id = op.product_id')
						->join('shop_orders as o', 'op.order_id = o.id')
						->where(array(
							'p.author_id' => $CI->data['user_info']['id'],
						))
						->group_by('p.id')
						->order_by('op.id', 'desc')
						->get();
				}

			}, array(
				'no_header' => 1,
				'class'     => 'table',
				'limit'     => 9,
				'tr_func'   => function($row) {
					if (!defined('MARK_DAYS') || !MARK_DAYS || ($row['marked_date'] + MARK_DAYS * 86400) > time()) {
						return 'class="marked"';
					}
				}
		));

		$this->data['center_block'] = $this->load->view('profile/products', $this->data, true);

		load_views();
	}


	function add_product() {
		if (!$this->data['user_info']['is_seller']) {
			redirect('profile');
		}
		$this->data['title'] = $this->data['header'] = lang('product_add_header');
		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			load_views();
		} else {
			$info = array(
				'name'      => $this->input->post('name'),
				'price'     => $this->input->post('price'),
				'type'      => $this->input->post('type'),
				'amount'    => $this->input->post('amount'),
				'cat_id'    => $this->input->post('cat_id'),
				'content'   => $this->input->post('content'),
				'add_date'  => time(),
				'author_id' => $this->data['user_info']['id'],
				'status'    => 0,
			);
			if ($info['type'] == 'licenses') {
				$info['amount'] = 0;
			} else {
				$info['type'] = 'media';
			}
			$this->db->insert('shop_products', $info);
			$id = $this->db->insert_id();
			$this->session->set_flashdata('success', lang('product_add_message_success'));
			redirect('profile/product_gallery/'.$id, 'refresh');
		}
	}

	function edit_product($id = false) {
		$id = $this->data['id'] = intval($id);
		$product_info = $this->shop_model->get_product_by_user($id, $this->data['user_info']['id']);
		if (empty($product_info)) {
			redirect('profile/products', 'refresh');
		}

		$this->data['title'] = $this->data['name'] = lang('product_edit_header').' "'.$product_info['name'].'"';
		$this->data['center_block'] = $this->edit_form($product_info);
		$this->data['center_block'] = $this->load->view('profile/edit', $this->data, true);

		if ($this->form_validation->run() == FALSE) {
			load_views();
		} else {
			$info = array(
				'name'      => $this->input->post('name'),
				'price'     => $this->input->post('price'),
				'cat_id'    => $this->input->post('cat_id'),
				'content'   => $this->input->post('content'),
				'author_id' => $this->data['user_info']['id'],
			);

			if ($product_info['type'] == 'media') {
				$info['amount']    = $this->input->post('amount');
				$info['unlimited'] = $this->input->post('unlimited');
			}

			foreach ($info as $key => $item) {
				if (isset($product_info[$key]) && $product_info[$key] != $item) {
					$info['status'] = 0;
					break;
				}
			}

			if ($product_info['is_locked']) {
				set_alert(lang('product_edit_message_lock'), false, 'warning');
			}

			if (isset($info['status']) && !$product_info['is_locked'])	{
				$this->db->where('id', $id)->update('shop_products', $info);
				if ($product_info['created']) {
					$this->session->set_flashdata('success', lang('product_add_message_success'));
				}
			}
			redirect(current_url(), 'refresh');
		}

	}

	public function finish($id = false) {
		$id = $this->data['id'] = intval($id);
		$product_info = $this->shop_model->get_product_by_user($id, $this->data['user_info']['id']);
		if (empty($product_info)) {
			redirect('profile/products', 'refresh');
		}

		if (isset($_POST['finish']) && !$product_info['created']) {
			$this->db->where('id', $id)->update('shop_products', array('created' => 1));
			$this->session->set_flashdata('success', lang('product_add_message_success'));
			redirect('profile/products', 'refresh');
		}
	}

	private function edit_form($product_info = false) {
		$product_types = array(
			'media'    => lang('product_media_files'),
			'licenses' => lang('product_licenses'),
		);
		$product_unlimited = array(
			'0' => lang('no'),
			'1' => lang('yes'),
		);
		$product_categories = $this->shop_model->get_product_categories();
		array_unshift($product_categories, array('id' => '', 'name' => lang('product_no_category')));
		$this->load->library('form');
		$this->form
			->text('name', array(
				'value'       => $product_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => lang('product_name'),
			))
			->text('price', array(
				'value'       => $product_info['price'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|price',
				'symbol'      => '$',
				'icon_post'   => true,
				'label'       => lang('product_price'),
			));
		if (!empty($product_info)) {
			$this->form
				->text('type', array(
					'value'       => $product_info['type'],
					'valid_rules' => 'trim|xss_clean',
					'label'       => lang('product_type'),
					'readonly'    => true,
				));
			if ($product_info['type'] != 'licenses') {
				$this->form
					->radio('unlimited', array(
						'value'       => $product_info['unlimited'],
						'inputs'      => $product_unlimited,
						'label'       => lang('product_unlimited_descr'),
						'valid_rules' => 'required|trim|xss_clean',
					))
					->text('amount', array(
						'group_class' => 'amount_field',
						'value'       => $product_info['amount'],
						'valid_rules' => 'required|trim|xss_clean|is_natural',
						'label'       => lang('product_amount'),
					));
			}
		} else {
			$this->form
				->radio('type', array(
					'inputs'      => $product_types,
					'label'       => lang('product_type_descr'),
					'valid_rules' => 'required|trim|xss_clean',
				))
				->radio('unlimited', array(
					'inputs'      => $product_unlimited,
					'label'       => lang('product_unlimited_descr'),
					'valid_rules' => 'required|trim|xss_clean',
				))
				->text('amount', array(
					'group_class' => 'amount_field',
					'value'       => 0,
					'valid_rules' => 'required|trim|xss_clean|is_natural',
					'label'       => lang('product_amount'),
				));
		}
		$this->form
			->select('cat_id', array(
				'value'       => $product_info['cat_id'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => lang('product_category'),
				'options'     => $product_categories,
				'search'      => true,
			))
			->textarea('content', array(
				'value'       => $product_info['content'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => lang('product_content'),
			))
			->btn(array('value' => empty($product_info) ? lang('add') : lang('edit')));
		if (!empty($product_info)) {
			$this->form
				->link(array('name' => lang('next_step'), 'href' => site_url('profile/product_gallery/'.$product_info['id']), 'style' => 'float:right;'));
			//->link(array('name' => 'Gallery', 'href' => site_url('profile/product_gallery/'.$product_info['id'])))
			//->link(array('name' => 'Media content', 'href' => site_url('profile/product_media_files/'.$product_info['id'])));
		}
		return $this->form->create(array('action' => current_url(), 'error_inline' => 'true'));
	}

	function delete_product($id = false) {
		$id = intval($id);
		$product_info = $this->shop_model->get_product_by_user($id, $this->data['user_info']['id']);
		if (empty($product_info)) {
			redirect('profile/products', 'refresh');
		}
		if (empty($product_info) || $product_info['is_locked']) {
			if ($product_info['is_locked']) {
				$this->session->set_flashdata('danger', lang('product_delete_message_lock'));
			}
			if ($this->input->is_ajax_request()) {
				echo 'refresh';
				exit;
			} else {
				redirect('profile/products', 'refresh');
			}
		}

		$this->data['title'] = $this->data['header'] = lang('product_delete_header').' "'.$product_info['name'].'"';

		if ($this->input->is_ajax_request()) {
			if (isset($_POST['delete'])) {
				$this->db->where('id', $id)->update('shop_products', array('status' => 3));
				$this->session->set_flashdata('success', lang('product_delete_message_success'));
				echo 'refresh';
			} else {
				$this->load->library('form');
				$this->data['center_block'] = $this->form
					->btn(array('name' => 'cancel', 'value' => lang('cancel'), 'class' => 'btn-default', 'modal' => 'close'))
					->btn(array('name' => 'delete', 'value' => lang('delete'), 'class' => 'btn-danger'))
					->create(array('action' => current_url(), 'btn_offset' => 3));
				echo $this->load->view('ajax', $this->data, true);
			}
		} else {
			$this->db->where('id', $id)->update('shop_products', array('status' => 3));
			$this->session->set_flashdata('success', lang('product_delete_message_success'));
			redirect('profile/products', 'refresh');
		}
	}

	function product_gallery($id = false) {
		$this->product_media_files($id, 'image');
	}

	function upload_gallery($id = false) {
		$this->upload_media_files($id, 'image');
	}

	function delete_image($id = false) {
		$this->delete_file($id, 'image');
	}

	function product_media_files($id = false, $type = false) {
		$id = $this->data['id'] = intval($id);
		$this->data['type'] = $type;
		$product_info = $this->shop_model->get_product_by_user($id, $this->data['user_info']['id']);
		if (empty($product_info)) {
			redirect('profile/products', 'refresh');
		}

		if ($type == 'image') {
			$allowed_types = 'jpg|jpeg|png|gif|txt|text|rtx|rtf|doc|docx|xlsx|word|xl';
			$this->data['title'] = $this->data['name'] = lang('product_gallery_header');
			$this->data['descr'] = lang('product_gallery_descr');
			$this->data['descr'] .= '<br /><b>'.lang('available_formats').':</b> '.str_replace('|',', ', $allowed_types);
			$this->data['descr'] .= '<br /><b>'.lang('max_size').':</b> 5 Mb';
			$this->data['upload_url'] = site_url('profile/upload_gallery/'.$id);
		} else {
			$this->data['title'] = $this->data['name'] = lang('product_media_file_header');
			if ($product_info['type'] == 'licenses') {
				$allowed_types = 'jpg|jpeg|png|gif|text|txt';
			} else {
				//$allowed_types = 'jpg|jpeg|png|gif|doc|pdf|docx|txt|xls|mpeg|mpg|mpe|qt|mov|avi|movie|wmv';
				$allowed_types = 'avi|wmv|mpg|mpeg|mp4|m2t|m2ts|mkv|mov|flv|jpg|jpeg|cr2|psd|gif|bmp|tif|tga|cdr|ai|dwg|eps|raw|png|al|ps|plt|dxf|pdf|svg|svgz|zip|rar';
			}
			$this->data['descr'] = lang('product_media_file_descr');
			$this->data['descr'] .= '<br /><b>'.lang('available_formats').':</b> '.str_replace('|',', ', $allowed_types);
			$this->data['descr'] .= '<br /><b>'.lang('max_size').':</b> 10 Gb';
			$this->data['upload_url'] = site_url('profile/upload_media_files/'.$id);
		}

		if ($product_info['is_locked']) {
			set_alert(lang('product_edit_message_lock'), false, 'warning');
		}

		$this->data['center_block'] = $this->load->view('profile/upload', $this->data, true);
		load_views();
	}

	function upload_media_files($id = false, $type = 'file') {
		$id = intval($id);
		$product_info = $this->shop_model->get_product_by_user($id, $this->data['user_info']['id']);
		if (empty($product_info)) {
			redirect('profile/products', 'refresh');
		}
		$folder = $product_info['id'];

		if ($type == 'image') {
			$upload_path_url         = base_url('uploads/gallery/'.$folder).'/';
			$config['upload_path']   = FCPATH.'uploads/gallery/'.$folder;
			$config['allowed_types'] = 'jpg|jpeg|png|gif|txt|text|rtx|rtf|doc|docx|xlsx|word|xl';
			$config['max_size']      = '5000000';
		} else {
			$upload_path_url = base_url('media_files').'/';
			$config['upload_path'] = FCPATH.'media_files/'.$folder;
			if ($product_info['type'] == 'licenses') {
				$config['allowed_types'] = 'jpg|jpeg|png|gif|text|txt';
			} else {
				//$config['allowed_types'] = 'jpg|jpeg|png|gif|doc|pdf|docx|txt|xls|mpeg|mpg|mpe|qt|mov|avi|movie|wmv';
				$config['allowed_types'] = 'avi|wmv|mpg|mpeg|mp4|m2t|m2ts|mkv|mov|flv|jpg|jpeg|cr2|psd|gif|bmp|tif|tga|cdr|ai|dwg|eps|raw|png|al|ps|plt|dxf|pdf|svg|svgz|zip|rar';
			}
			$config['max_size']      = '10000000000';
		}
		@mkdir($config['upload_path'], 0777, true);
		//$config['file_name'] = !empty($_FILES['userfile']) ? $_FILES['userfile']['size'] : false;

		$this->load->helper('file');
		$this->load->library('upload');
		$this->upload->initialize($config);

		$files = array();

		if (!$this->upload->do_upload() || $product_info['is_locked']) {
			$error = $this->upload->display_errors();
			if ($product_info['is_locked']) {
				$error = lang('product_edit_message_lock');
				$data = $this->upload->data();
				@unlink($data['full_path']);
			}
			if (!empty($error) && !empty($_FILES)) {
				$files[] = array(
					'name'         => $_FILES['userfile']['name'],
					'url'          => '',
					'thumbnailUrl' => '',
					'deleteUrl'    => '',
					'deleteType'   => 'POST',
					'error'        => strip_tags($error),
				);
			} else {
				$product_files = $type == 'image' ? $this->shop_model->get_product_images($id) : $this->shop_model->get_product_files($id);
				foreach ($product_files as $item) {
					$thumbnail = '';
					if (preg_match('/\.(jpg|jpeg|png|gif)/iu', $item['file_name'])) {
						if ($type == 'image') {
							$thumbnail = $upload_path_url.'small_thumb/'.$item['file_name'];
						} else {
							$thumbnail = $upload_path_url.$item['id'];
						}
					}
					$files[] = array(
						'name'         => $item['file_name'],
						'url'          => $type == 'image' ? $upload_path_url.$item['file_name'] : $upload_path_url.$item['id'],
						'thumbnailUrl' => $thumbnail,
						'deleteUrl'    => site_url('profile/delete_'.$type.'/'.$item['id']),
						'deleteType'   => 'POST',
						'error'        => null,
						'sold'         => !empty($item['status'])
					);
					$last_order = $item['order'];
				}
			}

		} else {
			$data = $this->upload->data();
			if (isset($_POST['order'])) {
				$data['order'] = intval($_POST['order']);
			}
			$data['folder'] = $folder.'/';
			if ($type == 'image') {
				$file_id = $this->shop_model->add_product_image($id, $data);
				$this->load->library('image_lib');
				if (preg_match('/\.(jpg|jpeg|png|gif)/iu', $data['file_name'])) {
					$this->resize_image($data, $new_width = 250, 'small_thumb');
				}
			} else {
				$file_id = $this->shop_model->add_product_file($id, $data);
			}

			$thumbnail = '';
			if (preg_match('/\.(jpg|jpeg|png|gif)/iu', $data['file_name'])) {
				if ($type == 'image') {
					$thumbnail = $upload_path_url.'small_thumb/'.$data['file_name'];
				} else {
					$thumbnail = $upload_path_url.$file_id;
				}
			}

			$files[] = array(
				'name'         => $data['file_name'],
				'url'          => $type == 'image' ? $upload_path_url.$data['file_name'] : $upload_path_url.$file_id,
				'thumbnailUrl' => $thumbnail,
				'deleteUrl'    => site_url('profile/delete_'.$type.'/'.$file_id),
				'deleteType'   => 'POST',
				'error'        => null,
			);
		}

		$result_array['files'] = $files;
		if (!empty($last_order)) {
			$result_array['order'] = $last_order;
		}
		$result_array['files'] = $files;
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($result_array));
	}

	public function delete_file($id = false, $type = 'file') {
		$id = intval($id);
		if ($type == 'image') {
			$product_file = $this->shop_model->get_image_by_user($id);
		} else {
			$product_file = $this->shop_model->get_file_by_user($id);
		}
		if (empty($product_file)) {
			redirect('profile/products', 'refresh');
		}

		$success = ($type == 'image') ? $this->shop_model->delete_image($id) : $this->shop_model->delete_file($id);
		$file = $product_file['folder'].'/'.$product_file['file_name'];

		$info = array(
			'success' => $success,
			'path'    => base_url('uploads/gallery/'.$file),
			'file'    => is_file(FCPATH.'uploads/gallery/'.$file),
			'error'   => $success !== true ? $success : null,
		);

		if ($this->input->is_ajax_request()) {
			if (!$product_file['is_locked']) {
				echo json_encode(array($info));
			}
		} else {
			$file_data['delete_data'] = $file;
			$this->load->view('admin/delete_success', $file_data);
		}
	}

	private function resize_image($data, $new_width = false, $dir = ''){
		if(empty($new_width)){
			return false;
		}
		$origin_width = $data['image_width'];
		$origin_height = $data['image_height'];

		$prep_width = $origin_width/$new_width;
		$prep_height = round($origin_height/$prep_width);

		@mkdir($data['file_path'].$dir.'/', 0777, true);
		$config['image_library']  = 'gd2';
		$config['source_image']   = $data['full_path'];
		$config['new_image']      = $data['file_path'].$dir.'/'.$data['file_name'];
		$config['quality']        = '85%';
		$config['width']          = $new_width;
		$config['height']         = $prep_height;
		$config['maintain_ratio'] = true;

		$this->image_lib->initialize($config);
		$this->image_lib->resize();
	}

	function get_media_file($id = false) {
		$id = intval($id);
		$product_file = $this->shop_model->get_file_by_user($id);
		if (empty($product_file)) {
			$product_file = $this->db
				->select('f.*, p.type, p.file_ids, o.status, o.user_id')
				->from('shop_product_media_files as f')
				->join('shop_order_products as p', 'p.product_id = f.product_id')
				->join('shop_orders as o', 'p.order_id = o.id')
				->where('f.id', $id)
				->where('o.user_id', $this->data['user_info']['id'])
				->where('o.status', 1)
				->get()
				->row_array();
			if (empty($product_file)) {
				show_404();
			}

			if ($product_file['type'] == 'licenses') {
				$file_ids = explode(',', $product_file['file_ids']);
				if (empty($file_ids) || !in_array($id, $file_ids)) {
					show_404();
				}
			}
		}

		header('X-Sendfile: '.FCPATH.'media_files/'.$product_file['folder'].$product_file['file_name']);
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$product_file['file_name'].'"');
		exit;
	}


	function orders() {
		if ($this->data['user_info']['is_seller']) {
			redirect('profile');
		}
		$this->data['title'] = $this->data['header'] = lang('my_orders');

		$this->load->library('table');
		$this->table
			->text('id', array(
				'title' => lang('number'),
				'width' => '20%',
				'func'  => function($row, $params) {
					return '<a class="number" href="'.site_url('profile/order_view/'.$row['id']).'">'.lang('order').' №'.$row['id'].'</a>';
				}
		))
			->text('total_price', array(
				'title' => lang('orders_total_price'),
				'width' => '30%',
				'func'  => function($row, $params) {
					return '<div class="price"><i class="c_icon_label"></i>'.floatval($row['total_price']).' '.$row['symbol'].'</div>';
				}
		))
			->date('add_date', array('title' => 'Date', 'width' => '20%'))
			->btn(array('func' => function($row, $params, $html, $that, $CI) {
				if ($row['status'] == 0) {
					return '<span class="label label-default">'.lang('orders_pending_payment').'</span>';
				} elseif ($row['status'] == 1) {
					return '<span class="label label-success">'.lang('orders_paid').'</span>';
				}
			}
		))
			->btn(array('link'  => site_url('profile/order_view/%d'), 'title' => lang('orders_details')));

		$this->data['center_block'] = $this->table
			->create(function($CI) {
				return $CI->db
					->select('o.*, c.symbol, c.code')
					->from('shop_orders as o')
					->join('shop_currencies as c', 'o.currency = c.id')
					->where(array(
						'o.user_id' => $CI->data['user_info']['id'],
					))
					->order_by('id', 'desc')
					->get();
			}, array('no_header' => 1, 'class' => 'table product_list orders'));
		load_views();
	}


	function order_view($id = false) {
		if ($this->data['user_info']['is_seller']) {
			redirect('profile');
		}
		$id = intval($id);
		if (empty($id)) {
			show_404();
		}

		$this->data['order_info'] = $this->db->where(array('id' => $id, 'user_id' => $this->data['user_info']['id']))->get('shop_orders')->row_array();
		if (empty($this->data['order_info'])) {
			show_404();
		}

		$this->data['title'] = $this->data['header'] = lang('order').' №'.$this->data['order_info']['id'];
		$this->data['right_amount'] = true;

		$this->load->library('table');
		$this->table
			->text('name', array(
				'title' => 'Name',
				'width' => '60%',
				'func'  => function($row, $params, $that, $CI) {
					$row['id'] = $row['product_id'];
					if ($CI->data['order_info']['status'] == 1) {
						if ($row['type'] == 'licenses') {
							$row['files_list'] = $CI->db->where_in('id', explode(',', $row['file_ids']))->get('shop_product_media_files')->result_array();
						} else {
							$row['files_list'] = $CI->db->where(array('product_id' => $row['id'], 'status' => 0))->get('shop_product_media_files')->result_array();
						}
					}
					return $CI->load->view('profile/item', $row, true);
				}
		))
			->text('qty', array(
				'title' => lang('product_amount'),
				'width' => '20%',
				'func'  => function($row, $params) {
					return $row['qty'].' '.lang('product_items');
				}
		))
			->text('price', array(
				'title' => lang('product_price'),
				'width' => '20%',
				'func'  => function($row, $params) {
					return '<div class="price"><i class="c_icon_label"></i>'.floatval($row['price']).' '.$row['symbol'].'</div>';
				}
		))
			->btn(array('func' => function($row, $params, $html, $that, $CI) {
				if ($row['amount'] < $row['qty'] && $CI->data['order_info']['status'] == 0) {
					$CI->data['right_amount'] = false;
					set_alert(lang('orders_danger_message_can_not_pay_part1').' "'.$row['name'].'" '.lang('orders_danger_message_can_not_pay_part2').' '.$row['qty'], false, 'danger');
					return '<span class="label label-danger">'.lang('orders_message_no_product_in_amount').' '.$row['qty'].'</span>';
				}
			}
		));

		$this->data['center_block'] = $this->table
			->create(function($CI) {
				return $CI->db
					->select('op.*, p.amount, c.symbol, c.code, i.file_name, i.folder')
					->from('shop_order_products as op')
					->join('shop_products as p', 'p.id = op.product_id')
					->join('shop_currencies as c', 'p.currency = c.id')
					->join('shop_product_images as i', 'p.id = i.product_id AND i.main = 1', 'left')
					->where('op.order_id', $CI->data['order_info']['id'])
					->order_by('op.id', 'desc')
					->get();
			}, array('no_header' => 1, 'class' => 'table product_list orders'));

		if ($this->data['right_amount'] && $this->data['order_info']['status'] == 0) {
			$this->data['center_block'] .= '<a href="'.site_url('profile/test_payment/'.$id).'" class="btn" title="">'.lang('orders_pay').'</a>';
		}
		$this->data['center_block'] .= '<span class="price_total">'.lang('orders_total_price').' <span><i class="c_icon_label"></i> <span>'.$this->data['order_info']['total_price'].'</span> $</span></span>';

		load_views();
	}

	function test_payment($id = false) {
		$id = intval($id);
		if (empty($id)) {
			show_404();
		}

		$order_info = $this->db->where(array('id' => $id, 'user_id' => $this->data['user_info']['id']))->get('shop_orders')->row_array();
		if (empty($order_info)) {
			show_404();
		}

		if ($order_info['status']) {
			redirect('profile/order_view/'.$id, 'refresh');
		}

		$user_balance = $this->shop_model->get_user_balance();
		if ($order_info['total_price'] > $user_balance[0]['amount'] || $order_info['currency'] != $user_balance[0]['currency']) {
			$this->session->set_flashdata('danger', lang('finance_no_money_message'));
			redirect('profile/order_view/'.$id, 'refresh');

		}

		$this->shop_model->pay_order($id, $order_info);

		redirect('profile/order_view/'.$id, 'refresh');
	}



	function finance() {
		$this->data['title'] = $this->data['header'] = lang('my_finance');

		$this->data['balance'] = $this->shop_model->get_user_balance();
		$this->data['center_block'] = $this->load->view('profile/finance', $this->data, true);

		$this->load->library('table');
		$this->table
			->text('type_name', array(
				'title' => lang('finance_type'),
				'width' => '50%',
				'func'  => function($row, $params, $that, $CI) {
					return lang('finance_'.$row['type_name']);
				}
		))
			->text('amount', array(
				'title' => lang('price'),
				'width' => '30%',
				'func'  => function($row, $params) {
					$color = '';
					if ($row['amount'] < 0) {
						$color = 'text-danger';
					} elseif ($row['type_name'] == 'fill_up') {
						$color = 'text-warning';
					} elseif ($row['type_name'] == 'income_product') {
						$color = 'text-success';
					}
					return '<div class="price '.$color.'"><i class="c_icon_label"></i>'.floatval($row['amount']).' '.$row['symbol'].'</div>';
				}
		))
			->date('date', array(
				'title' => lang('date'),
			));
		$this->data['center_block'] .= $this->table
			->create(function($CI) {
				return $CI->db
					->select('l.*, c.symbol, c.code')
					->from('shop_user_payment_logs as l')
					->join('shop_currencies as c', 'l.currency = c.id')
					->where('l.user_id', $CI->data['user_info']['id'])
					->order_by('l.id', 'desc')
					->get();
			}, array('no_header' => 1, 'class' => 'table product_list orders'));

		load_views();
	}

	function fill_up_requests() {
		$this->data['title'] = $this->data['header'] = lang('finance_fill_up');

		$this->data['center_block'] = 'Paxum<br />';
		$this->data['center_block'] .= lang('finance_send_money').': <b>'.$this->data['user_info']['email'].'</b><br />';
		$this->data['center_block'] .= lang('finance_add_payment_comment').': <b>add-'.$this->data['user_info']['email'].'</b>';

		load_views();
	}

/*	function fill_up_requests() {
		$this->data['title'] = $this->data['header'] = lang('finance_fill_up_requests');

		$this->load->library('table');
		$this->table
			->text('id', array(
				'width' => '30%',
				'func'  => function($row, $params) {
					return '№'.$row['id'];
				}
		))
			->text('amount', array(
				'title' => lang('price'),
				'func'  => function($row, $params) {
					return '<div class="price"><i class="c_icon_label"></i>'.floatval($row['amount']).' '.$row['symbol'].'</div>';
				}
		))
			->date('add_date', array(
				'title' => lang('date'),
			))
			->btn(array(
				'link'  => 'profile/delete_withdrawal_request/%d',
				'class' => 'delete',
				'title' => lang('delete'),
				'modal' => true,
			))
			;

		$this->data['center_block'] = $this->table
			->create(function($CI) {
				return $CI->shop_model->get_payment_requests('fill_up');
			}, array('no_header' => 1, 'class' => 'table product_list orders'));

		$this->load->library('form');
		$this->data['center_block'] .= $this->form
			->text('amount', array(
				'valid_rules' => 'required|trim|xss_clean|price',
				'symbol'      => '$',
				'icon_post'   => true,
				'label'       => lang('product_amount'),
			))
			->btn(array('value' => lang('finance_fill_up')))
			->create(array('action' => current_url(), 'error_inline' => 'true'));

		if ($this->form_validation->run() != FALSE) {
			$this->db->insert('shop_user_payment_requests', array(
				'type'     => 'fill_up',
				'name'     => '',
				'amount'   => $this->input->post('amount'),
				'currency' => 1,
				'user_id'  => $this->data['user_info']['id'],
				'add_date' => time(),
			));
			$this->session->set_flashdata('success', lang('finance_add_fill_up_requests_success'));
			redirect('profile/fill_up_requests', 'refresh');

		}

		load_views();
	}
 */
	function withdrawal_requests() {
		if ($this->data['user_info']['is_seller']) {
			redirect('personal/edit_profile');
		}

		$this->data['title'] = $this->data['header'] = lang('finance_withdrawal_requests');

		$this->load->library('table');
		$this->table
			->text('id', array(
				'func'  => function($row, $params) {
					return '№'.$row['id'];
				}
		))
			->text('name')
			->text('number')
			->text('amount', array(
				'title' => lang('price'),
				'func'  => function($row, $params) {
					return '<div class="price"><i class="c_icon_label"></i>'.floatval($row['amount']).' '.$row['symbol'].'</div>';
				}
		))
			->text('commission', array(
				'title' => lang('commission'),
				'func'  => function($row, $params) {
					return '<div class="price">'.lang('commission').': <i class="c_icon_label"></i>-'.floatval($row['commission']).' '.$row['symbol'].'</div>';
				}
		))
			->date('add_date', array(
				'title' => lang('date'),
			))
			->btn(array(
				'link'  => 'profile/delete_withdrawal_request/%d',
				'class' => 'delete',
				'title' => lang('delete'),
				'modal' => true,
			))
			;
		$this->data['center_block'] = $this->table
			->create(function($CI) {
				return $CI->shop_model->get_payment_requests('withdraw');
			}, array('no_header' => 1, 'class' => 'table product_list orders'));


		$this->data['center_block'] .= $this->form
			->select('name', array(
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => lang('finance_account_name'),
				'options'     => array('Webmoney' => 'Webmoney', 'Paxum' => 'Paxum'),
			))
			->text('number', array('valid_rules' => 'required|trim|xss_clean|max_length[70]', 'label' => lang('finance_account_number')))
			->text('amount', array(
				'valid_rules' => 'required|trim|xss_clean|price',
				'label'       => lang('product_amount'),
			))
			->btn(array('value' => lang('add')))
			->create(array('action' => current_url(), 'error_inline' => 'true'));

		//Commission
		$commission = defined('WITHDRAWAL_COMMISSION') ? WITHDRAWAL_COMMISSION : 0;
		set_alert('<b>'.lang('finance_withdrawal_commission_message').': '.$commission.'%</b>', false, 'warning');


		if ($this->form_validation->run() != FALSE) {
			//Requests sum
			$exist_requests = $this->shop_model->get_payment_requests('withdraw')->result_array();
			$request_sum = 0;
			if (!empty($exist_requests)) {
				foreach ($exist_requests as $item) {
					$request_sum += $item['amount'] + $item['commission'];
				}
			}

			$user_balance = $this->shop_model->get_user_balance();
			if ($this->input->post('amount') + $commission + $request_sum > $user_balance[0]['amount']) {
				$this->session->set_flashdata('danger', lang('finance_no_money_message'));
				redirect('profile/withdrawal_requests', 'refresh');
			}

			$this->db->insert('shop_user_payment_requests', array(
				'type'       => 'withdraw',
				'name'       => $this->input->post('name'),
				'number'     => $this->input->post('number'),
				'amount'     => $this->input->post('amount'),
				'currency'   => 1,
				'commission' => $this->input->post('amount') / 100 * $commission,
				'user_id'    => $this->data['user_info']['id'],
				'add_date'   => time(),
			));
			$this->session->set_flashdata('success', lang('finance_add_withdrawal_requests_success'));
			redirect('profile/withdrawal_requests', 'refresh');
		}

		load_views();
	}

	function delete_withdrawal_request($id = false) {
		$id = intval($id);
		$request_info = $this->db
			->where(array(
				'id'      => $id,
				'user_id' => $this->data['user_info']['id'],
				'status'  => 0,
			))
			->get('shop_user_withdrawal_requests')
			->row_array();

		if (empty($request_info)) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';
				exit;
			} else {
				redirect('profile/payment_accounts', 'refresh');
			}
		}

		$this->data['title'] = $this->data['header'] = lang('finance_delete_withdrawal_request').' №'.$request_info['id'];

		if ($this->input->is_ajax_request()) {
			if (isset($_POST['delete'])) {
				$this->db->where('id', $id)->update('shop_user_withdrawal_requests', array('status' => 2));
				$this->session->set_flashdata('success', lang('finance_delete_withdrawal_request_success'));
				echo 'refresh';
			} else {
				$this->load->library('form');
				$this->data['center_block'] = $this->form
					->btn(array('name' => 'cancel', 'value' => lang('cancel'), 'class' => 'btn-default', 'modal' => 'close'))
					->btn(array('name' => 'delete', 'value' => lang('delete'), 'class' => 'btn-danger'))
					->create(array('action' => current_url(), 'btn_offset' => 3));
				echo $this->load->view('ajax', $this->data, true);
			}
		} else {
			$this->db->where('id', $id)->update('shop_user_withdrawal_requests', array('status' => 2));
			$this->session->set_flashdata('success', lang('finance_delete_withdrawal_request_success'));
			redirect('profile/withdrawal_requests', 'refresh');
		}
	}

}
