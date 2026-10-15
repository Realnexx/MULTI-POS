<?php
$title = "Setup Your Profile | Stacey Halu Halu - Sweet Efficiency";

$current_avatar = $_SESSION['user']['avatar_url'] ?? '';
if (empty($current_avatar) && isset($_SESSION['user']['id'])) {
    $user_db = User::find_by_id($_SESSION['user']['id']);
    $current_avatar = $user_db['avatar_url'] ?? '';
}
if (empty($current_avatar)) {
    $current_avatar = 'https://lh3.googleusercontent.com/aida-public/AB6AXuCuom_75fEmAQrawrwRszprdNd7mbTW-uLVHnh7vK0oZIlXiJ51O3bRSjPy2zfOnXs19wgqHa58KiiU1XP4qCp1GqPikps7CByYEWxDFZDEMjR_MV2kTMmnzhbKDq-uDPvFY3WqP3eNZsYoc0oylqlF_NZHGa75-wHJprW5fm2be_lMFq6iWViq07kIzkDKckPdkivgPOHn7-THyinajDZk44gk0FhO2VLeV43FMUgIvOuEMbwlOnmb2a0qY4PnCTGczI2ufBAMAB8';
}

// Fetch active branches dynamically from the database
global $conn;
$branches_query = $conn->query("SELECT name, address, latitude, longitude FROM branches WHERE status = 'Active' ORDER BY name ASC");
$db_branches = [];
if ($branches_query) {
    while ($row = $branches_query->fetch_assoc()) {
        $db_branches[] = [
            'name' => $row['name'],
            'address' => $row['address'],
            'latitude' => $row['latitude'] !== null ? (float)$row['latitude'] : null,
            'longitude' => $row['longitude'] !== null ? (float)$row['longitude'] : null
        ];
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<?php require __DIR__.'/../partials/site_favicon.php'; ?>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .step-transition {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #b60e3d !important;
            box-shadow: 0 0 0 2px rgba(182, 14, 61, 0.1) !important;
        }
        select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: none !important;
        }
        select::-ms-expand {
            display: none !important;
        }
    </style>
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "on-tertiary-fixed": "#271900",
                    "on-tertiary": "#ffffff",
                    "tertiary-container": "#996c00",
                    "surface-container-highest": "#e1e3e4",
                    "secondary-fixed": "#f2daff",
                    "on-secondary-container": "#fffbff",
                    "on-secondary": "#ffffff",
                    "on-primary-container": "#fffbff",
                    "outline-variant": "#e2bec0",
                    "on-primary-fixed-variant": "#91002d",
                    "error-container": "#ffdad6",
                    "inverse-on-surface": "#f0f1f2",
                    "on-tertiary-container": "#fffbff",
                    "secondary": "#8429c8",
                    "surface-dim": "#d9dadb",
                    "surface-variant": "#e1e3e4",
                    "primary-fixed-dim": "#ffb2b8",
                    "primary-container": "#da3054",
                    "secondary-container": "#9e49e3",
                    "surface": "#f8f9fa",
                    "primary": "#b60e3d",
                    "tertiary-fixed-dim": "#ffba27",
                    "secondary-fixed-dim": "#e0b6ff",
                    "surface-container-high": "#e7e8e9",
                    "on-primary-fixed": "#40000f",
                    "surface-container-lowest": "#ffffff",
                    "surface-bright": "#f8f9fa",
                    "on-tertiary-fixed-variant": "#5e4100",
                    "on-secondary-fixed": "#2e004e",
                    "on-secondary-fixed-variant": "#6b00af",
                    "on-primary": "#ffffff",
                    "surface-tint": "#ba1340",
                    "inverse-primary": "#ffb2b8",
                    "background": "#f8f9fa",
                    "primary-fixed": "#ffdadb",
                    "on-error-container": "#93000a",
                    "outline": "#8e6f71",
                    "surface-container-low": "#f3f4f5",
                    "on-surface": "#191c1d",
                    "tertiary": "#7a5500",
                    "on-error": "#ffffff",
                    "on-background": "#191c1d",
                    "tertiary-fixed": "#ffdea9",
                    "inverse-surface": "#2e3132",
                    "error": "#ba1a1a",
                    "on-surface-variant": "#5a4042",
                    "surface-container": "#edeeef"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "sm": "12px",
                    "lg": "40px",
                    "xl": "64px",
                    "margin-mobile": "16px",
                    "base": "8px",
                    "margin-desktop": "32px",
                    "md": "24px",
                    "gutter": "24px",
                    "xs": "4px"
            },
            "fontFamily": {
                    "label-sm": ["Inter"],
                    "body-lg": ["Inter"],
                    "label-md": ["Inter"],
                    "headline-md": ["Montserrat"],
                    "headline-lg": ["Montserrat"],
                    "display-lg": ["Montserrat"],
                    "body-md": ["Inter"],
                    "display-lg-mobile": ["Montserrat"]
            },
            "fontSize": {
                    "label-sm": ["12px", {"lineHeight": "1", "fontWeight": "500"}],
                    "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                    "label-md": ["14px", {"lineHeight": "1", "letterSpacing": "0.05em", "fontWeight": "600"}],
                    "headline-md": ["24px", {"lineHeight": "1.4", "fontWeight": "600"}],
                    "headline-lg": ["32px", {"lineHeight": "1.3", "fontWeight": "600"}],
                    "display-lg": ["48px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                    "display-lg-mobile": ["36px", {"lineHeight": "1.2", "fontWeight": "700"}]
            }
          },
        },
      }
    </script>
