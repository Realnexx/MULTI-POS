<?php
require_once("../../auth/config/db_connection.php");

$modules = [
    'Manage Students'         => 'Interface for importing OJT students from ECR database',
    'Student Attendance'      => 'Main dashboard for viewing and printing OJT attendance logs',
    'Hours Summary'           => 'Dashboard for tracking total rendered and remaining OJT hours',
    'OJT Student Registry'    => 'Local database management for enrolled OJT students'
];

echo "<h2>OJT Module Registration</h2>";
echo "<ul>";

foreach ($modules as $name => $desc) {
    // Check if module exists
    $stmt = $conn->prepare("SELECT id FROM modules WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $slug = strtolower(str_replace(' ', '-', $name));
        $ins = $conn->prepare("INSERT INTO modules (name, slug, description) VALUES (?, ?, ?)");
        $ins->bind_param("sss", $name, $slug, $desc);
        
        if ($ins->execute()) {
            $mid = $conn->insert_id;
            echo "<li style='color:green'>Created: <b>$name</b> (ID: $mid)</li>";
            
            // Assign full permissions to Admin (Role ID 1) automatically
            $perm = $conn->prepare("INSERT INTO role_permissions (role_id, module_id, can_view, can_manage, can_delete, can_export) VALUES (1, ?, 1, 1, 1, 1)");
            $perm->bind_param("i", $mid);
            $perm->execute();
        } else {
            echo "<li style='color:red'>Failed to create: $name - " . $conn->error . "</li>";
        }
    } else {
        echo "<li style='color:orange'>Skipped: <b>$name</b> (Already exists)</li>";
    }
}

echo "</ul>";
echo "<p>Next Step: You can now configure these permissions for other roles in the <a href='../roles-permissions.php'>Roles & Permissions</a> page.</p>";
$conn->close();
?>
