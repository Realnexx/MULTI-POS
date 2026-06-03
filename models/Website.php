<?php
require_once __DIR__ . '/../config/db.php';

class Website {
    public static function get_content() {
        global $conn;
        $query = "SELECT section_key, content_value FROM website_content";
        $results = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
        $content = [];
        foreach ($results as $row) {
            $content[$row['section_key']] = $row['content_value'];
        }
        return $content;
    }

    public static function update_content($data) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO website_content (section_key, content_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_value = ?");
        $success = true;
        foreach ($data as $key => $value) {
            $stmt->bind_param("sss", $key, $value, $value);
            if (!$stmt->execute()) {
                $success = false;
            }
        }
        $stmt->close();
        return $success;
    }

    public static function save_contact_message($name, $email, $subject, $message) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO contacts_messages (name, email, subject, message) VALUES (?, (?, ?, ?)");
        // Wait, let's make sure the SQL syntax is perfect: VALUES (?, ?, ?, ?)
        $stmt = $conn->prepare("INSERT INTO contacts_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function get_all_messages() {
        global $conn;
        $query = "SELECT * FROM contacts_messages ORDER BY id DESC";
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function mark_message_read($id) {
        global $conn;
        $stmt = $conn->prepare("UPDATE contacts_messages SET is_read = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
