<?php
include './../utils/auth.php';

if (!$auth->isLoggedIn() || !$artistAuth->isArtist()) {
    header("Location: artist_login.php");
    exit();
}

$artist_id = $artistAuth->getArtistId();
$username = $_SESSION['username'];
$artist_name = $artistAuth->getArtistName();

// Get artist stats
$stats_query = "SELECT 
    COUNT(t.id) as total_tracks,
    SUM(t.plays_count) as total_plays,
    SUM(te.amount) as total_earnings,
    a.earnings as current_balance
FROM artists a 
LEFT JOIN tracks t ON a.id = t.artist_id 
LEFT JOIN track_earnings te ON t.id = te.track_id 
WHERE a.id = ? 
GROUP BY a.id";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $artist_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get recent tracks
$tracks_query = "SELECT * FROM tracks WHERE artist_id = ? ORDER BY upload_date DESC LIMIT 5";
$tracks_stmt = $conn->prepare($tracks_query);
$tracks_stmt->bind_param("i", $artist_id);
$tracks_stmt->execute();
$recent_tracks = $tracks_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Dashboard - moMusic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="flex items-center text-xl font-bold text-white">
                        <i class="fas fa-music text-purple-400 mr-2"></i>
                        moMusic
                    </a>
                    <div class="hidden md:ml-6 md:flex md:space-x-4">
                        <a href="artist_dashboard.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        <a href="upload-track.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Upload Track</a>
                        <a href="artist-earnings.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Earnings</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-300 text-sm">Welcome, <?php echo htmlspecialchars($artist_name ?: $username); ?></span>
                    <a href="../middleware/logout.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Artist Dashboard</h1>
            <p class="text-gray-600 mt-2">Manage your music and track your performance</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Tracks -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-blue-100 text-blue-600 mr-4">
                        <i class="fas fa-music text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Tracks</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_tracks'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Plays -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-green-100 text-green-600 mr-4">
                        <i class="fas fa-headphones text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Plays</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_plays'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Earnings -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-yellow-100 text-yellow-600 mr-4">
                        <i class="fas fa-dollar-sign text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Earnings</p>
                        <p class="text-2xl font-bold text-gray-900">$<?php echo number_format($stats['total_earnings'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>

            <!-- Available Balance -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-indigo-100 text-indigo-600 mr-4">
                        <i class="fas fa-wallet text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Available Balance</p>
                        <p class="text-2xl font-bold text-gray-900">$<?php echo number_format($stats['current_balance'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Quick Actions -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="upload-track.php" class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg hover:bg-purple-700 transition duration-200 font-semibold flex items-center justify-center">
                            <i class="fas fa-upload mr-2"></i>
                            Upload New Track
                        </a>
                        <a href="artist-earnings.php" class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition duration-200 font-semibold flex items-center justify-center">
                            <i class="fas fa-chart-line mr-2"></i>
                            View Earnings
                        </a>
                        <a href="artist-tracks.php" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200 font-semibold flex items-center justify-center">
                            <i class="fas fa-list mr-2"></i>
                            Manage Tracks
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Tracks -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Recent Tracks</h2>
                    </div>
                    <div class="p-6">
                        <?php if ($recent_tracks->num_rows > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plays</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <?php while ($track = $recent_tracks->fetch_assoc()): ?>
                                        <tr class="hover:bg-gray-50 transition duration-150">
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($track['title']); ?></div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo number_format($track['plays_count']); ?></div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">$<?php echo $track['price']; ?></div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500"><?php echo date('M j, Y', strtotime($track['upload_date'])); ?></div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-music text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">No tracks uploaded yet</h3>
                                <p class="text-gray-600 mb-4">Start sharing your music with the world</p>
                                <a href="upload-track.php" class="inline-flex items-center bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition duration-200 font-semibold">
                                    <i class="fas fa-upload mr-2"></i>
                                    Upload Your First Track
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info Section -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Performance Tips -->
            <div class="bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl p-6 text-white">
                <h3 class="text-lg font-semibold mb-3">💡 Performance Tips</h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mr-2 mt-0.5 flex-shrink-0"></i>
                        <span>Upload high-quality audio files for better listener experience</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mr-2 mt-0.5 flex-shrink-0"></i>
                        <span>Use engaging track titles and descriptions</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mr-2 mt-0.5 flex-shrink-0"></i>
                        <span>Share your tracks on social media to increase plays</span>
                    </li>
                </ul>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Quick Stats</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600"><?php echo $stats['total_tracks'] ?? 0; ?></div>
                        <div class="text-gray-600">Tracks</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600"><?php echo number_format($stats['total_plays'] ?? 0); ?></div>
                        <div class="text-gray-600">Total Plays</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">$<?php echo number_format($stats['total_earnings'] ?? 0, 2); ?></div>
                        <div class="text-gray-600">Earned</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">$<?php echo number_format($stats['current_balance'] ?? 0, 2); ?></div>
                        <div class="text-gray-600">Balance</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>