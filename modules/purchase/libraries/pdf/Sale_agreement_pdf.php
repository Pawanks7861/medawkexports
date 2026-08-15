<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Sale_agreement_pdf extends App_pdf
{
    protected $sale_agreement;

    public function __construct($sale_agreement)
    {
        $sale_agreement               = hooks()->apply_filters('request_html_pdf_data', $sale_agreement);
        $GLOBALS['sale_agreement_pdf'] = $sale_agreement;

        parent::__construct();

        $this->sale_agreement = $sale_agreement;

        // ✅ Set custom page size: 8.5 x 14 inches (Legal)
        // 1 inch = 25.4 mm → [8.5 * 25.4, 14 * 25.4] = [215.9, 355.6]
        $custom_layout = array(215.9, 355.6);
        $this->SetPageFormat($custom_layout, 'P'); // 'P' = Portrait; use 'L' for Landscape

        // Optional: adjust margins and auto page break
        $this->SetMargins(15, 20, 15);
        $this->SetAutoPageBreak(true, 20);

        $this->SetTitle(_l('sale_agreement'));

        // Important for proper layout rendering
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
        $actualPath = APP_MODULES_PATH . '/purchase/views/customers/sale_agreementpdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
