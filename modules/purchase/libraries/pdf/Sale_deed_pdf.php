<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Sale_deed_pdf extends App_pdf
{
    protected $sale_deed;

    public function __construct($sale_deed)
    {
        $sale_deed = hooks()->apply_filters('request_html_pdf_data', $sale_deed);
        $GLOBALS['Sale_deed_pdf'] = $sale_deed;

        parent::__construct();

        $this->sale_deed = $sale_deed;

        // Custom legal size (8.5 x 14 inches)
        $custom_layout = [215.9, 355.6];
        $this->SetPageFormat($custom_layout, 'P');

        // Enable header and footer
        $this->setPrintHeader(true);
        $this->setPrintFooter(true);

        // Convert inches to mm
        $top_margin_before_header = 38.1;    // 1.5 inches = 1.5 * 25.4
        $left_margin = 31.75;               // 1.25 inches = 1.25 * 25.4
        $right_margin = 25.4;               // 1 inch = 1 * 25.4
        $bottom_margin = 19.05;             // 0.75 inches = 0.75 * 25.4
        
        // Set header margin to 1.5 inches (38.1mm) - this is the space from top of page to header content
        $this->setHeaderMargin($top_margin_before_header);
        
        // Set footer margin (space from bottom of page to footer content)
        $footer_margin = 15; // mm from bottom
        $this->setFooterMargin($footer_margin);
        
        // Calculate header height including lines and text
        $header_height = 38; // Your header content height
        
        // Set margins: Left, Top (header margin + header height), Right
        $this->SetMargins(
            $left_margin, 
            $top_margin_before_header + $header_height, 
            $right_margin
        );
        
        // Set auto page break with bottom margin (including space for footer)
        $this->SetAutoPageBreak(true, $bottom_margin + $footer_margin + 5);
        
        $this->SetTitle(_l('sale_deed'));
        $this->sale_deed = $this->fix_editor_html($this->sale_deed);

        // Manually add first page with correct positioning
        $this->AddPage();
        $this->deletePage(1);

        // Force starting position below header for first page
        $this->SetY($top_margin_before_header + $header_height);
    }


    public function prepare()
    {
        // Don't auto-add page here since we added it in constructor
        $this->set_view_vars('sale_deed', $this->sale_deed);
        return $this->build();
    }


    protected function type()
    {
        return 'sale_deed';
    }


    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $actualPath = APP_MODULES_PATH . '/purchase/views/customers/sale_deedpdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }

    public function Header()
    {
        // Start header content 1.5 inches from top
        $header_start_y = 38.1; // 1.5 inches in mm
        
        $this->SetY($header_start_y);
        
        $this->SetLineStyle(['width' => 0.4]);
        
        // Draw line from left margin to right margin
        $page_width = $this->getPageWidth();
        $this->Line(
            $this->lMargin,           // Start X from left margin (31.75mm)
            $header_start_y,          // Start Y at header start
            $page_width - $this->rMargin,  // End X at right margin (page width - 25.4mm)
            $header_start_y           // End Y at header start
        );

        $this->SetFont('', 'B', 13);
        $this->SetX($this->lMargin); // Start from left margin (31.75mm)
        $this->Cell(
            $page_width - $this->lMargin - $this->rMargin, // Width: page width minus both margins
            6, 
            "“ KAUTILYA ONE-54 ”", 
            0, 1, 'C'
        );

        $this->SetFont('', 'B', 13);
        $this->SetX($this->lMargin); // Start from left margin (31.75mm)
        $this->Cell(
            $page_width - $this->lMargin - $this->rMargin, // Width: page width minus both margins
            6, 
            "RERA No. PR/GJ/AHMEDABAD/AHMEDABAD CITY/AUDA/MAA10980/291122", 
            0, 1, 'C'
        );

        // Bottom line of header
        $this->Line(
            $this->lMargin,           // Start X from left margin (31.75mm)
            $header_start_y + 18,     // Start Y at header start + 18mm
            $page_width - $this->rMargin,  // End X at right margin (page width - 25.4mm)
            $header_start_y + 18      // End Y at header start + 18mm
        );

        // Reset Y position for content after header
        $this->SetY($this->getHeaderMargin() + $header_height);
    }

    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        
        // Set font for footer
        $this->SetFont('helvetica', 'I', 9);
        
        // Get page width
        $page_width = $this->getPageWidth();
        
        // Get current page number and total pages
        $page_number = $this->getAliasNumPage();
        $total_pages = $this->getAliasNbPages();
        
        // Page number text
        $footer_text = "Page No : {$page_number}";
        
        // Print page number aligned to right side
        // Calculate position: start from right margin position
        $text_width = 30; // Approximate width for "Page X of Y"
        $x_position = $page_width - $this->rMargin - $text_width;
        
        $this->SetX($x_position);
        $this->Cell(
            $text_width,
            10,
            $footer_text,
            0,
            0,
            'R'
        );
    }
}