<?php
class Setting {
    public static function get($key, $default = null) {
        global $conn;
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['setting_value'];
        }
        return $default;
    }

    public static function set($key, $value, $category = 'general') {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, category) VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE setting_value = ?, category = ?");
        $stmt->bind_param("sssss", $key, $value, $category, $value, $category);
        return $stmt->execute();
    }

    public static function get_by_category($category) {
        global $conn;
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE category = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public static function update_all($data, $category = 'general') {
        foreach ($data as $key => $value) {
            self::set($key, $value, $category);
        }
    }
}