</head>
<body class="bg-surface text-on-surface font-body-md selection:bg-primary-fixed-dim selection:text-primary">
<?php require_once __DIR__ . '/../partials/toast.php'; ?>



<main class="min-h-screen py-xl px-margin-mobile md:px-0">
    <div class="max-w-5xl mx-auto">
        <!-- Header Section -->
        <header class="text-center mb-xl">
            <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg text-on-surface mb-xs">Craft Your Experience</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant">Join the community of sweet efficiency and layers of delight.</p>
        </header>

        <!-- Stepper -->
        <div class="flex justify-between items-center mb-xl max-w-2xl mx-auto px-md relative">
            <div class="absolute top-1/2 left-0 w-full h-[2px] bg-surface-container-highest -z-10 -translate-y-1/2"></div>
            <!-- Step 1 Indicator -->
            <div class="flex flex-col items-center gap-xs">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary text-on-primary font-bold shadow-md transition-all duration-300" id="step-indicator-1">1</div>
                <span class="font-label-sm text-label-sm text-primary">Personal</span>
            </div>
            <!-- Step 2 Indicator -->
            <div class="flex flex-col items-center gap-xs">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-surface-container-highest text-on-surface-variant font-bold transition-all duration-300" id="step-indicator-2">2</div>
                <span class="font-label-sm text-label-sm text-on-surface-variant">Taste</span>
            </div>
            <!-- Step 3 Indicator -->
            <div class="flex flex-col items-center gap-xs">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-surface-container-highest text-on-surface-variant font-bold transition-all duration-300" id="step-indicator-3">3</div>
                <span class="font-label-sm text-label-sm text-on-surface-variant">Delivery</span>
            </div>
        </div>

        <!-- Dynamic Flash Alert Block -->
        <?php $flash = get_flash(); if ($flash): ?>
            <div class="max-w-2xl mx-auto mb-lg">
                <div class="alert alert-<?= $flash['type'] === 'danger' ? 'danger' : 'success' ?> shadow-sm rounded-xl border-0 py-3.5 px-4 flex items-center gap-3" role="alert">
                    <?php if ($flash['type'] === 'success'): ?>
                        <i class="fa-solid fa-circle-check text-success fs-5"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-circle-exclamation text-danger fs-5"></i>
                    <?php endif; ?>
                    <div class="font-body-md text-sm text-on-surface leading-tight"><?= htmlspecialchars($flash['message']) ?></div>
                </div>
            </div>
        <?php endif; ?>

        <form class="relative overflow-hidden" id="profile-setup-form" action="<?= url('?route=setup_profile') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <!-- Step 1: Personal Info -->
            <div class="step-transition w-full opacity-100 translate-x-0" id="step-1">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
                    <!-- Imagery Sidebar -->
                    <div class="md:col-span-5 rounded-xl overflow-hidden h-[400px] md:h-auto shadow-sm relative">
                        <img alt="Halo-halo Parfait" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBzsE5R75Ca6g7E9ROczvJO1x0VVafs2OOSlB5jp-hdFxQHhuTfibSiGTtTTaPyOXaUdv3BMuWQf--DLN5MeX7NmH8ylxUWRGqxYr03eQB5CY6GQ9irOx4B-RXOjJqrBlgMWMFupyu-VbPrlA0fv82SRlFGI0CtyE81xVsYnKblB-GldwFiYWdOuzD0FTNxzmtwTi-1ZEN5CwTGMYIDnJTI3Tsrn14dhkL9G4m8rHEEpO0x9wdvNBwQ8M7g5iv13bwtXtVI2QapunU"/>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                        <div class="absolute bottom-md left-md text-white">
                            <p class="font-headline-md text-headline-md leading-tight">Authentic Flavors</p>
                            <p class="font-body-md text-body-md opacity-90">Every layer tells a story.</p>
                        </div>
                    </div>
                    <!-- Form Content -->
                    <div class="md:col-span-7 bg-surface-container-lowest p-md md:p-xl rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)]">
                        <div class="flex flex-col items-center mb-lg">
                            <div class="relative group cursor-pointer" onclick="document.getElementById('profile_image_input').click()">
                                <div class="w-32 h-32 rounded-full border-4 border-primary-fixed bg-surface-container overflow-hidden">
                                    <img id="profile_image_preview" alt="User Profile" class="w-full h-full object-cover" src="<?= strpos($current_avatar, 'http') === 0 ? $current_avatar : url($current_avatar) ?>"/>
                                </div>
                                <div class="absolute bottom-0 right-0 bg-primary text-on-primary p-2 rounded-full shadow-lg group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                                </div>
                                <input type="file" id="profile_image_input" name="profile_image" accept="image/*" class="hidden" onchange="previewProfileImage(this)">
                            </div>
                            <span class="font-label-md text-label-md mt-sm text-on-surface-variant font-bold">Profile Photo</span>
                        </div>
                        <div class="space-y-md">
                            <!-- Name Inputs Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                                <div class="flex flex-col gap-xs">
                                    <label class="font-label-md text-label-md text-on-surface font-bold">First Name</label>
                                    <input class="w-full bg-surface-container-low border-none rounded-lg p-sm focus:ring-2 focus:ring-primary/20" name="firstname" placeholder="Stacey" required type="text"/>
                                </div>
                                <div class="flex flex-col gap-xs">
                                    <label class="font-label-md text-label-md text-on-surface font-bold flex justify-between">
                                        <span>Middle Name</span>
                                        <span class="text-xs text-on-surface-variant/60 font-normal">Optional</span>
                                    </label>
                                    <input class="w-full bg-surface-container-low border-none rounded-lg p-sm focus:ring-2 focus:ring-primary/20" name="middlename" placeholder="Maria" type="text"/>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                                <div class="flex flex-col gap-xs">
                                    <label class="font-label-md text-label-md text-on-surface font-bold">Last Name</label>
                                    <input class="w-full bg-surface-container-low border-none rounded-lg p-sm focus:ring-2 focus:ring-primary/20" name="lastname" placeholder="Lopez" required type="text"/>
                                </div>
                                <div class="flex flex-col gap-xs">
                                    <label class="font-label-md text-label-md text-on-surface font-bold flex justify-between">
                                        <span>Extension Name</span>
                                        <span class="text-xs text-on-surface-variant/60 font-normal">Optional</span>
                                    </label>
                                    <input class="w-full bg-surface-container-low border-none rounded-lg p-sm focus:ring-2 focus:ring-primary/20" name="extname" placeholder="Jr., III, etc." type="text"/>
                                </div>
                            </div>

                            <div class="flex flex-col gap-xs">
                                <label class="font-label-md text-label-md text-on-surface font-bold">Email Address</label>
                                <input class="w-full bg-surface-container-low border-none rounded-lg p-sm focus:ring-2 focus:ring-primary/20" name="email" placeholder="stacey@example.com" required type="email"/>
                            </div>

                            <!-- Styled visible Birthday Date Field -->
                            <div class="flex flex-col gap-xs">
                                <label class="font-label-md text-label-md text-on-surface font-bold">Birthday</label>
                                <div class="relative w-full">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-primary font-bold pointer-events-none" style="font-size: 20px;">calendar_month</span>
                                    <input class="w-full bg-surface-container-low border-none rounded-lg p-sm pl-10 focus:ring-2 focus:ring-primary/20 text-on-surface" name="birthdate" required type="date"/>
                                </div>
                                <p class="font-label-sm text-label-sm text-primary font-bold">Get a free Halo-halo on your birthday!</p>
                            </div>

                            <div class="flex flex-col gap-xs">
                                <label class="font-label-md text-label-md text-on-surface font-bold">Bio</label>
                                <textarea class="w-full bg-surface-container-low border-none rounded-lg p-sm focus:ring-2 focus:ring-primary/20 resize-none" name="bio" placeholder="Tell us about your sweet tooth..." rows="2"></textarea>
                            </div>
                        </div>
                        <div class="mt-lg flex justify-end">
                            <button class="bg-primary text-white font-bold px-xl py-sm rounded-full font-label-md text-label-md hover:bg-primary-container transition-all active:scale-95 flex items-center gap-xs" onclick="goToStep(2)" type="button">
                                Next Step
                                <span class="material-symbols-outlined">arrow_forward</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Taste Preferences -->
            <div class="step-transition w-full opacity-0 translate-x-full hidden" id="step-2">
                <div class="bg-surface-container-lowest p-md md:p-xl rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)]">
                    <div class="flex items-center gap-sm mb-lg">
                        <div class="w-1 bg-primary h-8 rounded-full"></div>
                        <h2 class="font-headline-md text-headline-md font-bold">Your Signature Layers</h2>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-lg">Select your favorite ingredients. We'll use these to suggest special parfaits just for you.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-sm sm:gap-md mb-xl">
                        <?php if (!empty($db_ingredients)): ?>
                            <?php foreach ($db_ingredients as $ing): ?>
                                <label class="group relative cursor-pointer select-none">
                                    <input class="peer hidden" name="layers[]" value="<?= htmlspecialchars($ing['name']) ?>" type="checkbox"/>
                                    <div class="p-sm sm:p-md bg-surface-container-low rounded-xl border-2 border-transparent peer-checked:border-primary peer-checked:bg-primary-fixed transition-all flex flex-col items-center gap-xs sm:gap-sm text-center h-full hover:bg-surface-container-high shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full overflow-hidden mb-xs sm:mb-sm border-2 border-outline-variant shadow-sm group-hover:scale-105 transition-transform duration-300">
                                            <img src="<?= htmlspecialchars($ing['image_url']) ?>" alt="<?= htmlspecialchars($ing['name']) ?>" class="w-full h-full object-cover"/>
                                        </div>
                                        <span class="font-label-sm sm:font-label-md text-label-sm sm:text-label-md text-on-surface-variant peer-checked:text-primary font-bold leading-tight"><?= htmlspecialchars($ing['name']) ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="col-span-full text-center text-on-surface-variant opacity-75">No ingredients available.</p>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-row justify-between items-center gap-md">
                        <button class="text-primary font-label-md text-label-sm sm:text-label-md px-sm sm:px-lg py-sm flex items-center gap-xs hover:bg-primary-fixed rounded-full transition-all font-bold" onclick="goToStep(1)" type="button">
                            <span class="material-symbols-outlined">arrow_back</span>
                            Back
                        </button>
                        <button class="bg-primary text-white px-md sm:px-xl py-sm rounded-full font-label-md text-label-sm sm:text-label-md hover:bg-primary-container transition-all active:scale-95 flex items-center gap-xs font-bold shadow-lg shadow-primary/10" onclick="goToStep(3)" type="button">
                            Delivery Details
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Delivery Details -->
            <div class="step-transition w-full opacity-0 translate-x-full hidden" id="step-3">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
                    <!-- Form Content -->
                    <div class="md:col-span-8 bg-surface-container-lowest p-md md:p-xl rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)]">
                        <h2 class="font-headline-md text-headline-md mb-lg font-bold">Where should we deliver?</h2>
                        <div class="space-y-md">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                                <div class="flex flex-col gap-xs">
                                    <label class="font-label-md text-label-md text-on-surface font-bold">Country</label>
                                    <div class="relative">
                                        <select class="w-full bg-surface-container-low border-none rounded-lg p-sm focus:ring-2 focus:ring-primary/20 appearance-none font-bold" name="country" id="country_select">
                                            <option value="Philippines">Philippines</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-md flex items-center pointer-events-none">
                                            <span class="material-symbols-outlined text-on-surface-variant">expand_more</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-xs">
                                    <label class="font-label-md text-label-md text-on-surface font-bold">Province</label>
                                    <div class="relative">
                                        <select class="w-full bg-surface-container-low border-none rounded-lg p-sm focus:ring-2 focus:ring-primary/20 appearance-none font-bold animate-pulse" name="province" id="province_select" required>
                                            <option value="">Loading Provinces...</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-md flex items-center pointer-events-none">
                                            <span class="material-symbols-outlined text-on-surface-variant">expand_more</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                                <div class="flex flex-col gap-xs">
                                    <label class="font-label-md text-label-md text-on-surface font-bold">City / Municipality (Town)</label>
                                    <div class="relative">
                                        <select class="w-full bg-surface-container-low border-none rounded-lg p-sm focus:ring-2 focus:ring-primary/20 appearance-none font-bold" name="city" id="city_select" required>
                                            <option value="">Select Province First</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-md flex items-center pointer-events-none">
                                            <span class="material-symbols-outlined text-on-surface-variant">expand_more</span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="town" id="town_input" />
                                </div>
                                <div class="flex flex-col gap-xs">
                                    <label class="font-label-md text-label-md text-on-surface font-bold">Barangay</label>
                                    <div class="relative">
                                        <select class="w-full bg-surface-container-low border-none rounded-lg p-sm focus:ring-2 focus:ring-primary/20 appearance-none font-bold" name="barangay" id="barangay_select" required>
                                            <option value="">Select City First</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-md flex items-center pointer-events-none">
                                            <span class="material-symbols-outlined text-on-surface-variant">expand_more</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Automated Address and Zip Code inputs populated dynamically by Geocoding and Map pin placement -->
                            <input type="hidden" name="purok" id="purok_input" value="" />
                            <input type="hidden" name="zip" id="zip_input" value="1000" />
                            <input type="hidden" name="address" id="address_input" />

                            <hr class="border-outline-variant my-lg"/>
                            
                            <div class="flex flex-col gap-xs">
                                <label class="font-label-md text-label-md text-on-surface font-bold">Closest Stacey Halu Halu Store (Auto-Selected)</label>
                                <div class="relative">
                                    <select class="w-full bg-surface-container-low border-none rounded-lg p-sm focus:ring-2 focus:ring-primary/20 appearance-none font-bold animate-pulse" name="store" id="store_select">
                                        <?php if (!empty($db_branches)): ?>
                                            <?php foreach ($db_branches as $branch): ?>
                                                <option value="<?= htmlspecialchars($branch['name']) ?>"><?= htmlspecialchars($branch['name']) ?></option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="Quezon City (Flagship)">Quezon City (Flagship)</option>
                                        <?php endif; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-md flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-on-surface-variant">expand_more</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-xl flex flex-row justify-between items-center gap-md">
                            <button class="text-primary font-label-md text-label-sm sm:text-label-md px-sm sm:px-lg py-sm flex items-center gap-xs hover:bg-primary-fixed rounded-full transition-all font-bold" onclick="goToStep(2)" type="button">
                                <span class="material-symbols-outlined">arrow_back</span>
                                Back
                            </button>
                            <button class="bg-primary text-white px-md sm:px-xl py-sm rounded-full font-label-md text-label-sm sm:text-label-md hover:bg-primary-container shadow-lg shadow-primary/20 active:scale-95 transition-all font-bold" type="submit" id="submitBtn">
                                Complete Profile
                            </button>
                        </div>
                    </div>
                    <!-- Mini Map Visual -->
                    <div class="md:col-span-4 flex flex-col gap-md">
                        <div class="rounded-xl overflow-hidden shadow-sm relative border border-outline-variant" id="delivery-map" style="height: 250px; z-index: 10;">
                            <!-- Leaflet Map Container -->
                        </div>
                        <div class="bg-primary-fixed p-md rounded-xl">
                            <h3 class="font-label-md text-label-md text-on-primary-fixed-variant mb-xs font-bold flex items-center gap-xs">
                                <span class="material-symbols-outlined text-[18px]">my_location</span>
                                Interactive Pin Map
                            </h3>
                            <p class="font-body-md text-body-sm text-on-primary-fixed-variant opacity-80 leading-normal" id="map-status">
                                Drag the pin on the map to pinpoint your exact chilling location!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- Restore Draft Prompt Modal -->
