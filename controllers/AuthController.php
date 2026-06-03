<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/auth.php';

class AuthController
{
    public function login()
    {
        require_guest();
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username'] ?? $_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $selected_role = sanitize($_POST['role'] ?? 'Admin');
            $selected_branch = sanitize($_POST['branch'] ?? 'Quezon City');

            // Login attempt rate limiting
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_attempt_time'] = time();
            }

            if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt_time']) < 300) {
                set_flash('danger', 'Too many failed login attempts. Please try again in 5 minutes.');
                redirect('?route=login');
            }

            $user = User::find_by_username_or_email($username);
            if ($user && password_verify($password, $user['password'])) {
                $user_username = $user['username'] ?: explode('@', $user['email'])[0];
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'username' => $user_username,
                    'role' => $selected_role,
                    'branch' => $selected_branch,
                    'avatar_url' => $user['avatar_url'] ?: $user['image'] ?: null
                ];
                unset($_SESSION['login_attempts']);

                // Set status online
                User::set_status($user['id'], 'online');

                // Persist selected branch to users table
                global $conn;
                $b = $conn->real_escape_string($selected_branch);
                $conn->query("UPDATE users SET branch = '$b' WHERE id = " . intval($user['id']));

                // Log login activity
                User::log_activity($user['id'], 'login', 'User logged in as ' . $selected_role . ' from branch: ' . $selected_branch);

                // Check roles to determine redirect
                if (strcasecmp($selected_role, 'Admin') === 0 && User::has_permission($user['id'], 'view_dashboard')) {
                    redirect('?route=dashboard');
                } elseif (strcasecmp($selected_role, 'Cashier') === 0 && User::has_permission($user['id'], 'access_pos')) {
                    redirect('?route=pos');
                } else {
                    redirect('?route=home');
                }
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                // Log failed admin login attempt (no user_id)
                User::log_activity(null, 'login_failed', 'Failed admin login attempt for: ' . $username);
                set_flash('danger', 'Invalid username or password.');
                redirect('?route=login');
            }
        }

        require_once __DIR__ . '/../models/Branch.php';
        $db_branches = Branch::get_all_active();
        require __DIR__ . '/../views/auth/login.php';
    }

    public function customer_login() {
        require_guest();
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username'] ?? $_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_attempt_time'] = time();
            }

            if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt_time']) < 300) {
                set_flash('danger', 'Too many failed login attempts. Please try again in 5 minutes.');
                redirect('?route=customer_login');
            }

            $user = User::find_by_username_or_email($username);
            if ($user && password_verify($password, $user['password'])) {
                $user_username = $user['username'] ?: explode('@', $user['email'])[0];
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'username' => $user_username
                ];
                unset($_SESSION['login_attempts']);

                // Set status online
                User::set_status($user['id'], 'online');

                // Log customer login activity
                User::log_activity($user['id'], 'customer_login', 'Customer logged in: ' . $user_username);

                // Migrate cart from session-based to database user-based
                require_once __DIR__ . '/../models/Cart.php';
                Cart::migrate(session_id(), $user['id']);

                set_flash('success', 'Welcome back, ' . htmlspecialchars($user_username) . '! Delightful layers await you.');
                redirect('?route=home');
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                // Log failed customer login attempt
                User::log_activity(null, 'login_failed', 'Failed customer login attempt for: ' . $username);
                set_flash('danger', 'Invalid username or password.');
                redirect('?route=customer_login');
            }
        }

        require __DIR__ . '/../views/auth/customer_login.php';
    }

    public function register()
    {
        require_guest();
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (empty($username) || empty($password)) {
                set_flash('danger', 'All fields are required.');
                redirect('?route=register');
            }

            if ($password !== $confirm) {
                set_flash('danger', 'Passwords do not match.');
                redirect('?route=register');
            }

            if (User::find_by_username_or_email($username)) {
                set_flash('danger', 'Username is already taken.');
                redirect('?route=register');
            }

            // Create user using username for all name parts to satisfy model, and username@haluhalu.club as email
            $email = $username . '@haluhalu.club';
            
            // The User::create method now expects:
            // 1. $firstname, 2. $middlename, 3. $lastname, 4. $extname, 5. $username, 6. $email, 7. $password
            $user_id = User::create($username, '', '', '', $username, $email, $password, [3]);
            if ($user_id) {
                $_SESSION['user'] = [
                    'id' => $user_id,
                    'name' => $username,
                    'email' => $email,
                    'username' => $username
                ];
                // Set status online on register
                User::set_status($user_id, 'online');
                // Log registration
                User::log_activity($user_id, 'register', 'New customer account registered: ' . $username);
                set_flash('success', 'Account created! Now, let\'s customize your profile.');
                redirect('?route=setup_profile');
            } else {
                set_flash('danger', 'Registration failed. Please try again.');
                redirect('?route=register');
            }
        }

        require __DIR__ . '/../views/auth/register.php';
    }

    public function setup_profile()
    {
        require_login();
        $user_id = $_SESSION['user']['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            enforce_csrf();
            $firstname = sanitize($_POST['firstname'] ?? '');
            $middlename = sanitize($_POST['middlename'] ?? '');
            $lastname = sanitize($_POST['lastname'] ?? '');
            $extname = sanitize($_POST['extname'] ?? '');
            $real_email = sanitize($_POST['email'] ?? '');

            if (empty($firstname) || empty($lastname) || empty($real_email)) {
                set_flash('danger', 'First name, last name, and email are required to complete your profile.');
                redirect('?route=setup_profile');
            }

            $name_parts = array_filter([$firstname, $middlename, $lastname, $extname]);
            $real_name = implode(' ', $name_parts);

            $birthdate = sanitize($_POST['birthdate'] ?? '');
            $bio = sanitize($_POST['bio'] ?? '');
            $selected_layers = $_POST['layers'] ?? [];
            $layers_str = implode(', ', array_map('sanitize', $selected_layers));
            $address = sanitize($_POST['address'] ?? '');
            $purok = sanitize($_POST['purok'] ?? '');
            $barangay = sanitize($_POST['barangay'] ?? '');
            $town = sanitize($_POST['town'] ?? '');
            $city = sanitize($_POST['city'] ?? '');
            $province = sanitize($_POST['province'] ?? '');
            $country = sanitize($_POST['country'] ?? '');
            $zip = sanitize($_POST['zip'] ?? '');
            $store = sanitize($_POST['store'] ?? '');

            // Handle Profile Image Upload
            $avatar_url = 'https://lh3.googleusercontent.com/aida-public/AB6AXuCuom_75fEmAQrawrwRszprdNd7mbTW-uLVHnh7vK0oZIlXiJ51O3bRSjPy2zfOnXs19wgqHa58KiiU1XP4qCp1GqPikps7CByYEWxDFZDEMjR_MV2kTMmnzhbKDq-uDPvFY3WqP3eNZsYoc0oylqlF_NZHGa75-wHJprW5fm2be_lMFq6iWViq07kIzkDKckPdkivgPOHn7-THyinajDZk44gk0FhO2VLeV43FMUgIvOuEMbwlOnmb2a0qY4PnCTGczI2ufBAMAB8';
            $curr_user = User::find_by_id($user_id);
            if ($curr_user && !empty($curr_user['avatar_url'])) {
                $avatar_url = $curr_user['avatar_url'];
            }

            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_image']['tmp_name'];
                $fileName = $_FILES['profile_image']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($fileExtension, $allowedExtensions)) {
                    $uploadFileDir = __DIR__ . '/../assets/uploads/avatars/';
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }

                    $newFileName = 'avatar_' . $user_id . '_' . time() . '.' . $fileExtension;
                    $dest_path = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $avatar_url = 'assets/uploads/avatars/' . $newFileName;
                    }
                }
            }

            $existing_user = User::find_by_email($real_email);
            if ($existing_user && $existing_user['id'] != $user_id) {
                set_flash('danger', 'This email address is already in use by another member.');
                redirect('?route=setup_profile');
            }

            $success = User::update_profile(
                $user_id,
                $firstname,
                $middlename,
                $lastname,
                $extname,
                $real_email,
                $birthdate,
                $bio,
                $layers_str,
                $address,
                $purok,
                $barangay,
                $town,
                $city,
                $province,
                $country,
                $zip,
                $store,
                $avatar_url
            );
            if ($success) {
                $_SESSION['user']['name'] = $real_name;
                $_SESSION['user']['email'] = $real_email;
                $_SESSION['user']['avatar_url'] = $avatar_url;

                $_SESSION['notification'] = [
                    'title' => 'Profile Configured Successfully!',
                    'message' => 'Welcome to Stacey Halo-Halo Club, ' . htmlspecialchars($real_name) . '! Your onboarding is complete and your custom signature layers are active.',
                    'type' => 'success',
                    'redirect' => '?route=home',
                    'btn_text' => 'Enter Stacey Halo-Halo'
                ];

                redirect('?route=notification');
            } else {
                set_flash('danger', 'Failed to update profile. Please try again.');
                redirect('?route=setup_profile');
            }
        }

        global $conn;
        $ingredients_query = $conn->query("SELECT * FROM ingredients WHERE status = 'Active' ORDER BY name ASC");
        $db_ingredients = [];
        if ($ingredients_query) {
            while ($row = $ingredients_query->fetch_assoc()) {
                $db_ingredients[] = $row;
            }
        }

        require __DIR__ . '/../views/auth/setup_profile.php';
    }

    public function notification()
    {
        require_login();

        $title = sanitize($_GET['title'] ?? $_SESSION['notification']['title'] ?? 'Success!');
        $message = sanitize($_GET['message'] ?? $_SESSION['notification']['message'] ?? 'Action completed successfully.');
        $type = sanitize($_GET['type'] ?? $_SESSION['notification']['type'] ?? 'success');
        $redirect = sanitize($_GET['redirect'] ?? $_SESSION['notification']['redirect'] ?? '?route=home');
        $btn_text = sanitize($_GET['btn_text'] ?? $_SESSION['notification']['btn_text'] ?? 'Continue');

        // Clear session notification payload to prevent refresh retention
        unset($_SESSION['notification']);

        require __DIR__ . '/../views/auth/notification.php';
    }

    public function logout()
    {
        if (isset($_SESSION['user'])) {
            $user_id = $_SESSION['user']['id'];
            $username = $_SESSION['user']['username'] ?? $_SESSION['user']['name'] ?? 'unknown';
            User::log_activity($user_id, 'logout', 'User logged out: ' . $username);
            User::set_status($user_id, 'offline');
        }
        session_unset();
        session_destroy();
        session_start();
        set_flash('success', 'You have been logged out successfully.');
        redirect('?route=login');
    }
}
