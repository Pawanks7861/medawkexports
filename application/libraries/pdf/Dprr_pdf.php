<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(__DIR__ . '/App_pdf.php');

class Dprr_pdf extends App_pdf
{
    protected $form_data;
    protected $form_type;
    protected $formid;
    protected $ci;

    public function __construct($form_data)
    {
        parent::__construct();
        
        $this->ci =& get_instance();
        $this->form_data = $form_data;
        $this->form_type = $form_data->form_type;
        $this->formid = $form_data->formid;

        $this->SetTitle("DPR");
    }

    public function prepare()
    {
        $this->set_view_vars([
            'form_data' => $this->form_data,
            'form_basic_info' => $this->get_dpr_form($this->formid),
            'form_rows_info' => $this->get_dpr_form_detail($this->formid),
            'form_rmc_plant' => $this->get_progress_report_rmc_grade($this->formid),
            'form_material_inward' => $this->get_progress_report_material_inward($this->formid),
            'form_dept_labour' => $this->get_progress_report_dept_labour($this->formid),
            'form_attachments' => $this->get_form_attachments($this->formid),
            'form_cement_rack' => $this->get_progress_report_cement_rack($this->formid),
            'form_block_mortar_joint' => $this->get_progress_report_block_mortar_joint($this->formid),
            'form_tile' => $this->get_progress_report_tile($this->formid),
            'form_coupler' => $this->get_progress_report_coupler($this->formid),
            'form_wire' => $this->get_progress_report_wire($this->formid),
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'Dprr';
    }

    protected function file_path()
    {
        $actualPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/form_pdf/dprrpdf.php';
        return $actualPath;
    }

    private function get_dpr_form($form_id) 
    {
        $this->ci->db->where('form_id', $form_id);
        return $this->ci->db->get(db_prefix() . 'dpr_form')->row();
    }

    private function get_dpr_form_detail($form_id)
    {
        $this->ci->db->where('form_id', $form_id);
        return $this->ci->db->get(db_prefix() . 'dpr_form_detail')->result_array();
    }

    private function get_form_attachments($form_id)
    {
        $this->ci->db->where('formid', $form_id);
        return $this->ci->db->get(db_prefix() . 'form_attachments')->result_array();
    }

    private function get_progress_report_rmc_grade($form_id)
    {
        $this->ci->db->where('form_id', $form_id);
        return $this->ci->db->get(db_prefix() . 'dpr_rmc_form_detail')->result_array();
    }

    private function get_progress_report_material_inward($form_id)
    {
        $this->ci->db->where('form_id', $form_id);
        return $this->ci->db->get(db_prefix() . 'dpr_material_form_detail')->result_array();
    }

    private function get_progress_report_dept_labour($form_id)
    {
        $this->ci->db->where('form_id', $form_id);
        return $this->ci->db->get(db_prefix() . 'dpr_dept_form_detail')->result_array();
    }
    private function get_progress_report_cement_rack($form_id)
    {
        $this->ci->db->where('form_id', $form_id);
        return $this->ci->db->get(db_prefix() . 'dpr_cement_form_detail')->result_array();
    }
    private function get_progress_report_block_mortar_joint($form_id)
    {
        $this->ci->db->where('form_id', $form_id);
        return $this->ci->db->get(db_prefix() . 'dpr_block_form_detail')->result_array();
    }
    private function get_progress_report_tile($form_id)
    {
        $this->ci->db->where('form_id', $form_id);
        return $this->ci->db->get(db_prefix() . 'dpr_tile_form_detail')->result_array();
    }
    private function get_progress_report_coupler($form_id)
    {
        $this->ci->db->where('form_id', $form_id);
        return $this->ci->db->get(db_prefix() . 'dpr_coupler_form_detail')->result_array();
    }
    private function get_progress_report_wire($form_id)
    {
        $this->ci->db->where('form_id', $form_id);
        return $this->ci->db->get(db_prefix() . 'dpr_wires_form_detail')->result_array();
    }
}
