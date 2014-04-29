<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_product extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $DB_TABLE = 'shop_products';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Все продукты',
			'header_descr' => 'Список продуктов',
		),
		'moderate'         => array(
			'header'       => 'Продукты на модерацию',
			'header_descr' => 'Список продуктов',
		),
		'activated'        => array(
			'header'       => 'Активные продукты',
			'header_descr' => 'Список продуктов',
		),
		'rejected'         => array(
			'header'       => 'Продукты непрошедшие модерацию',
			'header_descr' => 'Список продуктов',
		),
		'add'              => array(
			'header'       => 'Добавления продукта',
			'header_descr' => 'Информация о продукте',
		),
		'edit'             => array(
			'header'       => 'Редактирование "%name"',
			'header_descr' => 'Редактирование информации о продукте',
		),
		'delete'           => array(
			'header'       => 'Удаление продукта "%name"',
			'header_descr' => false,
		),
	);

	function __construct() {
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_product_model');
		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();
	}

	public function index($status = false) {
		$product_categories = $this->admin_product_model->get_product_categories();

		$this->data['status'] = $status;

		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title'   => 'Имя',
				'p_width' => 50
			))
			->text('cat_id', array(
				'title' => 'Категория',
				'extra' => $product_categories ,
				'func'  => function($row, $params) {
					if (isset($params['extra'][$row['cat_id']]['name'])) {
						return '<span class="label label-info">'.$params['extra'][$row['cat_id']]['name'].'</span>';
					} else {
						return '<span class="label label-warning">Отсутствует</span>';
					}
				}
		))
			->date('add_date', array(
				'title' => 'Дата создания'
			))
			->edit(array('link' => $this->MAIN_URL.'edit/%d'))
			->delete(array('link' => $this->MAIN_URL.'delete/%d', 'modal' => 1))
