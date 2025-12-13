<?php
/**
 * Application Settings
 * Timezone and Currency Configuration
 */

// Set Philippine Timezone
date_default_timezone_set('Asia/Manila');

// Currency Settings
define('CURRENCY_SYMBOL', '₱');
define('CURRENCY_CODE', 'PHP');

// Helper function to format currency
function format_currency($amount) {
    return '₱' . number_format($amount, 2);
}

// Helper function to get current datetime
function get_current_datetime() {
    return date('Y-m-d H:i:s');
}

// Helper function to format datetime for display
function format_datetime($datetime, $format = 'M d, Y h:i A') {
    if (empty($datetime)) return 'N/A';
    return date($format, strtotime($datetime));
}
?>