<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Sale_agreement2_pdf extends App_pdf
{
    protected $sale_agreement;

    public function __construct($sale_agreement)
    {
        $sale_agreement                 = hooks()->apply_filters('request_html_pdf_data', $sale_agreement);
        $GLOBALS['sale_agreement2_pdf'] = $sale_agreement;

        parent::__construct();

        $this->sale_agreement = $sale_agreement;

        $custom_layout = array(215.9, 355.6);
        $this->SetPageFormat($custom_layout, 'P'); // 'P' = Portrait; use 'L' for Landscape

        // Optional: adjust margins if needed
        $this->SetMargins(15, 20, 15);
        $this->SetAutoPageBreak(true, 20);

        $this->SetTitle(_l('sale_agreement'));

        // Important for layout
        $this->sale_agreement = $this->fix_editor_html($this->sale_agreement);
    }

    public function prepare()
    {
        $this->set_view_vars('sale_agreement', $this->sale_agreement);
        return $this->build();
    }

    protected function type()
    {
        return 'sale_agreement';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $actualPath = APP_MODULES_PATH . '/purchase/views/customers/sale_agreement2pdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
