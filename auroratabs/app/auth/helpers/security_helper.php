<?php
/**
 * Security Helper for AuroraTabs
 */

/**
 * Sanitizes input data to prevent XSS and other injection attacks.
 * 
 * @param string $data The raw input data
 * @return string The sanitized data
 */
function sanitize($data) {
    if ($data === null) return '';
    $data = trim($data);               // Remove spaces
    $data = stripslashes($data);       // Remove backslashes
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Load all permissions for a given role and cache them in session.
 * This is called once during login to improve performance.
 * 
 * @param mysqli $conn Database connection
 * @param int $role_id The role ID
 * @return array Associative array of permissions by module name
 */
function loadRolePermissions($conn, $role_id) {
    $query = "SELECT m.name, rp.can_view, rp.can_manage, rp.can_delete, rp.can_export
              FROM role_permissions rp
              JOIN modules m ON rp.module_id = m.id
              WHERE rp.role_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        // Cast TINYINT values to integers for consistency
        $permissions[$row['name']] = [
            'can_view'   => (int)$row['can_view'],
            'can_manage' => (int)$row['can_manage'],
            'can_delete' => (int)$row['can_delete'],
            'can_export' => (int)$row['can_export']
        ];
    }
    $stmt->close();
    return $permissions;
}

/**
 * Check if the current user has a specific permission on a module.
 * Fetches directly from the database for real-time permission checks.
 * 
 * @param string $module_name Name of the module (as in 'modules' table)
 * @param string $action The permission action: 'view', 'manage', 'delete', or 'export'
 * @param mysqli|null $connection Optional database connection (recommended to avoid scope issues)
 * @return bool True if permitted, false otherwise
 */
function hasPermission($module_name, $action = 'view', $connection = null) {
    // Check if user is logged in and has a role
    if (!isset($_SESSION['role_id'])) {
        return false;
    }
    
    // Get database connection
    if ($connection === null) {
        global $conn;
        if (!isset($conn)) {
            require_once __DIR__ . '/../config/db_connection.php';
        }
        $connection = $conn;
    }
    
    // Verify we have a valid connection
    if (!$connection || !($connection instanceof mysqli)) {
        return false;
    }
    
    // Map action to permission column
    $permission_column = 'can_' . $action;
    
    // Query database directly for the permission
    $query = "SELECT rp.{$permission_column}
              FROM role_permissions rp
              JOIN modules m ON rp.module_id = m.id
              WHERE rp.role_id = ? AND m.name = ?";
    
    $stmt = $connection->prepare($query);
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("is", $_SESSION['role_id'], $module_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $has_permission = (int)$row[$permission_column] === 1;
        $stmt->close();
        return $has_permission;
    }
    
    $stmt->close();
    return false;
}

/**
 * Alias for hasPermission for backward compatibility
 */
function has_permission($module_name, $action = 'view', $connection = null) {
    return hasPermission($module_name, $action, $connection);
}
?>
