<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Allotment_letter_pdf extends App_pdf
{
    protected $allotment_letter;

    public function __construct($allotment_letter)
    {
        $allotment_letter                = hooks()->apply_filters('request_html_pdf_data', $allotment_letter);
        $GLOBALS['Allotment_letter_pdf'] = $allotment_letter;

        parent::__construct();

        $this->allotment_letter = $allotment_letter;

        $this->SetTitle(_l('allotment_letter'));
        # Don't remove these lines - important for the PDF layout
        $this->allotment_letter = $this->fix_editor_html($this->allotment_letter);
    }

    public function prepare()
    {
        $this->set_view_vars('allotment_letter', $this->allotment_letter);

        return $this->build();
    }

    protected function type()
    {
        return 'allotment_letter';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $actualPath = APP_MODULES_PATH . '/purchase/views/customers/allotment_letterpdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}