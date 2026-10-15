<?php
$file = 'c:/xampp/htdocs/pos/models/Product.php';
$content = file_get_contents($file);

$gen_methods = "
    public static function generate_unique_sku()
    {
        global \$conn;
        \$prefix = \"SKX-\";
        \$year = date('y');
        \$sql = \"SELECT sku FROM products WHERE sku LIKE '{\$prefix}{\$year}%' ORDER BY sku DESC LIMIT 1\";
        \$res = \$conn->query(\$sql);
        if (\$res && \$row = \$res->fetch_assoc()) {
            \$last_num = (int) substr(\$row['sku'], -5);
            \$next_num = \$last_num + 1;
        } else {
            \$next_num = 1;
        }
        return \$prefix . \$year . str_pad(\$next_num, 5, '0', STR_PAD_LEFT);
    }

    public static function generate_unique_barcode()
    {
        global \$conn;
        do {
            \$barcode = '1' . str_pad(mt_rand(0, 99999999999), 11, '0', STR_PAD_LEFT);
            \$res = \$conn->query(\"SELECT id FROM products WHERE barcode = '\$barcode'\");
        } while (\$res && \$res->num_rows > 0);
        return \$barcode;
    }
";

// Add before the end of the class
$content = rtrim($content);
$content = substr($content, 0, -1) . $gen_methods . "\n}";

file_put_contents($file, $content);
echo "Auto-generation methods added to Product.php\n";
