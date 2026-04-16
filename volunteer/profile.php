<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['volunteer', 'rescuer'])) {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = "";
$error = "";

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');

    if (!preg_match("/^[a-zA-Z\s]+$/", $username)) {
        $error = "Name can only contain letters and spaces.";
    } elseif (!empty($phone) && !preg_match("/^\d{1,10}$/", $phone)) {
        $error = "Phone number must contain only numbers and cannot exceed 10 digits.";
    } else {
    $latitude = !empty($_POST['latitude']) ? (float) $_POST['latitude'] : 'NULL';
    $longitude = !empty($_POST['longitude']) ? (float) $_POST['longitude'] : 'NULL';
    // Password Update Logic
    $passwordSql = "";
    if (!empty($_POST['new_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Verify current password
        $verify = $conn->query("SELECT password FROM users WHERE id=$userId");
        $row = $verify->fetch_assoc();

        if ($currentPassword !== $row['password']) {
            $error = "Current password is incorrect.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match.";
        } else {
            $passwordSql = ", password='$newPassword'";
        }
    }

    if (empty($error)) {
        $updateSql = "UPDATE users SET username='$username', email='$email', phone='$phone', latitude=$latitude, longitude=$longitude $passwordSql WHERE id=$userId";
        if ($conn->query($updateSql)) {
            $_SESSION['username'] = $username;
            $message = "Profile updated successfully!";
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
        }
    }
}

$userResult = $conn->query("SELECT * FROM users WHERE id=$userId");
$user = $userResult->fetch_assoc();

$basePath = '../';
$hideNavbar = true;
$hideFooter = true;
include '../includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    #map { height: 300px; width: 100%; border-radius: 0.75rem; z-index: 10; margin-bottom: 1.5rem; }
</style>

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-y-auto">
        <div class="max-w-3xl mx-auto">
            <h1 class="font-serif text-4xl mb-8">My Profile</h1>

            <?php if ($message): ?>
                <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center gap-2"><i
                        data-lucide="check-circle" class="w-5 h-5"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 flex items-center gap-2"><i
                        data-lucide="alert-circle" class="w-5 h-5"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm p-8">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Username</label>
                            <input type="text" name="username"
                                value="<?php echo htmlspecialchars($user['username']); ?>" required pattern="[a-zA-Z\s]+" title="Only letters and spaces are allowed"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                        </div>
                        <div>
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                        </div>
                        <div>
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                pattern="\d{1,10}" maxlength="10" title="Only numbers, maximum 10 digits"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                        </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-gray-100">
                        <h3 class="font-serif text-2xl mb-4">My Current Location</h3>
                        <p class="text-sm text-paw-gray mb-6">Updating your location helps the admin assign you to nearby rescues (within 10km).</p>
                        <div id="map"></div>
                        <input type="hidden" name="latitude" id="latitude" value="<?php echo $user['latitude']; ?>">
                        <input type="hidden" name="longitude" id="longitude" value="<?php echo $user['longitude']; ?>">
                    </div>

                    <div class="pt-8 border-t border-gray-100">
                        <h3 class="font-serif text-2xl mb-6">Change Password</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Current
                                    Password</label>
                                <input type="password" name="current_password"
                                    placeholder="Enter only if changing password"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">New
                                        Password</label>
                                    <input type="password" name="new_password" placeholder="New Password"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                                </div>
                                <div>
                                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Confirm
                                        New Password</label>
                                    <input type="password" name="confirm_password" placeholder="Confirm New Password"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pt-6">
                        <button type="submit"
                            class="px-8 py-4 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<?php include '../includes/footer.php'; ?>

<script>
    // Initialize Map
    document.addEventListener('DOMContentLoaded', function() {
        const initialLat = <?php echo ($user['latitude'] && $user['latitude'] != 'NULL') ? $user['latitude'] : '28.6139'; ?>;
        const initialLng = <?php echo ($user['longitude'] && $user['longitude'] != 'NULL') ? $user['longitude'] : '77.2090'; ?>;
        
        const map = L.map('map').setView([initialLat, initialLng], <?php echo ($user['latitude'] && $user['latitude'] != 'NULL') ? '15' : '13'; ?>);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let marker;
        if (<?php echo ($user['latitude'] && $user['latitude'] != 'NULL') ? 'true' : 'false'; ?>) {
            marker = L.marker([initialLat, initialLng]).addTo(map);
        }

        function onMapClick(e) {
            const { lat, lng } = e.latlng;
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
        }

        map.on('click', onMapClick);

        // Try to get current location if not set
        if (!<?php echo ($user['latitude'] && $user['latitude'] != 'NULL') ? 'true' : 'false'; ?> && navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                const { latitude, longitude } = position.coords;
                map.setView([latitude, longitude], 15);
            });
        }
    });
</script>