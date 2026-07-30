<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Cost_certificate_pdf extends App_pdf
{
    protected $cost_certificate;

    public function __construct($cost_certificate)
    {
        $cost_certificate                = hooks()->apply_filters('request_html_pdf_data', $cost_certificate);
        $GLOBALS['Cost_certificate_pdf'] = $cost_certificate;

        parent::__construct();

        $this->cost_certificate = $cost_certificate;

        $this->SetTitle(_l('cost_certificate'));
        # Don't remove these lines - important for the PDF layout
        $this->cost_certificate = $this->fix_editor_html($this->cost_certificate);
    }

    public function prepare()
    {
        $this->set_view_vars('cost_certificate', $this->cost_certificate);

        return $this->build();
    }

    protected function type()
    {
        return 'cost_certificate';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $actualPath = APP_MODULES_PATH . '/purchase/views/customers/cost_certificatepdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}