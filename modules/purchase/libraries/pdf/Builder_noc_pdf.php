<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Builder_noc_pdf extends App_pdf
{
    protected $builder_noc;

    public function __construct($builder_noc)
    {
        $builder_noc                = hooks()->apply_filters('request_html_pdf_data', $builder_noc);
        $GLOBALS['Builder_noc_pdf'] = $builder_noc;

        parent::__construct();

        $this->builder_noc = $builder_noc;

        $this->SetTitle(_l('builder_noc'));
        # Don't remove these lines - important for the PDF layout
        $this->builder_noc = $this->fix_editor_html($this->builder_noc);
    }

    public function prepare()
    {
        $this->set_view_vars('builder_noc', $this->builder_noc);

        return $this->build();
    }

    protected function type()
    {
        return 'builder_noc';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $actualPath = APP_MODULES_PATH . '/purchase/views/customers/builder_nocpdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}