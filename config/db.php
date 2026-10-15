<?php
require_once __DIR__ . '/app.php';
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params(['lifetime'=>0,'path'=>APP_BASE_PATH,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
foreach (['host' => 'DB_HOST', 'user' => 'DB_USER', 'password' => 'DB_PASS', 'name' => 'DB_NAME'] as $key => $constant) {
    define($constant, $appConfig['database'][$key]);
}
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (Throwable $error) {
    error_log('Database connection failed: ' . $error->getMessage());
    http_response_code(503);
    exit('Service temporarily unavailable.');
}
require_once __DIR__ . '/uploads.php';
validate_uploaded_images($_FILES);

// Global Helper Functions
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle)
    {
        return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
    }
}

if (!function_exists('url')) {
    function url($path = '')
    {
        $base = APP_BASE_PATH;
        return $base . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect($path)
    {
        header("Location: " . url($path));
        exit;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('set_flash')) {
    function set_flash($type, $message)
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('get_flash')) {
    function get_flash()
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('sanitize')) {
    function sanitize($data)
    {
        global $conn;
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = sanitize($val);
            }
            return $data;
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_product_image')) {
    function get_product_image($image_url, $product_id = 0)
    {
        // Fallback premium gourmet burger images for culinary presentation
        $burger_images = [
            "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=1000&q=80",
            "https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=1000&q=80",
            "https://images.unsplash.com/photo-1586190848861-99aa4a171e90?auto=format&fit=crop&w=1000&q=80",
            "https://images.unsplash.com/photo-1625813506062-0aeb1d7a094b?auto=format&fit=crop&w=1000&q=80",
            "https://images.unsplash.com/photo-1586816001966-79b736744398?auto=format&fit=crop&w=1000&q=80",
            "https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=1000&q=80"
        ];

        if (empty($image_url)) {
            return $burger_images[abs((int) $product_id) % count($burger_images)];
        }

        if (strpos($image_url, 'http://') === 0 || strpos($image_url, 'https://') === 0) {
            return $image_url;
        }

        $local_path = __DIR__ . '/../' . ltrim($image_url, '/');
        if (file_exists($local_path) && !is_dir($local_path)) {
            return url($image_url);
        }

        return $burger_images[abs((int) $product_id) % count($burger_images)];
    }
}

if (!function_exists('get_branch_image')) {
    function get_branch_image($image_url, $branch_id = 0)
    {
        $fallbacks = [
            "https://images.unsplash.com/photo-1555396273-367ea4eb4db5?q=80&w=800&auto=format&fit=crop",
            "https://images.unsplash.com/photo-1552566626-52f8b828add9?q=80&w=800&auto=format&fit=crop",
            "https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=800&auto=format&fit=crop",
            "https://images.unsplash.com/photo-1414235077428-338989a2e8c0?q=80&w=800&auto=format&fit=crop"
        ];
        if (empty($image_url))
            return $fallbacks[abs((int) $branch_id) % 4];
        if (strpos($image_url, 'http') === 0)
            return $image_url;
        return url($image_url);
    }
}
if (!function_exists('get_user_avatar')) {
    function get_user_avatar($avatar_url, $user_name = '')
    {
        if (empty($avatar_url)) {
            return "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=ff4d6d&color=fff&bold=true";
        }
        if (strpos($avatar_url, 'http://') === 0 || strpos($avatar_url, 'https://') === 0) {
            return $avatar_url;
        }
        return url($avatar_url);
    }
}
