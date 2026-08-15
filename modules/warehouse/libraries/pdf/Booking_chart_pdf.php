<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once APPPATH . 'libraries/pdf/App_pdf.php';

/**
 * Vendor allocation report pdf
 */
class Booking_chart_pdf extends App_pdf {
    protected $booking_chart;
    public $font_size = '';
    public $size = 9;

    /**
     * Get font size.
     *
     * @return mixed
     */
    public function get_font_size() {
        return $this->font_size;
    }

    /**
     * Set font size.
     *
     * @param int $size
     * @return $this
     */
    public function set_font_size($size) {
        $this->font_size = $size;
        return $this;
    }

    /**
     * Constructor.
     *
     * @param mixed $booking_chart
     */
    public function __construct($booking_chart) {
        $booking_chart = hooks()->apply_filters('request_html_pdf_data', $booking_chart);
        $GLOBALS['booking_chart_pdf'] = $booking_chart;

        parent::__construct();

        $this->booking_chart = $booking_chart;

        $this->SetTitle('Booking Chart');

        # Don't remove these lines - important for the PDF layout
        $this->booking_chart = $this->fix_editor_html($this->booking_chart);
    }

    /**
     * Prepare the PDF.
     *
     * @return mixed
     */
    public function prepare() {
        $this->set_view_vars('booking_chart', $this->booking_chart);
        return $this->build();
    }

    /**
     * Type of the PDF.
     *
     * @return string
     */
    protected function type() {
        return 'booking_chart';
    }

    /**
     * File path for the PDF view.
     *
     * @return string
     */
    protected function file_path() {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $actualPath = APP_MODULES_PATH . '/warehouse/views/booking_chartpdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
