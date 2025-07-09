<?php
// Common utility functions

// Format date for display
function format_date($date_string, $format = 'd/m/Y') {
    if (empty($date_string) || $date_string == '0000-00-00') {
        return '';
    }
    $date = new DateTime($date_string);
    return $date->format($format);
}

// Format currency for display
function format_currency($amount) {
    return 'KES ' . number_format($amount, 2);
}

// Sanitize output
function sanitize_output($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Get farmer name by ID
function get_farmer_name($farmer_id) {
    global $conn;
    
    $sql = "SELECT CONCAT(first_name, ' ', last_name) AS name FROM farmers WHERE farmer_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $farmer_id);
        $stmt->execute();
        $stmt->bind_result($name);
        $stmt->fetch();
        $stmt->close();
        return $name;
    }
    return '';
}

// Pagination function
function paginate($base_url, $total_items, $per_page, $current_page) {
    $total_pages = ceil($total_items / $per_page);
    $pagination = '';
    
    if ($total_pages > 1) {
        $pagination .= '<nav aria-label="Page navigation"><ul class="pagination">';
        
        // Previous link
        if ($current_page > 1) {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($current_page - 1) . '">Previous</a></li>';
        }
        
        // Page links
        for ($i = 1; $i <= $total_pages; $i++) {
            $active = ($i == $current_page) ? ' active' : '';
            $pagination .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $base_url . '&page=' . $i . '">' . $i . '</a></li>';
        }
        
        // Next link
        if ($current_page < $total_pages) {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($current_page + 1) . '">Next</a></li>';
        }
        
        $pagination .= '</ul></nav>';
    }
    
    return $pagination;
}
?>