<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tickets extends MY_Controller {
	const NB_PAR_PAGE = 20;

	function __construct(){
		parent::__construct();
		$this->layout->set_theme('default');
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		// $this->output->enable_profiler(TRUE);
	}

	function index($g_nb = 0) {
		$this->layout->set_title('Vos tickets');
		$this->data['h2'] = 'Vos tickets';

		$this->data['nb_total'] = $this->ticketManager->count_tickets(array('user_id' => $this->session->userdata('id')));

		if ($g_nb >= 1){
			if ($g_nb <= $this->data['nb_total']){
				$nb = intval($g_nb);
			}else{
				$nb = self::NB_PAR_PAGE;
			}
		}else{
			$nb = 1;
		}
		$nb -= 1;

		$config['base_url'] = site_url('admin/tickets/page');
		$config['uri_segment'] = 4;
		$config['total_rows'] = $this->data['nb_total'];
		$config['per_page'] = self::NB_PAR_PAGE;
		$config['num_links'] = 3;
		$config['first_link'] = '&laquo;';
		$config['first_url'] = '1';
		$config['prev_link'] = 'Précédent';
		$config['next_link'] = 'Suivant';
		$config['last_link'] = '&raquo;';
		$config['full_tag_open'] = '<ul>';
		$config['full_tag_close'] = '</ul>';
		$config['cur_tag_open'] = '<li class="active"><a href="javascript:;">';
		$config['cur_tag_close'] = '</a></li>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';
		$config['use_page_numbers'] = true;

		$this->pagination->initialize($config); 
		
		$this->data['pagination'] = $this->pagination->create_links();

		$start = self::NB_PAR_PAGE * $nb;

		$this->data['tickets'] = $this->ticketManager->get_all_tickets(array('user_id' => $this->session->userdata('id')), '', 'T.id DESC', self::NB_PAR_PAGE, $start);

		$this->layout->views('index', $this->data)->view();
	}

	function get($id) {
		$ticket = $this->ticketManager->get_ticket(array('T.id' => (int) $id, 'user_id' => $this->session->userdata('id')));

		if (!$ticket){
			redirect('tickets', 'refresh');
		}

		$this->data['ticket'] = $ticket;

		$this->layout->set_title($ticket->title);
		$this->data['h2'] = $ticket->title;

		$this->data['responses'] = $this->ticketManager->get_all_responses(array('ticket_id' => (int) $id), '', 'R.id ASC', 9999);

		$this->form_validation->set_rules('text','Texte','trim|required|xss_clean');
		if (!$this->form_validation->run()){
			$this->layout->views('view', $this->data)->view();
		}else{
			$options = array();
			$options['text'] = $this->input->post('text');
			$options['ticket_id'] = $ticket->id;
			$options['user_id'] = $this->session->userdata('id');
			$options['ip'] = $this->session->userdata('ip_address');
			$response = $this->ticketManager->create_response($options);

			if ($response){
				$attachment = $this->upload_attachment('attachment');
				if ($attachment){
					foreach ($attachment as $kA => $vA){
						$attachment[$kA] = (string) $vA;
					}
					$attachment['response_id'] = $response;
					$this->ticketManager->save_attachment($attachment);
				}

				$message = 'Une réponse a été postée par '.$this->session->userdata('lastname').' '.$this->session->userdata('firstname').' ('.$this->session->userdata('sitename').') : '.site_url('ticket/'.$ticket->id);

				$config['mailtype'] = 'html';
				$config['charset'] = 'UTF-8';
				$this->load->library('email');
				$this->email->initialize($config);
				$this->email->from($this->data['config']->noreply);
				$this->email->to($this->data['config']->system_email);
				$this->email->reply_to($this->session->userdata('email'), $this->session->userdata('lastname').' '.$this->session->userdata('firstname').' ('.$this->session->userdata('sitename').')');
				$this->email->subject('[REPONSE] '.$ticket->title);
				$this->email->message($message);
				@$this->email->send();

				redirect('ticket/'.$ticket->id.'#post-'.$response);
			}else{
				$this->layout->views('reply-error', $this->data)->view();
			}
		}
	}

	function open($g_nb = 0) {
		$this->layout->set_title('Vos tickets ouverts');
		$this->data['h2'] = 'Vos tickets ouverts';

		$this->data['nb_total'] = $this->ticketManager->count_tickets(array('state' => 'open', 'user_id' => $this->session->userdata('id')));

		if ($g_nb >= 1){
			if ($g_nb <= $this->data['nb_total']){
				$nb = intval($g_nb);
			}else{
				$nb = self::NB_PAR_PAGE;
			}
		}else{
			$nb = 1;
		}
		$nb -= 1;

		$config['base_url'] = site_url('admin/tickets/open/page');
		$config['uri_segment'] = 5;
		$config['total_rows'] = $this->data['nb_total'];
		$config['per_page'] = self::NB_PAR_PAGE;
		$config['num_links'] = 3;
		$config['first_link'] = '&laquo;';
		$config['first_url'] = '1';
		$config['prev_link'] = 'Précédent';
		$config['next_link'] = 'Suivant';
		$config['last_link'] = '&raquo;';
		$config['full_tag_open'] = '<ul>';
		$config['full_tag_close'] = '</ul>';
		$config['cur_tag_open'] = '<li class="active"><a href="javascript:;">';
		$config['cur_tag_close'] = '</a></li>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';
		$config['use_page_numbers'] = true;

		$this->pagination->initialize($config); 
		
		$this->data['pagination'] = $this->pagination->create_links();

		$start = self::NB_PAR_PAGE * $nb;

		$this->data['tickets'] = $this->ticketManager->get_all_tickets(array('state' => 'open', 'user_id' => $this->session->userdata('id')), '', 'T.id DESC', self::NB_PAR_PAGE, $start);

		$this->layout->views('index', $this->data)->view();
	}

	function closed($g_nb = 0) {
		$this->layout->set_title('Vos tickets fermés');
		$this->data['h2'] = 'Vos tickets fermés';

		$this->data['nb_total'] = $this->ticketManager->count_tickets(array('state' => 'close', 'user_id' => $this->session->userdata('id')));

		if ($g_nb >= 1){
			if ($g_nb <= $this->data['nb_total']){
				$nb = intval($g_nb);
			}else{
				$nb = self::NB_PAR_PAGE;
			}
		}else{
			$nb = 1;
		}
		$nb -= 1;

		$config['base_url'] = site_url('admin/tickets/closed/page');
		$config['uri_segment'] = 5;
		$config['total_rows'] = $this->data['nb_total'];
		$config['per_page'] = self::NB_PAR_PAGE;
		$config['num_links'] = 3;
		$config['first_link'] = '&laquo;';
		$config['first_url'] = '1';
		$config['prev_link'] = 'Précédent';
		$config['next_link'] = 'Suivant';
		$config['last_link'] = '&raquo;';
		$config['full_tag_open'] = '<ul>';
		$config['full_tag_close'] = '</ul>';
		$config['cur_tag_open'] = '<li class="active"><a href="javascript:;">';
		$config['cur_tag_close'] = '</a></li>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';
		$config['use_page_numbers'] = true;

		$this->pagination->initialize($config); 
		
		$this->data['pagination'] = $this->pagination->create_links();

		$start = self::NB_PAR_PAGE * $nb;

		$this->data['tickets'] = $this->ticketManager->get_all_tickets(array('state' => 'close', 'user_id' => $this->session->userdata('id')), '', 'T.id DESC', self::NB_PAR_PAGE, $start);

		$this->layout->views('index', $this->data)->view();
	}

	function create() {
		$this->layout->set_title('Nouveau ticket');
		$this->data['h2'] = 'Nouveau ticket';

		$this->form_validation->set_rules('title','Titre','trim|required|xss_clean');
		$this->form_validation->set_rules('text','Texte','trim|required|xss_clean');
		if (!$this->form_validation->run()){
			$this->layout->views('create', $this->data)->view();
		}else{
			$options = array();
			$options['title'] = $this->input->post('title');
			$options['text'] = $this->input->post('text');
			$options['user_id'] = $this->session->userdata('id');
			$options['ip'] = $this->session->userdata('ip_address');
			$valid = $this->ticketManager->create_ticket($options);

			if ($valid){
				$attachment = $this->upload_attachment('attachment');
				if ($attachment){
					foreach ($attachment as $kA => $vA){
						$attachment[$kA] = (string) $vA;
					}
					$attachment['ticket_id'] = $valid;
					$this->ticketManager->save_attachment($attachment);
				}

				$message = 'Un ticket a été ouvert par '.$this->session->userdata('lastname').' '.$this->session->userdata('firstname').' ('.$this->session->userdata('sitename').') : '.site_url('ticket/'.$valid);

				$config['mailtype'] = 'html';
				$config['charset'] = 'UTF-8';
				$this->load->library('email');
				$this->email->initialize($config);
				$this->email->from($this->data['config']->noreply);
				$this->email->to($this->data['config']->system_email);
				$this->email->reply_to($this->session->userdata('email'), $this->session->userdata('lastname').' '.$this->session->userdata('firstname').' ('.$this->session->userdata('sitename').')');
				$this->email->subject('[NOUVEAU] '.$options['title']);
				$this->email->message($message);
				@$this->email->send();

				redirect('ticket/'.$valid, 'refresh');
			}else{
				$this->layout->views('create-error', $this->data)->view();
			}
		}
	}

	function reopen($id) {
		$ticket = $this->ticketManager->get_ticket(array('T.id' => (int) $id, 'T.state' => 'close', 'user_id' => $this->session->userdata('id')));

		if (!$ticket){
			redirect('tickets', 'refresh');
		}

		$options = array();
		$options['id'] = $ticket->id;
		$options['state'] = 'open';

		$this->ticketManager->update_ticket($options);

		$message = 'Un ticket a été réouvert par '.$this->session->userdata('lastname').' '.$this->session->userdata('firstname').' ('.$this->session->userdata('sitename').') : '.site_url('ticket/'.$ticket->id);

		$config['mailtype'] = 'html';
		$config['charset'] = 'UTF-8';
		$this->load->library('email');
		$this->email->initialize($config);
		$this->email->from($this->data['config']->noreply);
		$this->email->to($this->data['config']->system_email);
		$this->email->reply_to($this->session->userdata('email'), $this->session->userdata('lastname').' '.$this->session->userdata('firstname').' ('.$this->session->userdata('sitename').')');
		$this->email->subject('[RE-OUVERTURE] '.$ticket->title);
		$this->email->message($message);
		@$this->email->send();

		redirect('ticket/'.$ticket->id, 'refresh');
	}

	function close($id) {
		$ticket = $this->ticketManager->get_ticket(array('T.id' => (int) $id, 'T.state' => 'open', 'user_id' => $this->session->userdata('id')));

		if (!$ticket){
			redirect('tickets', 'refresh');
		}

		$options = array();
		$options['id'] = $ticket->id;
		$options['state'] = 'close';

		$this->ticketManager->update_ticket($options);

		$message = 'Un ticket a été fermé par '.$this->session->userdata('lastname').' '.$this->session->userdata('firstname').' ('.$this->session->userdata('sitename').') : '.site_url('ticket/'.$ticket->id);

		$config['mailtype'] = 'html';
		$config['charset'] = 'UTF-8';
		$this->load->library('email');
		$this->email->initialize($config);
		$this->email->from($this->data['config']->noreply);
		$this->email->to($this->data['config']->system_email);
		$this->email->reply_to($this->session->userdata('email'), $this->session->userdata('lastname').' '.$this->session->userdata('firstname').' ('.$this->session->userdata('sitename').')');
		$this->email->subject('[RESOLU] '.$ticket->title);
		$this->email->message($message);
		@$this->email->send();

		redirect('ticket/'.$ticket->id, 'refresh');
	}

	function search(){
		if (!$this->input->post('search')){
			redirect('/', 'refresh');
		}

		$search = $this->input->post('search');

		$searches = array();
		$query = $this->ticketManager->search_tickets($search);
		foreach ($query->result_array() as $ticket){
			$searches[$ticket['id']] = $ticket;
		}
		$query = $this->ticketManager->search_responses($search);
		foreach ($query->result_array() as $response){
			if (!array_key_exists($response['id'], $searches)){
				$searches[$response['id']] = $response;
			}
		}
		krsort($searches);
		$this->data['search'] = $search;
		$this->data['tickets'] = $searches;

		$this->layout->views('search', $this->data)->view();
	}

	function get_attachment($from, $id){
		if ($from == 'ticket'){
			$item = $this->ticketManager->get_ticket(array('T.id' => (int) $id, 'user_id' => $this->session->userdata('id')));
		}else if ($from == 'response'){
			$item = $this->ticketManager->get_response(array('R.id' => (int) $id, 'user_id' => $this->session->userdata('id')));
		}

		if (!$item){
			redirect('tickets', 'refresh');
		}

		$this->load->helper('download');
		force_download($item->file_name, file_get_contents(ABSOLUTE_ASSPATH.'uploads/'.$item->file_name));
	}

	function upload_attachment($field_name){
		if ($_FILES && $_FILES[$field_name]['error'] == 0){
			$config['upload_path'] = ABSOLUTE_ASSPATH.'uploads';
			$config['allowed_types'] = 'doc|docx|xls|xlsx|odt|ods|pdf|txt|bmp|gif|jpg|jpeg|png';
			$config['overwrite'] = false;
			$config['remove_spaces'] = true;

			$this->load->library('upload', $config);

			if (!$this->upload->do_upload($field_name)){
				$this->session->set_flashdata('error', $this->upload->display_errors('<div class="error">', '</div>'));
				return false;
			}else{

				return $this->upload->data();
			}
		}
		return false;
	}
}