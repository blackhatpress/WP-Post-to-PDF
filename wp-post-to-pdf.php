<?php
/*
Plugin Name: WP Post to PDF
Plugin URI: https://example.com/wp-post-to-pdf
Description: Converts WordPress post to PDF and adds download link at the end of post
Version: 1.0.0
Author: blackhatpress
Author URI: https://example.com
License: GPL2
*/

// Register activation hook
register_activation_hook(__FILE__, 'wp_post_to_pdf_activate');

function wp_post_to_pdf_activate() {
    // Flush rewrite rules on activation
    flush_rewrite_rules();
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'wp_post_to_pdf_deactivate');

function wp_post_to_pdf_deactivate() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
}

// Add PDF download link at the end of post content
add_filter('the_content', 'wp_post_to_pdf_add_download_link');

function wp_post_to_pdf_add_download_link($content) {
    // Check if we're on a single post page
    if (is_singular('post')) {
        global $post;
        // Get the post ID
        $post_id = $post->ID;
        // Get the PDF URL
        $pdf_url = wp_post_to_pdf_generate_pdf($post_id);
        // Append download link to the post content
        $content .= '<p><a href="' . $pdf_url . '">Download PDF</a></p>';
    }
    return $content;
}

// Generate PDF from post content
function wp_post_to_pdf_generate_pdf($post_id) {
    // Load TCPDF library
    require_once('tcpdf/tcpdf.php');

    // Get post data
    $post = get_post($post_id);
    $post_title = $post->post_title;
    $post_content = $post->post_content;

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('WP Post to PDF');
    $pdf->SetAuthor('blackhatpress');
    $pdf->SetTitle($post_title);
    $pdf->SetSubject($post_title);
    $pdf->SetKeywords('WordPress, PDF');

    // Set default font
    $pdf->SetFont('cid0jp', '', 14);

    // Add a page
    $pdf->AddPage();

    // Output the HTML content as PDF
    $pdf->writeHTML($post_content, true, false, true, false, '');

    // Save the PDF file
    $pdf_file_name = sanitize_title($post_title) . '-' . date('Ymd') . '.pdf';
    $pdf_file_path = WP_CONTENT_DIR . '/uploads/pdf/' . $pdf_file_name;
    $pdf->Output($pdf_file_path, 'F');

    // Return the PDF URL
    $pdf_url = content_url('uploads/pdf/' . $pdf_file_name);
    return $pdf_url;
}
