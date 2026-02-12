<?php
session_start();
include 'config.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['reporter_name'];
    $phone = $_POST['contact_phone'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $urgency = $_POST['urgency'] ?? 'Medium';
    $image = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = 'uploads/rescues/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $imageName;
        }
    }

    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
    $reporterId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Use Prepared Statement
    $stmt = $conn->prepare("INSERT INTO rescue_reports (reporter_id, reporter_name, contact_phone, location, latitude, longitude, description, urgency, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Bind parameters
    $stmt->bind_param("isssddsss", $reporterId, $name, $phone, $location, $latitude, $longitude, $description, $urgency, $image);

    if ($stmt->execute()) {
        $message = "success";
    }
    $stmt->close();
}

$basePath = '';
include 'includes/header.php';
?>

<!-- Leaflet Configuration - Added here as it is page specific -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    /* Dark Mode Input Autofill fix */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 30px white inset !important;
    }

    .form-input {
        width: 100%;
        padding: 1rem;
        border: 1px solid #e5e5e5;
        border-radius: 0.75rem;
        background: white;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-input:focus {
        outline: none;
        border-color: #E07A5F;
        box-shadow: 0 0 0 3px rgba(224, 122, 95, 0.1);
    }

    #map {
        height: 300px;
        width: 100%;
        border-radius: 0.75rem;
        z-index: 10;
    }
</style>

<!-- Hero -->
<section class="pt-32 pb-12 px-6 relative overflow-hidden">
    <div class="absolute top-20 right-0 w-96 h-96 bg-paw-alert/10 rounded-full blur-3xl"></div>
    <div class="max-w-3xl mx-auto text-center relative z-10">
        <div class="w-16 h-16 bg-paw-alert/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <i data-lucide="siren" class="w-8 h-8 text-paw-alert"></i>
        </div>
        <p class="text-sm uppercase tracking-[0.3em] text-paw-alert mb-4">Emergency Response</p>
        <h1 class="font-serif text-5xl md:text-6xl text-paw-dark mb-6">
            Report a <span class="italic text-paw-alert">Rescue</span>
        </h1>
        <p class="text-paw-gray text-lg max-w-xl mx-auto">
            Found an injured or stranded animal? Fill out this form and our volunteers will respond immediately.
        </p>
    </div>
</section>

<!-- Form -->
<section class="py-12 px-6">
    <div class="max-w-2xl mx-auto">
        <?php if ($message === 'success'): ?>
            <div class="bg-green-50 border border-green-200 rounded-2xl p-8 text-center mb-10">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>
                </div>
                <h3 class="font-serif text-2xl text-green-800 mb-2">Report Submitted!</h3>
                <p class="text-green-700">Our volunteers have been notified and will respond shortly. Thank you for
                    helping!</p>
                <a href="index.php"
                    class="inline-block mt-6 text-sm uppercase tracking-widest font-semibold text-green-700 border-b border-green-700 pb-1">Return
                    Home</a>
            </div>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data"
                class="bg-white rounded-3xl shadow-xl p-8 md:p-12 transition-colors duration-300">
                <div class="grid gap-6">
                    <!-- Urgency -->
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Urgency Level</label>
                        <div class="flex gap-4 flex-wrap">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="urgency" value="Low" class="w-4 h-4 accent-paw-accent">
                                <span class="text-sm">Low</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="urgency" value="Medium" checked class="w-4 h-4 accent-yellow-500">
                                <span class="text-sm">Medium</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="urgency" value="High" class="w-4 h-4 accent-orange-500">
                                <span class="text-sm">High</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="urgency" value="Critical" class="w-4 h-4 accent-red-600">
                                <span class="text-sm text-red-600 font-semibold">Critical</span>
                            </label>
                        </div>
                    </div>

                    <!-- Name & Phone -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Your Name</label>
                            <input type="text" name="reporter_name" required placeholder="John Doe" class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Phone
                                Number</label>
                            <input type="tel" name="contact_phone" required placeholder="+91 98765 43210"
                                class="form-input">
                        </div>
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Location</label>
                        <input type="text" name="location" required placeholder="Near XYZ landmark, Street Name, City"
                            class="form-input">
                    </div>

                    <!-- Map Section -->
                    <!-- Map Section -->
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Pin Exact Location
                            <span class="text-xs text-paw-gray normal-case font-normal">(Click on map)</span></label>
                        <div id="map"></div>
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <p class="text-xs text-paw-gray mt-2 flex items-center gap-1"><i data-lucide="info"
                                class="w-3 h-3"></i> Pinning the exact location helps rescuers find the animal faster.
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Description</label>
                        <textarea name="description" required rows="4"
                            placeholder="Describe the animal's condition and the situation..."
                            class="form-input"></textarea>
                    </div>

                    <!-- Image -->
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Photo
                            (Optional)</label>
                        <input type="file" name="image" accept="image/*"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-paw-accent/10 file:text-paw-accent hover:file:bg-paw-accent/20 transition-all cursor-pointer">
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-paw-alert text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors shadow-lg shadow-paw-alert/30">
                        Submit Report
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<!-- Info -->
<section class="py-20 bg-paw-dark text-white">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-12">
        <div class="text-center">
            <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="map-pin" class="w-6 h-6 text-paw-accent"></i>
            </div>
            <h3 class="font-serif text-xl mb-2">Pin Location</h3>
            <p class="text-white/60 text-sm">Provide accurate location details to help rescuers find the animal
                quickly.</p>
        </div>
        <div class="text-center">
            <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="users" class="w-6 h-6 text-paw-alert"></i>
            </div>
            <h3 class="font-serif text-xl mb-2">Alert Volunteers</h3>
            <p class="text-white/60 text-sm">Your report instantly notifies nearby volunteers and rescue teams.</p>
        </div>
        <div class="text-center">
            <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="heart-handshake" class="w-6 h-6 text-green-400"></i>
            </div>
            <h3 class="font-serif text-xl mb-2">Save a Life</h3>
            <p class="text-white/60 text-sm">Every report helps us rescue and rehabilitate animals in need.</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
    // Initialize Map
    const map = L.map('map').setView([28.6139, 77.2090], 13); // Default to New Delhi or user location

    const lightTiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    });

    // Default to light tiles
    lightTiles.addTo(map);

    let marker;

    // Function to handle map clicks
    function onMapClick(e) {
        const { lat, lng } = e.latlng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }

        // Update hidden inputs
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);
    }

    map.on('click', onMapClick);

    // Try to get user's current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                map.setView([latitude, longitude], 15);
                // Optional: Auto-pin current location? Maybe better to let user decide.
            },
            (error) => {
                console.log('Geolocation not enabled or denied.');
            }
        );
    }
</script>
</body>

</html>