<div id="restore-draft-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
    <style>
        #restore-draft-modal {
            transition: opacity 0.3s ease;
        }
        #restore-draft-modal > div {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
    </style>
    <div class="bg-surface-container-lowest p-lg sm:p-xl rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-outline-variant max-w-md w-11/12 mx-auto translate-y-8 transition-transform duration-300">
        <div class="flex flex-col sm:flex-row items-center gap-sm sm:gap-md mb-md text-center sm:text-left">
            <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0 shadow-sm">
                <span class="material-symbols-outlined text-[24px]">restart_alt</span>
            </div>
            <div class="flex flex-col">
                <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface leading-tight">Resume Setup?</h3>
                <span class="font-label-sm text-[10px] sm:text-xs text-primary font-bold tracking-wider uppercase mt-0.5">Unsaved Progress Found</span>
            </div>
        </div>
        <p class="font-body-md text-body-md text-on-surface-variant mb-lg leading-relaxed text-center sm:text-left">
            We found some unsaved onboarding progress from your previous visit. Would you like to restore your details and pick up right where you left off?
        </p>
        <div class="flex flex-col-reverse sm:flex-row gap-sm sm:gap-md justify-end items-center">
            <button onclick="discardDraft()" class="w-full sm:w-auto px-lg py-sm rounded-full font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-high transition-all text-center font-bold" type="button">
                Start Fresh
            </button>
            <button onclick="restoreDraft()" class="w-full sm:w-auto px-xl py-sm bg-primary text-white rounded-full font-label-md text-label-md hover:bg-primary-container shadow-lg shadow-primary/20 transition-all font-bold text-center" type="button">
                Restore Progress
            </button>
        </div>
    </div>
