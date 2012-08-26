<?php
class User extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
	}

	public function index()
	{
		$data['user'] = $this->user_model->get_user();
		$data['title'] = 'Users list';

		$this->load->view('templates/header', $data);
		$this->load->view('user/index', $data);
		$this->load->view('templates/footer');
	}

	public function view($username)
	{
		$data['user'] = $this->user_model->get_user($username);
		if (empty($data['user']))
		{
			show_404();
		}

		$data['title'] = $data['user']['username'];

		$this->load->view('templates/header', $data);
		$this->load->view('user/view', $data);
		$this->load->view('templates/footer');
	}

  public function register(){
    // LOAD LIBRARIES
    $this->load->library(array('encrypt', 'form_validation', 'session'));
    // LOAD HELPERS
    $this->load->helper(array('form'));
		$this->load->library('form_validation');

		if(!$this->session->userdata('logged_in')){ //user not logged in
			$this->form_validation->set_rules('username', 'Username', 
				'trim|required|xss_clean|min_length[4]|check_username|is_unique[user.username]');
			$this->form_validation->set_rules('name', 'Name', 'required');
			$this->form_validation->set_rules('password', 'Password', 
				'trim|required|xss_clean|min_length[5]|matches[repeatPassword]');
			$this->form_validation->set_rules('repeatPassword', 'Password', 'trim|required|xss_clean');
			$this->form_validation->set_rules('email', 'Email', 'required|valid_email');

			if($this->form_validation->run() === FALSE){ 
				//Form has errors or it hasnt been filled yet
	      $data['title'] = "Register";
	      $this->load->view('templates/header', $data);
	      $this->load->view('user/register');
	      $this->load->view('templates/footer');
			}
			else{
				$this->user_model->set_user();
    		$result = $this->user_model->login($this->input->post('username'), $this->input->post('password'));
				$sess_array = array();
	      foreach($result as $row){
	        $sess_array = array(
	          'username' => $row->username,
	          'name' => $row->name,
	          'logged_in' => TRUE
	        );
      	}
        $this->session->set_userdata($sess_array);
	      $data['title'] = "Home area";
	      $this->load->view('templates/header', $data);
				$this->load->view('user/logged');
	      $this->load->view('templates/footer');
			}
		}
		else{
			//user already logged
      $data['title'] = "Home area";
      $this->load->view('templates/header', $data);
			$this->load->view('user/logged');
      $this->load->view('templates/footer');
		}
  }
  public function login(){
    // LOAD LIBRARIES
    $this->load->library(array('encrypt', 'form_validation'));
    // LOAD HELPERS
    $this->load->helper(array('form'));
		$this->load->library('form_validation');

		if(!$this->session->userdata('logged_in')){ //user not logged
			$this->form_validation->set_rules('username', 'Username', 
				'trim|required|xss_clean');
			$this->form_validation->set_rules('password', 'Password', 
				'trim|required|xss_clean|callback_check_database');
			//this validation checks if username and password exists at the database
			if($this->form_validation->run() === FALSE){ 
				//Form has errors or it hasnt been filled yet
	      $data['title'] = "Login";
	      $this->load->view('templates/header', $data);
	      $this->load->view('user/login_form');
	      $this->load->view('templates/footer');
			}
			else{
	      $data['title'] = "Home area";
	      $this->load->view('templates/header', $data);
				$this->load->view('user/logged');
	      $this->load->view('templates/footer');
			}
		}
		else{ //user already logged
      $data['title'] = "Home area";
      $this->load->view('templates/header', $data);
			$this->load->view('user/logged');
      $this->load->view('templates/footer');
		}

  }

	public function enter_facebook(){
		$config = array();
		$config['appId'] = '247496425267900';
		$config['secret'] = '920f899b5259a4f7bc2085b3203c46f4';
		// $config['appId'] = '198457386950891';
		// $config['secret'] = 'e8ee3a1c0d14ad74d26eb55e88497686';
		$config['fileUpload'] = false;
		$this->load->library('facebook/facebook',$config);
		$user = $this->facebook->getUser();
		if ($user) {
      try {
      	$user_info = $this->facebook->api('/me');
      	$user = $this->user_model->get_user_by_fbid($user_info['id']);
      	$unique_mail = $this->check_email($user_info['email']);
				if(!$user and $unique_mail){
					$this->user_model->set_facebook_user($user_info);
      		$user = $this->user_model->get_user_by_fbid($user_info['id']);
				}elseif (!$unique_mail){
			    $this->load->helper(array('form'));
					$this->load->library('form_validation');
					$this->session->set_flashdata('alert', 'Email address already used');
		      $data['title'] = "Login";
		      $this->load->view('templates/header', $data);
		      $this->load->view('user/register');
		      $this->load->view('templates/footer');
					return;
				}
        $sess_array = array(
          'username' => $user['username'],
          'name' => $user['name'],
          'logged_in' => TRUE
        );
        $this->session->set_userdata($sess_array);
	      $data['title'] = "Home area";
	      $this->load->view('templates/header', $data);
				$this->load->view('user/logged');
	      $this->load->view('templates/footer');
      } catch (FacebookApiException $e) {
        $user = null;
      }
    }

    if ($user) {
      $data['logout_url'] = $this->facebook->getLogoutUrl();
    } else {
      $data['login_url'] = $this->facebook->getLoginUrl(array(
				'scope'		=> 'email,user_birthday',
				'redirect_uri'	=> site_url("facebook_login")
			));
			redirect($data['login_url']);
    }
	}

  public function check_username($username){
    $result = $this->user_model->get_user($username);
    if($result){
      foreach($result as $row){
      	$this->form_validation->set_message('check_username', 'Username already used');
      	return FALSE;
      }
    }
    else return TRUE;
  }

  public function check_email($email){
    $result = $this->user_model->get_user_by_mail($email);
    if($result) return FALSE;
    else return TRUE;
  }

  public function check_database($password){
    $username = $this->input->post('username');
    $result = $this->user_model->login($username, $password);
    if($result){
      $sess_array = array();
      foreach($result as $row){
        $sess_array = array(
          'username' => $row->username,
          'name' => $row->name,
          'logged_in' => TRUE
        );
        $this->session->set_userdata($sess_array);
      }
      return TRUE;
    }
    else{
      $this->form_validation->set_message('check_database', 'Invalid username or password');
      return false;
    }
  }
  public function logout(){
		$this->session->unset_userdata('logged_in');
		$this->session->unset_userdata('username');
		$this->session->unset_userdata('name');
		$this->session->unset_userdata();
  	$this->session->sess_destroy();
  	$data['title'] = "Logged out";
    $this->load->view('templates/header', $data);
		$this->load->view('user/logout');
    $this->load->view('templates/footer');
  }
}