//			->active(array('link' => $this->MAIN_URL.'active/%d'))
			/*->btn(array(
				'link'   => $this->MAIN_URL.'add',
				'name'   => 'Добавить',
				'header' => true,
			))*/
			->create(function($CI) {
				return $CI->admin_product_model->get_all_products($CI->data['status']);
			});

		load_admin_views();
	}

	public function moderate() {
		$this->index(0);
	}

	public function activated() {
		$this->index(1);
	}

	public function rejected() {
		$this->index(2);
	}

	public function add() {
		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('add', $this->DB_TABLE);
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$product_info = $this->admin_product_model->get_product_info($id);

		if (empty($product_info )) {
			custom_404();
		}
		set_header_info($product_info);

		$this->data['center_block'] = $this->edit_form($product_info);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('edit', $this->DB_TABLE, array('id' => $id));
		}
	}

	private function edit_form($product_info = false) {
		$product_categories = $this->admin_product_model->get_product_categories();
		array_unshift($product_categories, array('id' => 0, 'name' => 'Без категории'));
		$currencies = $this->admin_product_model->get_currencies();
		array_unshift($currencies, array('id' => '', 'name' => 'Список валюты'));
		$this->load->library('form');
		return $this->form
			->text('name', array(
				'value'       => $product_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Имя',
			))
			->text('price', array(
				'value'       => $product_info['price'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|numeric',
				'symbol'	  => '$',
				'label'       => 'Цена',
			))
			/*->select('currency', array(
				'value'       => $product_info['currency'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Валюта',
				'options'     => $currencies,
				'search'      => true,
			))*/
			->select('cat_id', array(
				'value'       => $product_info['cat_id'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Категория',
				'options'     => $product_categories,
				'search'      => true,
			))
			->radio('recommended', array(
				'value'       => $product_info['recommended'] ?: false,
				'valid_rules' => 'trim|xss_clean|is_natural',
				'label'       => 'Рекомендуемый',
				'inputs'      => array('Нет', 'Да'),
			))
			->textarea('content', array(
				'value'       => $product_info['content'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Текст',
			))
			->text('keywords', array(
				'value'       => $product_info['keywords'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Ключевые слова',
			))
			->text('title', array(
				'value'       => $product_info['title'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Заголовок страницы',
			))
			->text('description', array(
				'value'       => $product_info['description'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Описание',
			))
			->radio('status', array(
				'value'       => $product_info['status'] ?: false,
				'valid_rules' => 'trim|xss_clean|is_natural',
				'label'       => '<span class="text-danger">Модерация</span>',
				'inputs'      => array('Ожидание', 'Подтверждено', 'Отказано'),
			))
			->btn(array('value' => empty($product_info) ? 'Добавить' : 'Изменить'))
			->link(array('name' => 'Галерея', 'href' => site_url($this->MAIN_URL.'gallery/'.$product_info['id'])))
			->link(array('name' => 'Медиа контент', 'href' => site_url($this->MAIN_URL.'media_files/'.$product_info['id'])))
			->create(array('action' => current_url()));
	}

	public function delete($id = false, $type = false) {
		if (empty($id)) {
			custom_404();
		}

		$product_info = $this->admin_product_model->get_product_info($id);

		if (empty($product_info)) {
			custom_404();
		}
		set_header_info($product_info);
		
		if ($this->IS_AJAX) {
			if (isset($_POST['delete'])) {
				$this->db->where('id', $id)->update($this->DB_TABLE, array('status' => 3));
				$this->session->set_flashdata('danger', 'Удаление успешно выполено');
				echo 'refresh';
			} else {
				$this->load->library('form');
				$this->data['center_block'] = $this->form
					->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
					->btn(array('name' => 'delete', 'value' => 'Удалить', 'class' => 'btn-danger'))
					->create(array('action' => current_url(), 'btn_offset' => 4));
				echo $this->load->view(ADM_FOLDER.'ajax', '', true);
			}
		} else {
			$this->db->where('id', $id)->update($this->DB_TABLE, array('status' => 3));
			$this->session->set_flashdata('danger', 'Удаление успешно выполено');
			redirect($this->MAIN_URL, 'refresh');
		}

	}

	public function active($id = false) {
		if (empty($id)) {
			custom_404();
		}

		$product_info = $this->admin_product_model->get_product_info($id);

		if (empty($product_info)) {
			custom_404();
		}
		set_header_info($product_info);

		admin_method('active', $this->DB_TABLE, $product_info);
	}

	function gallery($id = false) {
		$this->media_files($id, 'image');
	}

	function upload_gallery($id = false) {
		$this->upload_media_files($id, 'image');
	}

	function media_files($id = false, $type = false) {
		$id = $this->data['id'] = intval($id);
		$this->data['type'] = $type;
		$product_info = $this->admin_product_model->get_product_info($id);
		if (empty($product_info)) {
			redirect($this->MAIN_URL, 'refresh');
		}

		if ($type == 'image') {
			$this->data['title'] = $this->data['name'] = 'Галерея';
			$this->data['upload_url'] = base_url($this->MAIN_URL.'upload_gallery/'.$id);
		} else {
			$this->data['title'] = $this->data['name'] = 'Медиа контент';
			$this->data['upload_url'] = base_url($this->MAIN_URL.'upload_media_files/'.$id);
		}

		$this->data['center_block'] = $this->load->view(ADM_FOLDER.'upload', $this->data, true);
		load_admin_views();
	}

	function upload_media_files($id = false, $type = 'file') {
		$id = intval($id);
		$product_info = $this->admin_product_model->get_product_info($id);
		if (empty($product_info)) {
			redirect($this->MAIN_URL, 'refresh');
		}
		$this->load->model('shop_model');

		if ($type == 'image') {
			$upload_path_url = base_url('uploads/gallery').'/';
		} else {
			$upload_path_url = base_url('media_files').'/';
		}

		$files = array();

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
				'error'        => null,
				'sold'         => !empty($item['status'])
			);
		}

		if ($this->input->is_ajax_request()) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(array('files' => $files)));
		}	
	}

}
