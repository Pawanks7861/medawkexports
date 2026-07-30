<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(__DIR__ . '/App_pdf.php');

class Leads_pdf extends App_pdf
{
    protected $lead_data;
    protected $ci;

    public function __construct($lead_data)
    {
        parent::__construct();
        
        $this->ci =& get_instance();
        $this->lead_data = $lead_data;

        $this->SetTitle("Leads");
    }

    public function prepare()
    {
        $this->set_view_vars([
            'lead_data' => $this->lead_data,
            
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'Leads';
    }

    protected function file_path()
    {
        $actualPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/leads_pdf/leads_pdf.php';
        return $actualPath;
    }

    
}
