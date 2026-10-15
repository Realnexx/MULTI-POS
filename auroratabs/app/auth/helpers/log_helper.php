<?php
/**
 * Activity Log Helper for AuroraTabs
 */

if (!function_exists('log_activity')) {
    /**
     * Logs a system activity into the database.
     * 
     * @param mysqli $conn The database connection
     * @param int|null $user_id The ID of the user performing the action
     * @param string $action The action performed (e.g., 'Created', 'Updated', 'Deleted')
     * @param string $module The module affected (e.g., 'Users', 'Criteria', 'Competitions')
     * @param string $details Detailed description of the action
     * @return bool True on success, false on failure
     */
    function log_activity($conn, $user_id, $action, $module, $details) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) return false;
        
        $stmt->bind_param("issss", $user_id, $action, $module, $details, $ip_address);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
}
?>
