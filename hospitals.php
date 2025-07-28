<?php
require_once 'includes/functions.php';
requireAuth();

$current_user = getCurrentUser();
if (!$current_user) {
    header('Location: login.php');
    exit();
}

// Get hospitals with maternity services
$query = "SELECT * FROM hospitals WHERE has_maternity = 1 ORDER BY rating DESC, name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Search functionality
$search_query = '';
$filtered_hospitals = $hospitals;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = sanitizeInput($_GET['search']);
    $filtered_hospitals = array_filter($hospitals, function($hospital) use ($search_query) {
        return stripos($hospital['name'], $search_query) !== false || 
               stripos($hospital['address'], $search_query) !== false ||
               stripos($hospital['specialties'], $search_query) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Hospitals - MOMCARE 🏥</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/momcare-ui.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body class="bg-gray-50">
    <!-- Enhanced Navigation -->
    <nav class="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="navbar-brand">
                        <i class="fas fa-heart mr-2"></i>MOMCARE
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="dashboard.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                    </a>
                    <a href="chat.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-comments mr-1"></i>Chat
                    </a>
                    <a href="appointments.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-calendar-alt mr-1"></i>Appointments
                    </a>
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($current_user['full_name']); ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 card">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-4 lg:mb-0">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            <i class="fas fa-hospital text-blue-600 mr-3"></i>Find Nearby Hospitals
                        </h1>
                        <p class="text-gray-600">Locate maternity hospitals and medical centers near you</p>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="getCurrentLocation()" class="btn-primary">
                            <i class="fas fa-location-arrow mr-2"></i>Use My Location
                        </button>
                        <button class="emergency-btn relative" onclick="callEmergency()">
                            <i class="fas fa-phone mr-2"></i>Call 911
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="mb-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form method="GET" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input 
                                type="text" 
                                name="search" 
                                value="<?php echo htmlspecialchars($search_query); ?>"
                                placeholder="Search hospitals by name, location, or specialty..."
                                class="form-input pl-10"
                            >
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <a href="hospitals.php" class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg">
                            Clear
                        </a>
                    </div>
                </form>
                
                <!-- Quick Filters -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="text-sm font-medium text-gray-700 mr-3">Quick Filters:</span>
                    <button onclick="filterBySpecialty('NICU')" class="quick-action-btn">
                        <i class="fas fa-baby mr-1"></i>NICU Available
                    </button>
                    <button onclick="filterBySpecialty('High-Risk')" class="quick-action-btn">
                        <i class="fas fa-heartbeat mr-1"></i>High-Risk Pregnancy
                    </button>
                    <button onclick="filterByRating(4.0)" class="quick-action-btn">
                        <i class="fas fa-star mr-1"></i>Highly Rated
                    </button>
                    <button onclick="sortByDistance()" class="quick-action-btn">
                        <i class="fas fa-map-marker-alt mr-1"></i>Nearest First
                    </button>
                </div>
            </div>
        </div>

        <!-- Map and List View Toggle -->
        <div class="mb-6">
            <div class="bg-white rounded-xl shadow-lg p-4">
                <div class="flex justify-between items-center">
                    <div class="text-lg font-semibold text-gray-900">
                        <?php echo count($filtered_hospitals); ?> hospitals found
                    </div>
                    <div class="flex space-x-2">
                        <button id="listViewBtn" onclick="showListView()" class="btn-primary">
                            <i class="fas fa-list mr-2"></i>List View
                        </button>
                        <button id="mapViewBtn" onclick="showMapView()" class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg">
                            <i class="fas fa-map mr-2"></i>Map View
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Hospital List -->
            <div id="hospitalList" class="lg:col-span-2 space-y-4">
                <?php if (count($filtered_hospitals) > 0): ?>
                    <?php foreach ($filtered_hospitals as $hospital): ?>
                    <div class="hospital-card bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300" data-hospital-id="<?php echo $hospital['id']; ?>">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">
                                    <?php echo htmlspecialchars($hospital['name']); ?>
                                    <?php if ($hospital['has_nicu']): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">
                                            <i class="fas fa-baby mr-1"></i>NICU
                                        </span>
                                    <?php endif; ?>
                                </h3>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                    <span class="text-gray-600"><?php echo htmlspecialchars($hospital['address']); ?></span>
                                </div>
                                <div class="flex items-center mb-3">
                                    <div class="flex items-center mr-4">
                                        <?php
                                        $rating = $hospital['rating'];
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star text-yellow-400"></i>';
                                            } else {
                                                echo '<i class="far fa-star text-gray-300"></i>';
                                            }
                                        }
                                        ?>
                                        <span class="ml-2 text-sm text-gray-600">(<?php echo $rating; ?>/5)</span>
                                    </div>
                                    <span class="distance text-sm text-gray-500" data-lat="<?php echo $hospital['latitude']; ?>" data-lng="<?php echo $hospital['longitude']; ?>">
                                        <i class="fas fa-route mr-1"></i>Calculating distance...
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-green-600 mb-1">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="text-xs text-gray-500">Available</div>
                            </div>
                        </div>

                        <!-- Specialties -->
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Specialties:</h4>
                            <div class="flex flex-wrap gap-2">
                                <?php
                                $specialties = json_decode($hospital['specialties'], true) ?: [];
                                foreach ($specialties as $specialty):
                                ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?php echo htmlspecialchars($specialty); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Contact Actions -->
                        <div class="flex flex-wrap gap-3">
                            <a href="tel:<?php echo $hospital['phone']; ?>" class="btn-primary">
                                <i class="fas fa-phone mr-2"></i>Call Hospital
                            </a>
                            <?php if ($hospital['emergency_phone']): ?>
                            <a href="tel:<?php echo $hospital['emergency_phone']; ?>" class="btn-secondary">
                                <i class="fas fa-ambulance mr-2"></i>Emergency Line
                            </a>
                            <?php endif; ?>
                            <button onclick="getDirections(<?php echo $hospital['latitude']; ?>, <?php echo $hospital['longitude']; ?>, '<?php echo addslashes($hospital['name']); ?>')" class="px-4 py-2 text-indigo-600 hover:text-indigo-800 border border-indigo-300 rounded-lg">
                                <i class="fas fa-directions mr-2"></i>Directions
                            </button>
                            <button onclick="scheduleAppointment('<?php echo $hospital['id']; ?>')" class="px-4 py-2 text-green-600 hover:text-green-800 border border-green-300 rounded-lg">
                                <i class="fas fa-calendar-plus mr-2"></i>Schedule Visit
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-hospital text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No hospitals found</h3>
                        <p class="text-gray-500 mb-4">Try adjusting your search criteria or location</p>
                        <button onclick="getCurrentLocation()" class="btn-primary">
                            <i class="fas fa-location-arrow mr-2"></i>Use My Location
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Map Container (Hidden by default) -->
            <div id="mapContainer" class="lg:col-span-2 hidden">
                <div class="bg-white rounded-xl shadow-lg p-4">
                    <div id="hospitalMap" style="height: 600px;" class="rounded-lg"></div>
                </div>
            </div>

            <!-- Sidebar with Quick Info -->
            <div class="space-y-6">
                <!-- Emergency Contacts -->
                <div class="bg-red-50 rounded-xl shadow-lg p-6 border-l-4 border-red-500">
                    <h3 class="text-lg font-semibold text-red-900 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Emergency Contacts
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-red-800 font-medium">Emergency Services</span>
                            <a href="tel:911" class="text-red-600 font-bold hover:text-red-800 text-xl">911</a>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-red-800 font-medium">Poison Control</span>
                            <a href="tel:1-800-222-1222" class="text-red-600 font-semibold hover:text-red-800">1-800-222-1222</a>
                        </div>
                        <div class="pt-3 border-t border-red-200">
                            <p class="text-red-700 text-sm">
                                <i class="fas fa-info-circle mr-1"></i>
                                For pregnancy emergencies: severe bleeding, severe pain, difficulty breathing, or signs of preeclampsia
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Hospital Tips -->
                <div class="bg-blue-50 rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">
                        <i class="fas fa-lightbulb text-blue-600 mr-2"></i>Hospital Selection Tips
                    </h3>
                    <ul class="space-y-2 text-blue-800 text-sm">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-1"></i>
                            <span>Look for hospitals with Level III NICU if you have a high-risk pregnancy</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-1"></i>
                            <span>Consider the distance from your home for regular visits</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-1"></i>
                            <span>Check if your insurance covers the hospital</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-1"></i>
                            <span>Tour the maternity ward before your due date</span>
                        </li>
                    </ul>
                </div>

                <!-- Pregnancy Emergency Signs -->
                <div class="bg-amber-50 rounded-xl shadow-lg p-6 border-l-4 border-amber-500">
                    <h3 class="text-lg font-semibold text-amber-900 mb-4">
                        <i class="fas fa-exclamation-triangle text-amber-600 mr-2"></i>When to Seek Emergency Care
                    </h3>
                    <ul class="space-y-2 text-amber-800 text-sm">
                        <li>• Severe abdominal or pelvic pain</li>
                        <li>• Heavy bleeding or passing clots</li>
                        <li>• Severe headache with vision changes</li>
                        <li>• Difficulty breathing or chest pain</li>
                        <li>• Signs of preterm labor before 37 weeks</li>
                        <li>• Decreased fetal movement</li>
                        <li>• Severe nausea and vomiting</li>
                    </ul>
                    <div class="mt-4 pt-3 border-t border-amber-200">
                        <p class="text-amber-700 text-xs">
                            Trust your instincts. If something doesn't feel right, seek medical attention immediately.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="assets/js/momcare-app.js"></script>
    <script>
        let map;
        let userLocation = null;
        let hospitalMarkers = [];

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            getCurrentLocation();
            calculateAllDistances();
        });

        // Get user's current location
        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        calculateAllDistances();
                        MomCare.showNotification('Location detected! Distances updated.', 'success');
                    },
                    function(error) {
                        console.error('Geolocation error:', error);
                        MomCare.showNotification('Could not get your location. Please enter your address manually.', 'warning');
                    }
                );
            } else {
                MomCare.showNotification('Geolocation is not supported by your browser.', 'error');
            }
        }

        // Calculate distances to all hospitals
        function calculateAllDistances() {
            if (!userLocation) return;

            const distanceElements = document.querySelectorAll('.distance');
            distanceElements.forEach(element => {
                const lat = parseFloat(element.dataset.lat);
                const lng = parseFloat(element.dataset.lng);
                
                if (lat && lng) {
                    const distance = getDistance(userLocation.lat, userLocation.lng, lat, lng);
                    element.innerHTML = `<i class="fas fa-route mr-1"></i>${distance.toFixed(1)} miles away`;
                }
            });
        }

        // Calculate distance between two points
        function getDistance(lat1, lon1, lat2, lon2) {
            const R = 3959; // Earth's radius in miles
            const dLat = deg2rad(lat2 - lat1);
            const dLon = deg2rad(lon2 - lon1);
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        function deg2rad(deg) {
            return deg * (Math.PI/180);
        }

        // Show map view
        function showMapView() {
            document.getElementById('hospitalList').classList.add('hidden');
            document.getElementById('mapContainer').classList.remove('hidden');
            document.getElementById('listViewBtn').classList.remove('btn-primary');
            document.getElementById('listViewBtn').classList.add('px-4', 'py-2', 'text-gray-600', 'hover:text-gray-800', 'border', 'border-gray-300', 'rounded-lg');
            document.getElementById('mapViewBtn').classList.add('btn-primary');
            document.getElementById('mapViewBtn').classList.remove('px-4', 'py-2', 'text-gray-600', 'hover:text-gray-800', 'border', 'border-gray-300', 'rounded-lg');
            
            initializeMap();
        }

        // Show list view
        function showListView() {
            document.getElementById('hospitalList').classList.remove('hidden');
            document.getElementById('mapContainer').classList.add('hidden');
            document.getElementById('listViewBtn').classList.add('btn-primary');
            document.getElementById('listViewBtn').classList.remove('px-4', 'py-2', 'text-gray-600', 'hover:text-gray-800', 'border', 'border-gray-300', 'rounded-lg');
            document.getElementById('mapViewBtn').classList.remove('btn-primary');
            document.getElementById('mapViewBtn').classList.add('px-4', 'py-2', 'text-gray-600', 'hover:text-gray-800', 'border', 'border-gray-300', 'rounded-lg');
        }

        // Initialize map
        function initializeMap() {
            if (map) return; // Map already initialized

            const defaultCenter = userLocation || [40.7128, -74.0060]; // Default to NYC
            map = L.map('hospitalMap').setView([defaultCenter.lat || defaultCenter[0], defaultCenter.lng || defaultCenter[1]], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add user location marker
            if (userLocation) {
                L.marker([userLocation.lat, userLocation.lng])
                    .addTo(map)
                    .bindPopup('Your Location')
                    .openPopup();
            }

            // Add hospital markers
            <?php foreach ($filtered_hospitals as $hospital): ?>
            <?php if ($hospital['latitude'] && $hospital['longitude']): ?>
            const hospitalMarker<?php echo $hospital['id']; ?> = L.marker([<?php echo $hospital['latitude']; ?>, <?php echo $hospital['longitude']; ?>])
                .addTo(map)
                .bindPopup(`
                    <div class="p-2">
                        <h3 class="font-bold text-lg"><?php echo addslashes($hospital['name']); ?></h3>
                        <p class="text-sm text-gray-600 mb-2"><?php echo addslashes($hospital['address']); ?></p>
                        <div class="flex space-x-2">
                            <a href="tel:<?php echo $hospital['phone']; ?>" class="btn-primary btn-sm">Call</a>
                            <button onclick="getDirections(<?php echo $hospital['latitude']; ?>, <?php echo $hospital['longitude']; ?>, '<?php echo addslashes($hospital['name']); ?>')" class="btn-secondary btn-sm">Directions</button>
                        </div>
                    </div>
                `);
            <?php endif; ?>
            <?php endforeach; ?>
        }

        // Get directions to hospital
        function getDirections(lat, lng, name) {
            const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&destination_place_id=${encodeURIComponent(name)}`;
            window.open(url, '_blank');
        }

        // Call emergency services
        function callEmergency() {
            if (confirm('This will call 911 for emergency services. Do you want to continue?')) {
                window.open('tel:911', '_self');
            }
        }

        // Filter by specialty
        function filterBySpecialty(specialty) {
            const cards = document.querySelectorAll('.hospital-card');
            cards.forEach(card => {
                const specialtyText = card.textContent;
                if (specialtyText.includes(specialty)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Filter by rating
        function filterByRating(minRating) {
            const cards = document.querySelectorAll('.hospital-card');
            cards.forEach(card => {
                const ratingText = card.querySelector('.fa-star').parentElement.textContent;
                const rating = parseFloat(ratingText.match(/\(([^)]+)\)/)[1]);
                if (rating >= minRating) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Sort by distance
        function sortByDistance() {
            if (!userLocation) {
                MomCare.showNotification('Please allow location access to sort by distance', 'warning');
                return;
            }

            const hospitalList = document.getElementById('hospitalList');
            const cards = Array.from(hospitalList.querySelectorAll('.hospital-card'));
            
            cards.sort((a, b) => {
                const aLat = parseFloat(a.querySelector('.distance').dataset.lat);
                const aLng = parseFloat(a.querySelector('.distance').dataset.lng);
                const bLat = parseFloat(b.querySelector('.distance').dataset.lat);
                const bLng = parseFloat(b.querySelector('.distance').dataset.lng);
                
                const aDist = getDistance(userLocation.lat, userLocation.lng, aLat, aLng);
                const bDist = getDistance(userLocation.lat, userLocation.lng, bLat, bLng);
                
                return aDist - bDist;
            });
            
            cards.forEach(card => hospitalList.appendChild(card));
            MomCare.showNotification('Hospitals sorted by distance', 'success');
        }

        // Schedule appointment at hospital
        function scheduleAppointment(hospitalId) {
            // Redirect to appointments page with hospital pre-selected
            window.location.href = `appointments.php?hospital_id=${hospitalId}`;
        }
    </script>
</body>
</html>
