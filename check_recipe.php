<?php
require_once 'config/db.php';
// Check for product_modifiers table
$r1 = $conn->query("SHOW TABLES LIKE 'product_modifiers'");
echo "product_modifiers: " . ($r1->num_rows ? "EXISTS" : "MISSING") . "\n";

// Check product_recipes table structure
$r2 = $conn->query("DESCRIBE product_recipes");
echo "\nproduct_recipes columns:\n";
while ($row = $r2->fetch_assoc()) {
    echo "  {$row['Field']} ({$row['Type']})\n";
}

// Check if check_recipe_stock function logic exists
$r3 = $conn->query("SHOW TABLES LIKE 'product_modifier_options'");
echo "\nproduct_modifier_options: " . ($r3->num_rows ? "EXISTS" : "MISSING") . "\n";
?>