<?php
include './../utils/auth.php';

if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$is_premium = $auth->isPremium();

// Get user stats
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM purchases WHERE user_id = ?) as purchased_tracks,
    (SELECT COUNT(*) FROM tracks WHERE is_premium = 0 OR ? = 1) as available_tracks,
    (SELECT COALESCE(SUM(amount), 0) FROM purchases WHERE user_id = ?) as total_spent,
    (SELECT COUNT(*) FROM purchases WHERE user_id = ? AND DATE(purchase_date) = CURDATE()) as today_purchases";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("iiii", $user_id, $is_premium, $user_id, $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get recent purchases
$purchases_query = "SELECT p.*, t.title, t.artist, t.duration, t.file_path 
                   FROM purchases p 
                   JOIN tracks t ON p.track_id = t.id 
                   WHERE p.user_id = ? 
                   ORDER BY p.purchase_date DESC 
                   LIMIT 5";
$purchases_stmt = $conn->prepare($purchases_query);
$purchases_stmt->bind_param("i", $user_id);
$purchases_stmt->execute();
$recent_purchases = $purchases_stmt->get_result();

// Get recommended tracks (most popular tracks user hasn't purchased)
$recommended_query = "SELECT t.*, a.artist_name 
                     FROM tracks t 
                     LEFT JOIN artists a ON t.artist_id = a.id 
                     WHERE t.id NOT IN (
                         SELECT track_id FROM purchases WHERE user_id = ?
                     ) 
                     AND (t.is_premium = 0 OR ? = 1)
                     ORDER BY t.plays_count DESC 
                     LIMIT 6";
$rec_stmt = $conn->prepare($recommended_query);
$rec_stmt->bind_param("ii", $user_id, $is_premium);
$rec_stmt->execute();
$recommended_tracks = $rec_stmt->get_result();

// Get recently purchased tracks (for recently played section)
$recent_plays_query = "SELECT t.*, a.artist_name 
                      FROM tracks t 
                      LEFT JOIN artists a ON t.artist_id = a.id 
                      WHERE t.id IN (
                          SELECT track_id FROM purchases WHERE user_id = ?
                      ) 
                      ORDER BY (SELECT MAX(purchase_date) FROM purchases WHERE track_id = t.id AND user_id = ?) DESC 
                      LIMIT 4";
$plays_stmt = $conn->prepare($recent_plays_query);
$plays_stmt->bind_param("ii", $user_id, $user_id);
$plays_stmt->execute();
$recently_played = $plays_stmt->get_result();

