<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manage_menu extends CI_Controller {
	public function __construct()
    {
		$CI =& get_instance();
        $CI->load->library('form_validation');
		$CI->load->helper('small');
    }
	
	
	public function control($name = false,$item_id = false,$action = false)
	{
		$CI =& get_instance();
		$data['main_url'] = base_url('4U/manage_menu/'.$name);
		$menu_info = $CI->admin_menu_model->get_menu_info($name);
		if(!$name && !$menu_info){
			show_404();
		}
		
		$menu_info = $menu_info->row();
		$data['top_menu'] = $CI->TOP_MENU;
		$data['sub_menu'] = $CI->SUB_MENU;
		
		if($action == 'move_up' && !empty($item_id)){
			$CI->admin_menu_model->move_up_menu_item($item_id, $menu_info->id);
			$CI->session->set_flashdata('success', 'Изменения успешно внесены');
			redirect($data['main_url'].'.html', 'refresh');
		}
		
		if($action == 'move_down' && !empty($item_id)){
			$CI->admin_menu_model->move_down_menu_item($item_id, $menu_info->id);
			$CI->session->set_flashdata('success', 'Изменения успешно внесены');
			redirect($data['main_url'].'.html', 'refresh');
		}

		$all_branch = $CI->admin_menu_model->get_all_menu($menu_info->id)->result();
		
		if($action == 'delete' && !empty($item_id)){
			$CI->admin_menu_model->delete_menu_item($all_branch, $item_id);
			$CI->session->set_flashdata('success', 'Удаление успешно произведено');
			redirect($data['main_url'].'.html', 'refresh');
		}
		
		if($item_id == 'add' && !empty($_POST)){
			$this->add_menu($menu_info, $data['main_url']);
		}
		
		if($action == 'edit' && !empty($item_id)){
			$item_info = $CI->admin_menu_model->get_one_item_menu($menu_info->id,$item_id)->row();
			if(empty($item_info)){
				show_404();
			}
            
            foreach($all_branch as $key => $row){
                if($row->id == $item_id){
                    $all_branch[$key] = '';
                }
            }
            
			$data['menu_item'] = $item_info;
			$this->edit_menu($menu_info,$item_info, $data['main_url']);
			$data['title'] = '4U :: Редактировать пункт меню "'.$item_info->name.'"';
			$data['header'] = 'Пункт меню "'.$item_info->name.'"';
			$data['menu_items'] = select_tree($all_branch, !empty($_POST['parent_id']) ? $_POST['parent_id'] : $item_info->parent_id);
			
			$CI->load->view($CI->VIEW_URL.'header',$data);
			$CI->load->view($CI->VIEW_URL.'top',$data);
			$CI->load->view($CI->VIEW_URL.'edit_menu_item',$data);
			$CI->load->view($CI->VIEW_URL.'footer');
		}else{
			
			$data['title'] = '4U :: Редактировать "'.mb_strtolower($menu_info->ru_name).'" меню';
			$data['header'] = $menu_info->ru_name.' меню';
			$data['menu_tree'] = $CI->admin_menu_model->get_menu_tree($all_branch,0,'',$data['main_url']);
			$data['menu_items'] = select_tree($all_branch, !empty($_POST['parent_id']) ? $_POST['parent_id'] : 0);
				
			$CI->load->view($CI->VIEW_URL.'header',$data);
			$CI->load->view($CI->VIEW_URL.'top',$data);
			$CI->load->view($CI->VIEW_URL.'manage_menu',$data);
			$CI->load->view($CI->VIEW_URL.'footer');
		}

	}
	
	public function add_menu($menu_info, $main_url){
			$CI =& get_instance();
			if(isset($_POST['link_name'])){
				$_POST['link_name'] = url_title(translitIt($_POST['link_name']), 'underscore', TRUE);
			}
			
			$CI->form_validation->set_rules('name', 'Название', 'required|trim');
			$CI->form_validation->set_rules('link_name', 'Адрес', 'required|is_unique[menu_items.link_name]|trim');
			$CI->form_validation->set_rules('parent_id', 'Родитель', 'required|is_natural|trim');
			
			if ($CI->form_validation->run() != FALSE){
				$info = $CI->input->post();
				$info['parent_menu'] = $menu_info->id;
				$CI->admin_menu_model->add_menu_item($info,$menu_info->id);
				$CI->session->set_flashdata('success', 'Создание успешно завершено');
				redirect($main_url.'.html', 'refresh');
			}
	}
	
	public function edit_menu($menu_info, $item_info, $main_url){
			$CI =& get_instance();
			if(isset($_POST['link_name'])){
				$_POST['link_name'] = url_title(translitIt($_POST['link_name']), 'underscore', TRUE);
			}
			
			$CI->form_validation->set_rules('name', 'Название', 'required|trim');
			$CI->form_validation->set_rules('link_name', 'Адрес', 'required|trim|is_unique_without[menu_items.link_name.'.$item_info->id.']');
			$CI->form_validation->set_rules('parent_id', 'Родитель', 'required|is_natural|trim');
			
			if ($CI->form_validation->run() != FALSE){
				$update_info = $CI->input->post();
				$update_info['parent_menu'] = $menu_info->id;
				$CI->admin_menu_model->update_menu_item($update_info, $item_info, $menu_info->id);
				$CI->session->set_flashdata('success', 'Редактирование успешно завершено');
				redirect($main_url.'.html', 'refresh');
			}
	}
	
}
