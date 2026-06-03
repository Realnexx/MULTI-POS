<?php
require_once __DIR__ . '/../config/db.php';

class Cart {
    public static function add($product_id, $qty, $session_id, $user_id = null) {
        global $conn;
        // Check if item already exists in the cart for this user/session
        if ($user_id) {
            $stmt = $conn->prepare("SELECT id, qty FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
        } else {
            $stmt = $conn->prepare("SELECT id, qty FROM cart WHERE session_id = ? AND product_id = ?");
            $stmt->bind_param("si", $session_id, $product_id);
        }
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($res) {
            $new_qty = $res['qty'] + $qty;
            // Get product stock to cap the qty
            $p_stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
            $p_stmt->bind_param("i", $product_id);
            $p_stmt->execute();
            $prod = $p_stmt->get_result()->fetch_assoc();
            $p_stmt->close();
            
            if ($prod) {
                $new_qty = min($new_qty, $prod['stock']);
            }

            $stmt = $conn->prepare("UPDATE cart SET qty = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_qty, $res['id']);
            $stmt->execute();
            $stmt->close();
        } else {
            // Get product stock to cap the qty
            $p_stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
            $p_stmt->bind_param("i", $product_id);
            $p_stmt->execute();
            $prod = $p_stmt->get_result()->fetch_assoc();
            $p_stmt->close();
            
            if ($prod) {
                $qty = min($qty, $prod['stock']);
            }

            $stmt = $conn->prepare("INSERT INTO cart (session_id, user_id, product_id, qty) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siii", $session_id, $user_id, $product_id, $qty);
            $stmt->execute();
            $stmt->close();
        }
    }

    public static function update($product_id, $qty, $session_id, $user_id = null) {
        global $conn;
        // Get product stock to cap the qty
        $p_stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $p_stmt->bind_param("i", $product_id);
        $p_stmt->execute();
        $prod = $p_stmt->get_result()->fetch_assoc();
        $p_stmt->close();

        if (!$prod) return;
        $qty = min($qty, $prod['stock']);

        if ($qty <= 0) {
            self::remove($product_id, $session_id, $user_id);
            return;
        }

        if ($user_id) {
            $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
        } else {
            $stmt = $conn->prepare("SELECT id FROM cart WHERE session_id = ? AND product_id = ?");
            $stmt->bind_param("si", $session_id, $product_id);
        }
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($res) {
            $stmt = $conn->prepare("UPDATE cart SET qty = ? WHERE id = ?");
            $stmt->bind_param("ii", $qty, $res['id']);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO cart (session_id, user_id, product_id, qty) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siii", $session_id, $user_id, $product_id, $qty);
            $stmt->execute();
            $stmt->close();
        }
    }

    public static function remove($product_id, $session_id, $user_id = null) {
        global $conn;
        if ($user_id) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
        } else {
            $stmt = $conn->prepare("DELETE FROM cart WHERE session_id = ? AND product_id = ?");
            $stmt->bind_param("si", $session_id, $product_id);
        }
        $stmt->execute();
        $stmt->close();
    }

    public static function clear($session_id, $user_id = null) {
        global $conn;
        if ($user_id) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
        } else {
            $stmt = $conn->prepare("DELETE FROM cart WHERE session_id = ?");
            $stmt->bind_param("s", $session_id);
        }
        $stmt->execute();
        $stmt->close();
    }

    public static function get_items($session_id, $user_id = null) {
        global $conn;
        if ($user_id) {
            $stmt = $conn->prepare("
                SELECT c.qty, p.id, p.name, p.price, p.image_url, p.stock, cat.name as category_name
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                LEFT JOIN categories cat ON p.category_id = cat.id
                WHERE c.user_id = ?
            ");
            $stmt->bind_param("i", $user_id);
        } else {
            $stmt = $conn->prepare("
                SELECT c.qty, p.id, p.name, p.price, p.image_url, p.stock, cat.name as category_name
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                LEFT JOIN categories cat ON p.category_id = cat.id
                WHERE c.session_id = ?
            ");
            $stmt->bind_param("s", $session_id);
        }
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Index by product id to match $_SESSION['cart'] structure
        $cart = [];
        foreach ($res as $item) {
            $cart[$item['id']] = $item;
        }
        return $cart;
    }

    public static function migrate($session_id, $user_id) {
        global $conn;
        // Get all items in the session-based cart
        $session_items = self::get_items($session_id, null);
        foreach ($session_items as $item) {
            // Add to the user-based cart
            self::add($item['id'], $item['qty'], $session_id, $user_id);
        }
        // Delete items under session_id
        self::clear($session_id, null);
    }
}