// If no purchases yet, get some popular tracks as recommendations
if ($recently_played->num_rows == 0) {
    $popular_query = "SELECT t.*, a.artist_name 
                     FROM tracks t 
                     LEFT JOIN artists a ON t.artist_id = a.id 
                     WHERE (t.is_premium = 0 OR ? = 1)
                     ORDER BY t.plays_count DESC 
                     LIMIT 4";
    $popular_stmt = $conn->prepare($popular_query);
    $popular_stmt->bind_param("i", $is_premium);
    $popular_stmt->execute();
    $recently_played = $popular_stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MusicStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="flex items-center text-xl font-bold text-gray-900">
                        <i class="fas fa-music text-purple-600 mr-2"></i>
                        MusicStream
                    </a>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="dashboard.php" class="border-b-2 border-purple-600 text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">Dashboard</a>
                        <a href="browse.php" class="text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">Browse</a>
                        <a href="library.php" class="text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">My Library</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <?php if ($is_premium): ?>
                            <span class="bg-gradient-to-r from-yellow-400 to-yellow-600 text-black px-3 py-1 rounded-full text-sm font-bold">
                                <i class="fas fa-crown mr-1"></i> PREMIUM
                            </span>
                        <?php else: ?>
                            <a href="subscribe.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-purple-700 transition duration-200">
                                Go Premium
                            </a>
                        <?php endif; ?>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                                <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                                </div>
                                <span class="hidden md:block"><?php echo $username; ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user mr-2"></i>Profile</a>
                                <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cog mr-2"></i>Settings</a>
                                <div class="border-t border-gray-200"></div>
                                <a href="./../middleware/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Welcome back, <?php echo $username; ?>!
                <?php if ($is_premium): ?>
                    <span class="text-yellow-500">👑</span>
                <?php endif; ?>
            </h1>
            <p class="text-gray-600 mt-2">
                <?php
                $hour = date('H');
                if ($hour < 12) {
                    echo "Good morning! Ready to discover some great music?";
                } elseif ($hour < 17) {
                    echo "Good afternoon! What would you like to listen to?";
                } else {
                    echo "Good evening! Perfect time for some music!";
                }
                ?>
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-blue-100 text-blue-600 mr-4">
                        <i class="fas fa-music text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Purchased Tracks</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['purchased_tracks'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-green-100 text-green-600 mr-4">
                        <i class="fas fa-headphones text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Available Tracks</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['available_tracks'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-purple-100 text-purple-600 mr-4">
                        <i class="fas fa-dollar-sign text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Spent</p>
                        <p class="text-2xl font-bold text-gray-900">$<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-orange-100 text-orange-600 mr-4">
                        <i class="fas fa-shopping-cart text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Today's Purchases</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['today_purchases'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recently Played & Quick Actions -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Recently Played -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Recently Played</h2>
                        <a href="library.php" class="text-sm text-purple-600 hover:text-purple-500 font-medium">View All</a>
                    </div>
                    <div class="space-y-4">
                        <?php if ($recently_played->num_rows > 0): ?>
                            <?php while ($track = $recently_played->fetch_assoc()): ?>
                            <div class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition duration-200">
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-400 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-play text-white text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($track['title']); ?></p>
                                    <p class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($track['artist_name'] ?? $track['artist']); ?></p>
                                </div>
                                <button class="text-gray-400 hover:text-purple-600 transition duration-200" onclick="playTrack(<?php echo $track['id']; ?>)">
                                    <i class="fas fa-play-circle text-lg"></i>
                                </button>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-4">No recently played tracks</p>
                            <a href="browse.php" class="block w-full bg-purple-600 text-white text-center py-2 rounded-lg hover:bg-purple-700 transition duration-200">
                                Browse Music
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="browse.php" class="flex items-center space-x-3 p-3 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition duration-200 group">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition duration-200">
                                <i class="fas fa-search text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Browse Music</p>
                                <p class="text-sm text-gray-500">Discover new tracks</p>
                            </div>
                        </a>

                        <a href="library.php" class="flex items-center space-x-3 p-3 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition duration-200 group">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition duration-200">
                                <i class="fas fa-heart text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">My Library</p>
                                <p class="text-sm text-gray-500">Your purchased tracks</p>
                            </div>
                        </a>

                        <?php if (!$is_premium): ?>
                            <a href="subscribe.php" class="flex items-center space-x-3 p-3 rounded-lg border border-yellow-200 hover:border-yellow-300 hover:bg-yellow-50 transition duration-200 group">
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200 transition duration-200">
                                    <i class="fas fa-crown text-yellow-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Go Premium</p>
                                    <p class="text-sm text-gray-500">Unlock exclusive features</p>
                                </div>
                            </a>
                        <?php else: ?>
                            <a href="premium-content.php" class="flex items-center space-x-3 p-3 rounded-lg border border-yellow-200 hover:border-yellow-300 hover:bg-yellow-50 transition duration-200 group">
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200 transition duration-200">
                                    <i class="fas fa-star text-yellow-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Premium Content</p>
                                    <p class="text-sm text-gray-500">Exclusive tracks</p>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Recommended For You -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Recommended For You</h2>
                        <a href="browse.php" class="text-sm text-purple-600 hover:text-purple-500 font-medium">See More</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php if ($recommended_tracks->num_rows > 0): ?>
                            <?php while ($track = $recommended_tracks->fetch_assoc()): ?>
                            <div class="flex items-center space-x-4 p-4 rounded-lg border border-gray-200 hover:border-purple-300 hover:shadow-md transition duration-200 group">
                                <div class="flex-shrink-0 relative">
                                    <div class="w-16 h-16 bg-gradient-to-r from-purple-400 to-pink-400 rounded-lg flex items-center justify-center group-hover:from-purple-500 group-hover:to-pink-500 transition duration-200">
                                        <i class="fas fa-music text-white"></i>
                                    </div>
                                    <?php if ($track['is_premium'] && !$is_premium): ?>
                                        <div class="absolute -top-1 -right-1 bg-yellow-400 text-black text-xs px-2 py-1 rounded-full">
                                            <i class="fas fa-crown"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate"><?php echo htmlspecialchars($track['title']); ?></p>
                                    <p class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($track['artist_name'] ?? $track['artist']); ?></p>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-sm font-medium text-purple-600">$<?php echo $track['price']; ?></span>
                                        <span class="text-xs text-gray-400"><?php echo gmdate("i:s", $track['duration']); ?></span>
                                    </div>
                                </div>
                                <a href="track.php?id=<?php echo $track['id']; ?>" class="opacity-0 group-hover:opacity-100 bg-purple-600 text-white p-2 rounded-full hover:bg-purple-700 transition duration-200">
                                    <i class="fas fa-shopping-cart text-sm"></i>
                                </a>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-span-2 text-center py-8">
                                <i class="fas fa-music text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 mb-4">No recommendations available</p>
                                <a href="browse.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition duration-200">
                                    Explore Music
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Purchases -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Recent Purchases</h2>
                        <a href="library.php" class="text-sm text-purple-600 hover:text-purple-500 font-medium">View All</a>
                    </div>
                    <div class="space-y-4">
                        <?php if ($recent_purchases->num_rows > 0): ?>
                            <?php while ($purchase = $recent_purchases->fetch_assoc()): ?>
                            <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition duration-200">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-blue-400 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-check text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($purchase['title']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($purchase['artist']); ?></p>
                                        <p class="text-xs text-gray-400">Purchased on <?php echo date('M j, Y', strtotime($purchase['purchase_date'])); ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-green-600">$<?php echo $purchase['amount']; ?></p>
                                    <button class="text-purple-600 hover:text-purple-700 text-sm font-medium mt-1" onclick="playTrack(<?php echo $purchase['track_id']; ?>)">
                                        Play
                                    </button>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 mb-4">No purchases yet</p>
                                <a href="browse.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition duration-200">
                                    Start Shopping
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-500 text-sm">
                <p>&copy; 2024 MusicStream. All rights reserved.</p>
                <p class="mt-2">Enjoying your experience? <a href="#" class="text-purple-600 hover:text-purple-500">Share with friends</a></p>
            </div>
        </div>
    </footer>

    <script>
        function playTrack(trackId) {
            // For now, just show an alert. In production, this would integrate with your audio player
            alert('Playing track ID: ' + trackId + '\n\nIn a real application, this would:\n1. Check if user has purchased the track\n2. Load the audio file\n3. Start playback\n4. Update play count');
            
            // Example of what you might do:
            // fetch('play_track.php', {
            //     method: 'POST',
            //     headers: {'Content-Type': 'application/json'},
            //     body: JSON.stringify({track_id: trackId})
            // })
            // .then(response => response.json())
            // .then(data => {
            //     if (data.success) {
            //         // Load audio player with track
            //         audioPlayer.src = data.track_url;
            //         audioPlayer.play();
            //     } else {
            //         alert('Error: ' + data.message);
            //     }
            // });
        }

        // Simple play button functionality
        document.querySelectorAll('button').forEach(button => {
            if (button.textContent.includes('Play') || button.innerHTML.includes('fa-play')) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const trackId = this.getAttribute('onclick')?.match(/\d+/)?.[0] || 'unknown';
                    playTrack(trackId);
                });
            }
        });
    </script>
</body>
</html>