</div>

<!-- Enhanced Premium Footer -->
<?php include __DIR__ . '/../partials/footer_home.php'; ?>

<script>
    const form = document.getElementById('profile-setup-form');
    const userId = <?= json_encode($_SESSION['user']['id'] ?? 'guest') ?>;
    const draftKey = 'haluhalu_draft_user_' + userId;
    let currentActiveStep = 1;

    // Save draft state to localStorage in real-time
    function saveDraftState() {
        if (!form) return;
        const state = {
            inputs: {},
            layers: [],
            currentStep: currentActiveStep
        };

        const formData = new FormData(form);
        for (let [key, val] of formData.entries()) {
            if (key === 'csrf_token' || key === 'profile_image') continue;
            if (key === 'layers[]') {
                state.layers.push(val);
            } else {
                state.inputs[key] = val;
            }
        }

        localStorage.setItem(draftKey, JSON.stringify(state));
    }

    // Attach real-time input event listeners
    if (form) {
        form.querySelectorAll('input, textarea, select').forEach(el => {
            el.addEventListener('input', saveDraftState);
            el.addEventListener('change', saveDraftState);
        });
    }

    function goToStep(stepNumber) {
        const steps = [1, 2, 3];
        steps.forEach(num => {
            const stepEl = document.getElementById(`step-${num}`);
            const indicator = document.getElementById(`step-indicator-${num}`);
            
            if (num === stepNumber) {
                stepEl.classList.remove('hidden', 'opacity-0', 'translate-x-full', '-translate-x-full');
                stepEl.classList.add('opacity-100', 'translate-x-0');
                
                indicator.classList.remove('bg-surface-container-highest', 'text-on-surface-variant');
                indicator.classList.add('bg-primary', 'text-on-primary', 'shadow-md');
            } else if (num < stepNumber) {
                stepEl.classList.add('hidden', 'opacity-0', '-translate-x-full');
                stepEl.classList.remove('opacity-100', 'translate-x-0');
                
                indicator.classList.remove('bg-surface-container-highest', 'text-on-surface-variant');
                indicator.classList.add('bg-primary', 'text-on-primary');
            } else {
                stepEl.classList.add('hidden', 'opacity-0', 'translate-x-full');
                stepEl.classList.remove('opacity-100', 'translate-x-0');
                
                indicator.classList.add('bg-surface-container-highest', 'text-on-surface-variant');
                indicator.classList.remove('bg-primary', 'text-on-primary', 'shadow-md');
            }
        });

        currentActiveStep = stepNumber;
        saveDraftState();
        
        if (stepNumber === 3) {
            setTimeout(() => {
                initMap();
                if (map) {
                    map.invalidateSize();
                }
            }, 100);
        }
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Draft Modal Actions
    function closeRestoreModal() {
        const modal = document.getElementById('restore-draft-modal');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
        modal.classList.add('opacity-0', 'pointer-events-none');
        const inner = modal.querySelector('div');
        inner.classList.remove('translate-y-0');
        inner.classList.add('translate-y-8');
    }

    function discardDraft() {
        localStorage.removeItem(draftKey);
        closeRestoreModal();
    }

    function restoreDraft() {
        const savedDraft = localStorage.getItem(draftKey);
        if (!savedDraft) return;

        try {
            const draft = JSON.parse(savedDraft);
            
            // Set up pending async geographic dropdown loaders
            if (draft.inputs) {
                window.pendingProvince = draft.inputs.province || '';
                window.pendingCity = draft.inputs.city || '';
                window.pendingBarangay = draft.inputs.barangay || '';
            }

            // Restore text, textarea, select inputs
            if (draft.inputs && form) {
                for (let [name, val] of Object.entries(draft.inputs)) {
                    if (name === 'province' || name === 'city' || name === 'barangay') continue;
                    const field = form.querySelector(`[name="${name}"]`);
                    if (field) {
                        field.value = val;
                        field.dispatchEvent(new Event('input'));
                    }
                }
            }

            // Restore checkboxes for layers
            if (draft.layers && form) {
                form.querySelectorAll('input[name="layers[]"]').forEach(cb => {
                    cb.checked = draft.layers.includes(cb.value);
                    cb.dispatchEvent(new Event('change'));
                });
            }

            // Restore current step
            if (draft.currentStep) {
                goToStep(parseInt(draft.currentStep));
            }

            // Re-trigger province load to cascade populate
            loadProvinces();

        } catch (e) {
            console.error("Failed to restore draft", e);
        }

        closeRestoreModal();
    }

    // Checking for saved draft on load
    window.addEventListener('DOMContentLoaded', () => {
        const savedDraft = localStorage.getItem(draftKey);
        if (savedDraft) {
            try {
                const draft = JSON.parse(savedDraft);
                const hasData = Object.values(draft.inputs || {}).some(v => v.trim() !== '') || (draft.layers && draft.layers.length > 0);
                
                if (hasData) {
                    const modal = document.getElementById('restore-draft-modal');
                    if (modal) {
                        modal.classList.remove('opacity-0', 'pointer-events-none');
                        modal.classList.add('opacity-100', 'pointer-events-auto');
                        const inner = modal.querySelector('div');
                        if (inner) {
                            inner.classList.remove('translate-y-8');
                            inner.classList.add('translate-y-0');
                        }
                    }
                } else {
                    loadProvinces();
                }
            } catch (e) {
                console.error("Error reading setup draft state", e);
                loadProvinces();
            }
        } else {
            loadProvinces();
        }
    });

    // Loading feedback for profile form & clearing draft upon submit
    const profileForm = document.getElementById('profile-setup-form');
    if (profileForm) {
        profileForm.addEventListener('submit', () => {
            localStorage.removeItem(draftKey); // Finalized, clear draft!
            const btn = document.getElementById('submitBtn');
            if (btn) {
                btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving profile...`;
                btn.classList.add('opacity-75');
            }
        });
    }

    function previewProfileImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profile_image_preview');
                if (preview) {
                    preview.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // PSGC Dynamic Select Handlers
    const provinceSelect = document.getElementById('province_select');
    const citySelect = document.getElementById('city_select');
    const barangaySelect = document.getElementById('barangay_select');

    function loadProvinces() {
        fetch('https://psgc.gitlab.io/api/provinces.json')
            .then(res => res.json())
            .then(provinces => {
                provinces.sort((a, b) => a.name.localeCompare(b.name));
                provinceSelect.innerHTML = '<option value="">Select Province</option>';
                provinces.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.name;
                    opt.setAttribute('data-code', p.code);
                    opt.textContent = p.name;
                    provinceSelect.appendChild(opt);
                });
                
                provinceSelect.classList.remove('animate-pulse');
                
                if (window.pendingProvince) {
                    provinceSelect.value = window.pendingProvince;
                    window.pendingProvince = null;
                    provinceSelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(err => {
                console.error("Error loading PSGC provinces", err);
                provinceSelect.innerHTML = '<option value="">Failed to load provinces</option>';
            });
    }

    provinceSelect.addEventListener('change', () => {
        const selectedOpt = provinceSelect.options[provinceSelect.selectedIndex];
        const code = selectedOpt ? selectedOpt.getAttribute('data-code') : '';
        
        citySelect.innerHTML = '<option value="">Loading Cities...</option>';
        barangaySelect.innerHTML = '<option value="">Select City First</option>';
        
        if (!code) {
            citySelect.innerHTML = '<option value="">Select Province First</option>';
            return;
        }

        fetch(`https://psgc.gitlab.io/api/provinces/${code}/cities-municipalities.json`)
            .then(res => res.json())
            .then(cities => {
                cities.sort((a, b) => a.name.localeCompare(b.name));
                citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                cities.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.name;
                    opt.setAttribute('data-code', c.code);
                    opt.textContent = c.name;
                    citySelect.appendChild(opt);
                });

                if (window.pendingCity) {
                    citySelect.value = window.pendingCity;
                    window.pendingCity = null;
                    citySelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(err => {
                console.error("Error loading PSGC cities", err);
                citySelect.innerHTML = '<option value="">Failed to load cities</option>';
            });
            
        updateFullAddress();
    });

    citySelect.addEventListener('change', () => {
        const selectedOpt = citySelect.options[citySelect.selectedIndex];
        const code = selectedOpt ? selectedOpt.getAttribute('data-code') : '';
        
        barangaySelect.innerHTML = '<option value="">Loading Barangays...</option>';
        document.getElementById('town_input').value = citySelect.value;
        
        if (!code) {
            barangaySelect.innerHTML = '<option value="">Select City First</option>';
            return;
        }

        fetch(`https://psgc.gitlab.io/api/cities-municipalities/${code}/barangays.json`)
            .then(res => res.json())
            .then(barangays => {
                barangays.sort((a, b) => a.name.localeCompare(b.name));
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                barangays.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.name;
                    opt.textContent = b.name;
                    barangaySelect.appendChild(opt);
                });

                if (window.pendingBarangay) {
                    barangaySelect.value = window.pendingBarangay;
                    window.pendingBarangay = null;
                    barangaySelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(err => {
                console.error("Error loading PSGC barangays", err);
                barangaySelect.innerHTML = '<option value="">Failed to load barangays</option>';
            });
            
        geocodeAddress(`${citySelect.value}, ${provinceSelect.value}, Philippines`);
        updateFullAddress();
    });

    barangaySelect.addEventListener('change', () => {
        geocodeAddress(`${barangaySelect.value}, ${citySelect.value}, ${provinceSelect.value}, Philippines`);
        updateFullAddress();
    });

    document.getElementById('purok_input').addEventListener('input', updateFullAddress);

    function updateFullAddress() {
        const purok = document.getElementById('purok_input').value;
        const brgy = barangaySelect.value;
        const city = citySelect.value;
        const prov = provinceSelect.value;
        const country = document.getElementById('country_select').value;
        
        const full = [purok, brgy, city, prov, country].filter(Boolean).join(', ');
        document.getElementById('address_input').value = full;
        saveDraftState();
    }

    // Leaflet Interactive Map Handlers
    let map, marker;
    function initMap() {
        if (map) return;
        
        // Center around Bohol, Philippines by default
        let startLat = 9.8500;
        let startLng = 124.1435;
        
        map = L.map('delivery-map').setView([startLat, startLng], 10);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);
        
        marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);
        
        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            updateLocationFromCoords(position.lat, position.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateLocationFromCoords(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateLocationFromCoords(lat, lng) {
        const statusEl = document.getElementById('map-status');
        if (statusEl) {
            statusEl.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Pinning exact coordinates: <strong>${lat.toFixed(4)}, ${lng.toFixed(4)}</strong>`;
        }
        
        // Auto-select nearest store based on pin coordinates
        findNearestStore(lat, lng);

        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.address) {
                    const addr = data.address;
                    const road = addr.road || addr.suburb || addr.neighbourhood || addr.hamlet || addr.village || '';
                    const postcode = addr.postcode || '';
                    
                    if (statusEl) {
                        statusEl.innerHTML = `<span class="material-symbols-outlined text-[16px] text-green-600 align-middle mr-1">check_circle</span> Pin mapped: <span class="font-bold">${data.display_name.split(',').slice(0,3).join(',')}</span>`;
                    }
                    
                    if (road) {
                        document.getElementById('purok_input').value = road;
                    } else {
                        document.getElementById('purok_input').value = addr.quarter || addr.suburb || '';
                    }

                    if (postcode) {
                        document.getElementById('zip_input').value = postcode;
                    } else {
                        document.getElementById('zip_input').value = '1000'; // fallback
                    }

                    updateFullAddress();
                }
            })
            .catch(err => {
                console.error("Nominatim reverse geocoding failed", err);
            });
    }

    function geocodeAddress(query) {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
            .then(res => res.json())
            .then(results => {
                if (results && results.length > 0 && map && marker) {
                    const lat = parseFloat(results[0].lat);
                    const lon = parseFloat(results[0].lon);
                    map.setView([lat, lon], 14);
                    marker.setLatLng([lat, lon]);
                    findNearestStore(lat, lon);
                }
            })
            .catch(err => console.error("Geocoding failed", err));
    }

    // Distance Calculation and Auto-Store Selection
    function getHaversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Radius of earth in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function findNearestStore(lat, lng) {
        const storeBranches = <?= json_encode($db_branches) ?>.filter(s => s.latitude !== null && s.longitude !== null);

        let closestStore = storeBranches[0];
        let minDistance = Infinity;

        storeBranches.forEach(store => {
            const dist = getHaversineDistance(lat, lng, store.lat, store.lng);
            if (dist < minDistance) {
                minDistance = dist;
                closestStore = store;
            }
        });

        const storeSelect = document.getElementById('store_select');
        if (storeSelect) {
            storeSelect.value = closestStore.name;
            storeSelect.classList.remove('animate-pulse');
            
            // Premium visual flash highlight feedback
            storeSelect.classList.add('bg-primary-fixed', 'text-primary');
            setTimeout(() => {
                storeSelect.classList.remove('bg-primary-fixed', 'text-primary');
            }, 1000);
        }
    }
</script>
</body>
</html>
