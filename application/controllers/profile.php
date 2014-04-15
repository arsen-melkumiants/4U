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
		//$this->data['left_block'] = $this->shop_model->get_categories();
		$this->data['left_block'] = $this->load->view('profile/menu', $this->data, true);
		$this->data['user_info'] = $this->ion_auth->user()->row_array();

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	//redirect if needed, otherwise display the user list
	function index() {
		$this->data['title'] = $this->data['header'] = 'My profile';

		$allowed_fields = array_flip(array('username','email','active','company','address','city','state','country','zip','phone'));
		foreach ($this->data['user_info'] as $key => $field) {
			if(!isset($allowed_fields[$key])) {
				unset($this->data['user_info'][$key]);
			}
		}

		$this->data['center_block'] = $this->load->view('profile/info', $this->data, true);

		load_views();
	}

	function cash() {
		$this->data['title'] = $this->data['header'] = 'My cash';

		load_views();
	}

	function products($type = 'active') {
		$this->data['title'] = $this->data['name'] = 'My products';
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
		));
		if ($type == 'moderate') {
			$this->table
				->btn(
					array(
						'func' => function($row, $params, $html, $that, $CI) {
							if ($row['status'] == 0) {
								return '<span class="label label-default">Pending</span>';
							} elseif ($row['status'] == 2) {
								return '<span class="label label-danger">Rejected</span>';
							}
						}
			));
		}

		$this->table
			->btn(array(
				'link'  => 'profile/edit_product/%d',
				'class' => 'edit',
				'title' => 'Edit',
			))
			->btn(array(
				'link'  => 'profile/delete_product/%d',
				'class' => 'delete',
				'title' => 'Delete',
				'modal' => true,
			))
			;
		$this->data['table'] = $this->table
			->create(function($CI) {
				return $CI->db
					->select('p.*, c.symbol, c.code')
					->from('shop_products as p')
					->join('shop_currencies as c', 'p.currency = c.id')
					->where(array(
						'p.author_id' => $CI->data['user_info']['id'],
					))
					->where_in('p.status', $CI->data['type_list'][$CI->data['type']])
					->get();
			}, array('no_header' => 1, 'class' => 'table'));

		$this->data['center_block'] = $this->load->view('profile/products', $this->data, true);

		load_views();
	}


	function add_product() {
		$this->data['title'] = $this->data['header'] = 'Add product';
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
			$this->session->set_flashdata('success', 'Продукт успешно добавлен и ожидает модерации');
			redirect('profile/product_gallery', 'refresh');
		}
	}

	function edit_product($id = false) {
		$id = intval($id);
		$product_info = $this->shop_model->get_product_by_user($id, $this->data['user_info']['id']);
		if (empty($product_info)) {
			redirect('profile/products', 'refresh');
		}

		$this->data['title'] = $this->data['header'] = 'Edit product "'.$product_info['name'].'"';
		$this->data['center_block'] = $this->edit_form($product_info);

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
				$info['amount'] = $this->input->post('amount');
			}

			foreach ($info as $key => $item) {
				if (isset($product_info[$key]) && $product_info[$key] != $item) {
					$info['status'] = 0;
					break;
				}
			}

			if ($product_info['is_locked']) {
				set_alert('Редактирование данного продукта заблокированно в свзяи с выполенинем заказа по нему', false, 'warning');
			}

			if (isset($info['status']) && !$product_info['is_locked'])	{
				$this->db->where('id', $id)->update('shop_products', $info);
				$this->session->set_flashdata('success', 'Продукт успешно добавлен и ожидает модерации');
			}
			redirect(current_url(), 'refresh');
		}

	}

	private function edit_form($product_info = false) {
		$product_types = array(
			'media'    => 'Media files',
			'licenses' => 'Licenses',
		);
		$product_categories = $this->shop_model->get_product_categories();
		array_unshift($product_categories, array('id' => '', 'name' => 'Без категории'));
		$this->load->library('form');
		$this->form
			->text('name', array(
				'value'       => $product_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Name',
			))
			->text('price', array(
				'value'       => $product_info['price'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|price',
				'symbol'      => '$',
				'icon_post'   => true,
				'label'       => 'Price',
			));
		if (!empty($product_info)) {
			$this->form
				->text('type', array(
					'value'       => $product_info['type'],
					'valid_rules' => 'trim|xss_clean',
					'label'       => 'Type',
					'readonly'    => true,
				));
			if ($product_info['type'] != 'licenses') {
				$this->form
					->text('amount', array(
						'value'       => $product_info['amount'],
						'valid_rules' => 'required|trim|xss_clean|is_natural',
						'label'       => 'Amount',
					));
			}
		} else {
			$this->form
				->radio('type', array(
					'inputs' => $product_types,
					'label'  => 'Type of product (license type sets amount of product according amount of files automatically)',
				))
				->text('amount', array(
					'value'       => 0,
					'valid_rules' => 'required|trim|xss_clean|is_natural',
					'label'       => 'Amount',
				));
		}
		$this->form
			->select('cat_id', array(
				'value'       => $product_info['cat_id'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Category',
				'options'     => $product_categories,
				'search'      => true,
			))
			->textarea('content', array(
				'value'       => $product_info['content'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Content',
			))
			->btn(array('value' => empty($product_info) ? 'Add' : 'Update'));
		if (!empty($product_info)) {
			$this->form
				->link(array('name' => 'Gallery', 'href' => site_url('profile/product_gallery/'.$product_info['id'])))
				->link(array('name' => 'Media content', 'href' => site_url('profile/product_media_files/'.$product_info['id'])));
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
				$this->session->set_flashdata('danger', 'Удаление данного продукта заблокированно в свзяи с выполенинем заказа по нему');
			}
			if ($this->input->is_ajax_request()) {
				echo 'refresh';
				exit;
			} else {
				redirect('profile/products', 'refresh');
			}
		}

		$this->data['title'] = $this->data['header'] = 'Deleting of "'.$product_info['name'].'"';

		if ($this->input->is_ajax_request()) {
			if (isset($_POST['delete'])) {
				$this->db->where('id', $id)->update('shop_products', array('status' => 3));
				$this->session->set_flashdata('success', 'Удаление успешно выполено');
				echo 'refresh';
			} else {
				$this->load->library('form');
				$this->data['center_block'] = $this->form
					->btn(array('name' => 'cancel', 'value' => 'Cancel', 'class' => 'btn-default', 'modal' => 'close'))
					->btn(array('name' => 'delete', 'value' => 'Delete', 'class' => 'btn-danger'))
					->create(array('action' => current_url(), 'btn_offset' => 3));
				echo $this->load->view('ajax', $this->data, true);
			}
		} else {
			$this->db->where('id', $id)->update('shop_products', array('status' => 3));
			$this->session->set_flashdata('success', 'Удаление успешно выполено');
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
		$this->delete_media_files($id, 'image');
	}

	function product_media_files($id = false, $type = false) {
		$id = intval($id);
		$product_info = $this->shop_model->get_product_by_user($id, $this->data['user_info']['id']);
		if (empty($product_info)) {
			redirect('profile/products', 'refresh');
		}

		if ($type == 'image') {
			$this->data['title'] = $this->data['name'] = 'Product gallery';
			$this->data['upload_url'] = base_url('profile/upload_gallery/'.$id);
		} else {
			$this->data['title'] = $this->data['name'] = 'Product media content files';
			$this->data['upload_url'] = base_url('profile/upload_media_files/'.$id);
		}

		if ($product_info['is_locked']) {
			set_alert('Редактирование данного продукта заблокированно в свзяи с выполенинем заказа по нему', false, 'warning');
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

		if ($type == 'image') {
			$upload_path_url = base_url('uploads/gallery').'/';
			$config['upload_path'] = FCPATH.'uploads/gallery';
			$config['allowed_types'] = 'jpg|jpeg|png|gif';
			$config['max_size'] = '1000000';
		} else {
			$upload_path_url = base_url('media_files').'/';
			$config['upload_path'] = FCPATH.'media_files';
			if ($product_info['type'] == 'licenses') {
				$config['allowed_types'] = 'jpg|jpeg|png|gif';
			} else {
				$config['allowed_types'] = 'jpg|jpeg|png|gif|avi|mp4';
			}
			$config['max_size']      = '10000000000';
		}
		@mkdir($config['upload_path'], 0777, true);
		$config['file_name'] = !empty($_FILES['userfile']) ? $_FILES['userfile']['size'] : false;

		$this->load->helper('file');
		$this->load->library('upload');
		$this->upload->initialize($config);

		$files = array();

		if (!$this->upload->do_upload() || $product_info['is_locked']) {
			$error = $this->upload->display_errors();
			if ($product_info['is_locked']) {
				$error = 'Редактирование данного продукта заблокированно в свзяи с выполенинем заказа по нему';
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
					$files[] = array(
						'name'         => $item['file_name'],
						'url'          => $upload_path_url.$item['file_name'],
						'thumbnailUrl' => $type == 'image' ? $upload_path_url.'small_thumb/'.$item['file_name'] : $upload_path_url.$item['file_name'],
						'deleteUrl'    => base_url().'profile/delete_'.$type.'/'.$item['id'],
						'deleteType'   => 'POST',
						'error'        => null,
					);
				}
			}

		} else {
			$data = $this->upload->data();
			if ($type == 'image') {
				$file_id = $this->shop_model->add_product_image($id, $data);
				$this->load->library('image_lib');
				$this->resize_image($data, $new_width = 200, 'small_thumb');
			} else {
				$file_id = $this->shop_model->add_product_file($id, $data);
			}

			$files[] = array(
				'name'         => $data['file_name'],
				'url'          => $upload_path_url.$data['file_name'],
				'thumbnailUrl' => $type == 'image' ? $upload_path_url.'small_thumb/'.$data['file_name'] : $upload_path_url.$data['file_name'],
				'deleteUrl'    => base_url().'profile/delete_'.$type.'/'.$file_id,
				'deleteType'   => 'POST',
				'error'        => null,
			);
		}

		if ($this->input->is_ajax_request()) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(array('files' => $files)));
		}	
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
		$file = $product_file['file_name'];

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
}
