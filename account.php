<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zip = $_POST['zip'] ?? '';

    try {
        // Update user profile
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, zip = ? WHERE id = ?");
        $stmt->execute([$username, $email, $phone, $address, $city, $state, $zip, $_SESSION['user_id']]);
        
        // Update session data
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        
        $success = 'Profile updated successfully!';
    } catch(PDOException $e) {
        error_log("Profile update error: " . $e->getMessage());
        $error = 'An error occurred while updating your profile. Please try again.';
    }
}

// Handle notification preferences update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notifications'])) {
    $order_updates = isset($_POST['order_updates']) ? 1 : 0;
    $promotional_offers = isset($_POST['promotional_offers']) ? 1 : 0;
    $new_arrivals = isset($_POST['new_arrivals']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("UPDATE users SET notify_order_updates = ?, notify_promotions = ?, notify_new_arrivals = ? WHERE id = ?");
        $stmt->execute([$order_updates, $promotional_offers, $new_arrivals, $_SESSION['user_id']]);
        
        // Log the activity
        $stmt = $pdo->prepare("INSERT INTO account_activity (user_id, type, description) VALUES (?, 'settings', 'Updated notification preferences')");
        $stmt->execute([$_SESSION['user_id']]);
        
        $success = 'Notification preferences updated successfully!';
    } catch(PDOException $e) {
        error_log("Notification preferences update error: " . $e->getMessage());
        $error = 'An error occurred while updating your notification preferences.';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 8) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'New password must be at least 8 characters long.';
                }
            } else {
                $error = 'New passwords do not match.';
            }
        } else {
            $error = 'Current password is incorrect.';
        }
    } catch(PDOException $e) {
        $error = 'An error occurred while changing your password.';
    }
}

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    $error = 'Error fetching user data.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YOUR ACCOUNT - LIFE STYLE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: Montserrat, sans-serif;
        }
        .account-section {
            transition: all 0.3s ease;
        }
        .account-section:hover {
            transform: translateY(-2px);
        }
        .order-card {
            transition: all 0.3s ease;
        }
        .order-card:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body class="bg-white min-h-screen">
    <!-- Header -->
    <header class="border-b">
        <div class="container mx-auto px-4">
            <div class="h-16 flex items-center justify-between">
                <h1 class="text-2xl font-bold"><a href="index.html">LIFE STYLE</a></h1>
                <div class="flex-1 max-w-xl mx-8">
                    <div class="relative">
                        <input type="text" placeholder="Search here..." class="w-full h-10 pl-10 pr-4 rounded-full border border-gray-200 focus:outline-none focus:border-primary">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="relative group cursor-pointer">
                        <div class="flex items-center gap-1">
                            <a href="help.html" class="flex items-center gap-1">
                                <i class="ri-customer-service-line text-xl"></i>
                                <span>24/7 HELP</span>
                                <i class="ri-arrow-down-s-line"></i>
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 cursor-pointer">
                        <a href="account.php" class="flex items-center gap-1">
                            <i class="ri-user-line text-xl"></i>
                            <span>YOUR ACCOUNT</span>
                        </a>
                    </div>
                    <div class="flex items-center gap-1 cursor-pointer">
                        <a href="wishlist.html" class="flex items-center gap-1">
                            <i class="ri-heart-line text-xl"></i>
                            <span>WISHLIST</span>
                        </a>
                    </div>
                    <div class="relative cursor-pointer">
                        <a href="add-to-cart.html">
                            <i class="ri-shopping-bag-line text-xl"></i>
                            <span class="absolute -top-2 -right-2 bg-black text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">0</span>
                        </a>
                    </div>
                </div>
            </div>
            <hr>
            <nav class="h-10 flex items-center justify-center">
                <ul class="flex gap-8">
                    <li><a href="index.html" class="hover:text-primary">HOME</a></li>
                    <li><a href="all-items.html" class="hover:text-primary">SHOP</a></li>
                    <li><a href="#" class="hover:text-primary">WOMEN</a></li>
                    <li><a href="#" class="hover:text-primary">MEN</a></li>
                    <li><a href="#" class="hover:text-primary">KIDS</a></li>
                    <li><a href="#sale" class="hover:text-primary">SALE</a></li>
                    <li><a href="#" class="hover:text-primary">ABOUT US</a></li>
                </ul>
            </nav>
        </div>  
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold mb-8">YOUR ACCOUNT</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <!-- Account Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="account-section bg-gray-50 p-6 rounded-lg shadow-sm">
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ri-user-line text-2xl text-primary"></i>
                        <h2 class="text-xl font-semibold">Profile</h2>
                    </div>
                    <div class="space-y-2">
                        <p class="text-gray-600">Welcome back, <span class="font-semibold"><?php echo htmlspecialchars($user['username']); ?></span></p>
                        <p class="text-gray-600">Email: <?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="text-gray-600">Phone: <?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></p>
                        <button class="mt-4 text-primary hover:underline" onclick="showEditProfile()">Edit Profile</button>
                        <button id="logoutBtn" class="mt-4 text-primary hover:underline">Logout</button>
                    </div>
                </div>

                <div class="account-section bg-gray-50 p-6 rounded-lg shadow-sm">
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ri-map-pin-line text-2xl text-primary"></i>
                        <h2 class="text-xl font-semibold">Address</h2>
                    </div>
                    <div class="space-y-2">
                        <p class="text-gray-600"><?php echo htmlspecialchars($user['address'] ?? 'No address set'); ?></p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($user['city'] ?? ''); ?>, <?php echo htmlspecialchars($user['state'] ?? ''); ?></p>
                        <p class="text-gray-600">ZIP: <?php echo htmlspecialchars($user['zip'] ?? ''); ?></p>
                        <button class="mt-4 text-primary hover:underline" onclick="showEditAddress()">Edit Address</button>
                    </div>
                </div>

                <div class="account-section bg-gray-50 p-6 rounded-lg shadow-sm">
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ri-lock-line text-2xl text-primary"></i>
                        <h2 class="text-xl font-semibold">Security</h2>
                    </div>
                    <div class="space-y-2">
                        <p class="text-gray-600">Last password change: <?php echo htmlspecialchars($user['last_password_change'] ?? 'Never'); ?></p>
                        <button class="mt-4 text-primary hover:underline" onclick="showChangePassword()">Change Password</button>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div id="editProfileForm" class="hidden mb-12">
                <h2 class="text-2xl font-bold mb-6">Edit Profile</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="update_profile" value="1">
                    <div>
                        <label class="block text-gray-700 mb-2">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">Save Changes</button>
                        <button type="button" onclick="hideEditProfile()" class="ml-4 px-6 py-2 border rounded hover:bg-gray-100">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Edit Address Form -->
            <div id="editAddressForm" class="hidden mb-12">
                <h2 class="text-2xl font-bold mb-6">Edit Address</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 mb-2">Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">City</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">State</label>
                        <input type="text" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">ZIP Code</label>
                        <input type="text" name="zip" value="<?php echo htmlspecialchars($user['zip'] ?? ''); ?>" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">Save Changes</button>
                        <button type="button" onclick="hideEditAddress()" class="ml-4 px-6 py-2 border rounded hover:bg-gray-100">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Change Password Form -->
            <div id="changePasswordForm" class="hidden mb-12">
                <h2 class="text-2xl font-bold mb-6">Change Password</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="change_password" value="1">
                    <div>
                        <label class="block text-gray-700 mb-2">Current Password</label>
                        <input type="password" name="current_password" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">New Password</label>
                        <input type="password" name="new_password" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">Change Password</button>
                        <button type="button" onclick="hideChangePassword()" class="ml-4 px-6 py-2 border rounded hover:bg-gray-100">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Recent Orders -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Recent Orders</h2>
                <div class="space-y-4">
                    <?php
                    try {
                        $stmt = $pdo->prepare("SELECT o.*, COUNT(oi.id) as item_count FROM orders o 
                            LEFT JOIN order_items oi ON o.id = oi.order_id 
                            WHERE o.user_id = ? 
                            GROUP BY o.id 
                            ORDER BY o.order_date DESC LIMIT 5");
                        $stmt->execute([$_SESSION['user_id']]);
                        $orders = $stmt->fetchAll();

                        if (count($orders) > 0) {
                            foreach ($orders as $order) {
                                ?>
                                <div class="order-card border rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-semibold">Order #<?php echo $order['id']; ?></h3>
                                            <p class="text-gray-600">Placed on <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                                            <p class="text-gray-600">Items: <?php echo $order['item_count']; ?></p>
                                            <p class="text-gray-600">Total: ₹<?php echo number_format($order['total_amount'], 2); ?></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="px-3 py-1 <?php echo $order['status'] === 'delivered' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> rounded-full text-sm">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                            <button class="mt-2 text-primary hover:underline" onclick="showOrderDetails(<?php echo $order['id']; ?>)">View Details</button>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p class="text-gray-600">No orders found.</p>';
                        }
                    } catch(PDOException $e) {
                        echo '<p class="text-gray-600">Error loading orders.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Wishlist Items -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Your Wishlist</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php
                    try {
                        $stmt = $pdo->prepare("SELECT w.*, p.name, p.price, p.image FROM wishlist w 
                            JOIN products p ON w.product_id = p.id 
                            WHERE w.user_id = ? 
                            ORDER BY w.added_at DESC LIMIT 6");
                        $stmt->execute([$_SESSION['user_id']]);
                        $wishlistItems = $stmt->fetchAll();

                        if (count($wishlistItems) > 0) {
                            foreach ($wishlistItems as $item) {
                                ?>
                                <div class="wishlist-item border rounded-lg p-4">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-48 object-cover rounded mb-4">
                                    <h3 class="font-semibold"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="text-gray-600">₹<?php echo number_format($item['price'], 2); ?></p>
                                    <div class="mt-4 flex justify-between items-center">
                                        <button class="text-primary hover:underline" onclick="addToCart(<?php echo $item['product_id']; ?>)">Add to Cart</button>
                                        <button class="text-red-500 hover:underline" onclick="removeFromWishlist(<?php echo $item['id']; ?>)">Remove</button>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p class="text-gray-600 col-span-3">No items in your wishlist.</p>';
                        }
                    } catch(PDOException $e) {
                        echo '<p class="text-gray-600 col-span-3">Error loading wishlist items.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Payment Methods</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="payment-method border rounded-lg p-4">
                        <div class="flex items-center gap-4 mb-4">
                            <i class="ri-bank-card-line text-2xl text-primary"></i>
                            <h3 class="text-xl font-semibold">Credit/Debit Cards</h3>
                        </div>
                        <div class="space-y-4">
                            <?php
                            try {
                                $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE user_id = ? AND type = 'card'");
                                $stmt->execute([$_SESSION['user_id']]);
                                $cards = $stmt->fetchAll();

                                if (count($cards) > 0) {
                                    foreach ($cards as $card) {
                                        ?>
                                        <div class="card-item border rounded p-3">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="font-semibold">**** **** **** <?php echo substr($card['card_number'], -4); ?></p>
                                                    <p class="text-gray-600">Expires: <?php echo $card['expiry_date']; ?></p>
                                                </div>
                                                <button class="text-red-500 hover:underline" onclick="removePaymentMethod(<?php echo $card['id']; ?>)">Remove</button>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo '<p class="text-gray-600">No cards saved.</p>';
                                }
                            } catch(PDOException $e) {
                                echo '<p class="text-gray-600">Error loading payment methods.</p>';
                            }
                            ?>
                            <button class="text-primary hover:underline" onclick="showAddCardForm()">Add New Card</button>
                        </div>
                    </div>

                    <div class="payment-method border rounded-lg p-4">
                        <div class="flex items-center gap-4 mb-4">
                            <i class="ri-wallet-line text-2xl text-primary"></i>
                            <h3 class="text-xl font-semibold">UPI/Wallets</h3>
                        </div>
                        <div class="space-y-4">
                            <?php
                            try {
                                $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE user_id = ? AND type = 'upi'");
                                $stmt->execute([$_SESSION['user_id']]);
                                $upiMethods = $stmt->fetchAll();

                                if (count($upiMethods) > 0) {
                                    foreach ($upiMethods as $upi) {
                                        ?>
                                        <div class="upi-item border rounded p-3">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="font-semibold"><?php echo htmlspecialchars($upi['upi_id']); ?></p>
                                                    <p class="text-gray-600"><?php echo ucfirst($upi['provider']); ?></p>
                                                </div>
                                                <button class="text-red-500 hover:underline" onclick="removePaymentMethod(<?php echo $upi['id']; ?>)">Remove</button>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo '<p class="text-gray-600">No UPI methods saved.</p>';
                                }
                            } catch(PDOException $e) {
                                echo '<p class="text-gray-600">Error loading payment methods.</p>';
                            }
                            ?>
                            <button class="text-primary hover:underline" onclick="showAddUPIForm()">Add New UPI</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Notification Preferences</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="update_notifications" value="1">
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h3 class="font-semibold">Order Updates</h3>
                            <p class="text-gray-600">Get notified about your order status</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="order_updates" class="sr-only peer" <?php echo ($user['notify_order_updates'] ?? true) ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h3 class="font-semibold">Promotional Offers</h3>
                            <p class="text-gray-600">Receive special offers and discounts</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="promotional_offers" class="sr-only peer" <?php echo ($user['notify_promotions'] ?? true) ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h3 class="font-semibold">New Arrivals</h3>
                            <p class="text-gray-600">Get notified about new products</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="new_arrivals" class="sr-only peer" <?php echo ($user['notify_new_arrivals'] ?? true) ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">Save Preferences</button>
                </form>
            </div>

            <!-- Account Activity -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Account Activity</h2>
                <div class="space-y-4">
                    <?php
                    try {
                        $stmt = $pdo->prepare("SELECT * FROM account_activity WHERE user_id = ? ORDER BY activity_date DESC LIMIT 10");
                        $stmt->execute([$_SESSION['user_id']]);
                        $activities = $stmt->fetchAll();

                        if (count($activities) > 0) {
                            foreach ($activities as $activity) {
                                ?>
                                <div class="activity-item border rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="font-semibold"><?php echo htmlspecialchars($activity['description']); ?></p>
                                            <p class="text-gray-600"><?php echo date('F j, Y g:i A', strtotime($activity['activity_date'])); ?></p>
                                        </div>
                                        <span class="px-3 py-1 <?php echo $activity['type'] === 'login' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?> rounded-full text-sm">
                                            <?php echo ucfirst($activity['type']); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p class="text-gray-600">No recent activity found.</p>';
                        }
                    } catch(PDOException $e) {
                        echo '<p class="text-gray-600">Error loading account activity.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Form visibility functions
        function showEditProfile() {
            document.getElementById('editProfileForm').classList.remove('hidden');
            document.getElementById('editAddressForm').classList.add('hidden');
            document.getElementById('changePasswordForm').classList.add('hidden');
        }

        function hideEditProfile() {
            document.getElementById('editProfileForm').classList.add('hidden');
        }

        function showEditAddress() {
            document.getElementById('editAddressForm').classList.remove('hidden');
            document.getElementById('editProfileForm').classList.add('hidden');
            document.getElementById('changePasswordForm').classList.add('hidden');
        }

        function hideEditAddress() {
            document.getElementById('editAddressForm').classList.add('hidden');
        }

        function showChangePassword() {
            document.getElementById('changePasswordForm').classList.remove('hidden');
            document.getElementById('editProfileForm').classList.add('hidden');
            document.getElementById('editAddressForm').classList.add('hidden');
        }

        function hideChangePassword() {
            document.getElementById('changePasswordForm').classList.add('hidden');
        }

        // Logout functionality
        document.getElementById('logoutBtn').addEventListener('click', function() {
            fetch('logout.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error logging out. Please try again.');
                });
        });

        // New functions for added features
        function showOrderDetails(orderId) {
            // Implement order details modal
            console.log('Showing details for order:', orderId);
        }

        function addToCart(productId) {
            // Implement add to cart functionality
            console.log('Adding product to cart:', productId);
        }

        function removeFromWishlist(itemId) {
            // Implement remove from wishlist functionality
            console.log('Removing item from wishlist:', itemId);
        }

        function showAddCardForm() {
            // Implement add card form modal
            console.log('Showing add card form');
        }

        function showAddUPIForm() {
            // Implement add UPI form modal
            console.log('Showing add UPI form');
        }

        function removePaymentMethod(methodId) {
            // Implement remove payment method functionality
            console.log('Removing payment method:', methodId);
        }
    </script>
</body>
</html> 