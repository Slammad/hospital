<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Receptionist extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    function index() 
    {
        if ($this->session->userdata('receptionist_login') != 1)
        {
            $this->session->set_userdata('last_page' , current_url());
            redirect(base_url(), 'refresh');
        }
        
        $data['page_name']      = 'dashboard';
        $data['page_title']     = get_phrase('receptionist_dashboard');
        $this->load->view('backend/index', $data);
    }
    
    function appointment($task = "", $doctor_id = 'all', $start_timestamp = "", $end_timestamp = "")
    {
        if ($this->session->userdata('receptionist_login') != 1)
        {
            $this->session->set_userdata('last_page' , current_url());
            redirect(base_url(), 'refresh');
        }
        
        if ($task == 'filter')
        {
            $doctor_id          = $this->input->post('doctor_id');
            $start_timestamp    = strtotime($this->input->post('start_timestamp'));
            $end_timestamp      = strtotime($this->input->post('end_timestamp'));
            redirect('index.php?receptionist/appointment/search/' . $doctor_id . '/' . $start_timestamp . '/' . $end_timestamp);
        }
        
        if ($task == "create")
        {
            $this->crud_model->save_appointment_info();
            $this->session->set_flashdata('message' , get_phrase('appointment_info_saved_successfuly'));
            redirect('index.php?receptionist/appointment');
        }
        
        $data['doctor_id'] = $doctor_id;
        if($start_timestamp == '')
            $data['start_timestamp']    = strtotime('today - 30 days');
        else
            $data['start_timestamp']    = $start_timestamp;
        if($end_timestamp == '')
            $data['end_timestamp']      = strtotime('today');
        else
            $data['end_timestamp']      = $end_timestamp;
        
        $data['appointment_info']   = $this->crud_model->select_appointment_info($doctor_id, $data['start_timestamp'], $data['end_timestamp']);
        $data['page_name']          = 'show_appointment';
        $data['page_title']         = get_phrase('appointment');
        $this->load->view('backend/index', $data);
    }
    
    function appointment_requested($task = "", $appointment_id = "")
    {
        if ($this->session->userdata('receptionist_login') != 1)
        {
            $this->session->set_userdata('last_page' , current_url());
            redirect(base_url(), 'refresh');
        }
        
        if ($task == "approve")
        {
            $this->crud_model->approve_appointment_info($appointment_id);
            $this->session->set_flashdata('message' , get_phrase('appointment_info_approved'));
            redirect('index.php?receptionist/appointment_requested');
        }
        
        $data['requested_appointment_info'] = $this->crud_model->select_requested_appointment_info();
        $data['page_name']                  = 'manage_requested_appointment';
        $data['page_title']                 = get_phrase('requested_appointment');
        $this->load->view('backend/index', $data);
    }
    
    function profile($task = "")
    {
        if ($this->session->userdata('receptionist_login') != 1)
        {
            $this->session->set_userdata('last_page' , current_url());
            redirect(base_url(), 'refresh');
        }
        
        $receptionist_id      = $this->session->userdata('login_user_id');
        if ($task == "update")
        {
            $this->crud_model->update_receptionist_info($receptionist_id);
            $this->session->set_flashdata('message' , get_phrase('profile_info_updated_successfuly'));
            redirect('index.php?receptionist/profile');
        }
        
        if ($task == "change_password")
        {
            $password               = $this->db->get_where('receptionist', array('receptionist_id' => $receptionist_id))->row()->password;
            $old_password           = sha1($this->input->post('old_password'));
            $new_password           = $this->input->post('new_password');
            $confirm_new_password   = $this->input->post('confirm_new_password');
            
            if($password==$old_password && $new_password==$confirm_new_password)
            {
                $data['password'] = sha1($new_password);
                
                $this->db->where('receptionist_id',$receptionist_id);
                $this->db->update('receptionist',$data);
                
                $this->session->set_flashdata('message' , get_phrase('password_info_updated_successfuly'));
                redirect('index.php?receptionist/profile');
            }
            else
            {
                $this->session->set_flashdata('message' , get_phrase('password_update_failed'));
                redirect('index.php?receptionist/profile');
            }
        }
        
        $data['page_name']      = 'edit_profile';
        $data['page_title']     = get_phrase('profile');
        $this->load->view('backend/index', $data);
    }
}