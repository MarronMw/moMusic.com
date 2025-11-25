<?php
require_once './../utils/auth.php';

if (!$auth->isLoggedIn()) {
    header("Location: users/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$is_premium = $auth->isPremium();

// Get all tracks with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get genre filter
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query - SIMPLIFIED VERSION
$query = "SELECT t.*, a.artist_name 
          FROM tracks t 
          LEFT JOIN artists a ON t.artist_id = a.id 
          WHERE (t.is_premium = 0 OR ? = 1)";

$count_query = "SELECT COUNT(*) FROM tracks WHERE (is_premium = 0 OR ? = 1)";
$params = [$is_premium];
$param_types = "i";

// Add genre filter if specified
if (!empty($genre)) {
    $query .= " AND t.genre = ?";
    $count_query .= " AND genre = ?";
    $params[] = $genre;
    $param_types .= "s";
}

// Add search filter if specified
if (!empty($search)) {
    $query .= " AND (t.title LIKE ? OR t.artist LIKE ? OR a.artist_name LIKE ?)";
    $count_query .= " AND (title LIKE ? OR artist LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= "sss";
}

// Add pagination to main query
$query .= " ORDER BY t.upload_date DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$param_types .= "ii";

// Execute main query
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $tracks = $stmt->get_result();
} else {
    die("Error preparing statement: " . $conn->error);
}

// Get total count - SIMPLIFIED
$count_params = [$is_premium];
$count_types = "i";

if (!empty($genre)) {
    $count_params[] = $genre;
    $count_types .= "s";
}

if (!empty($search)) {
    $search_term = "%$search%";
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_types .= "ss";
}

$count_stmt = $conn->prepare($count_query);
if ($count_stmt) {
    $count_stmt->bind_param($count_types, ...$count_params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_tracks = $count_result->fetch_row()[0];
    $total_pages = ceil($total_tracks / $limit);
} else {
    $total_tracks = 0;
    $total_pages = 1;
}

// Get genres for filter
$genres_query = "SELECT DISTINCT genre FROM tracks WHERE genre IS NOT NULL ORDER BY genre";
$genres_result = $conn->query($genres_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Music - MusicStream</title>
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
                        <a href="dashboard.php" class="text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">Dashboard</a>
                        <a href="browse.php" class="border-b-2 border-purple-600 text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">Browse</a>
                        <a href="library.php" class="text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">My Library</a>
                        <a href="playlist.php" class="text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium">Playlists</a>
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
                                <a href="../middleware/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Browse Music</h1>
            <p class="text-gray-600 mt-2">Discover new music from talented artists</p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200 mb-8">
            <form method="GET" class="space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4">
                <!-- Search -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Search songs, artists..." 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                        >
                    </div>
                </div>

                <!-- Genre Filter -->
                <div class="md:w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Genre</label>
                    <select name="genre" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                        <option value="">All Genres</option>
                        <?php while ($genre_row = $genres_result->fetch_assoc()): ?>
                            <option value="<?php echo $genre_row['genre']; ?>" <?php echo $genre === $genre_row['genre'] ? 'selected' : ''; ?>>
                                <?php echo $genre_row['genre']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex space-x-3">
                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition duration-200 font-semibold">
                        <i class="fas fa-filter mr-2"></i>Apply
                    </button>
                    <a href="browse.php" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition duration-200 font-semibold">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Info -->
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600">
                Showing <?php echo $tracks->num_rows; ?> of <?php echo $total_tracks; ?> tracks
            </p>
            <?php if (!empty($search) || !empty($genre)): ?>
                <p class="text-sm text-gray-500">
                    <?php 
                    $filters = [];
                    if (!empty($search)) $filters[] = "search: \"$search\"";
                    if (!empty($genre)) $filters[] = "genre: $genre";
                    echo "Filters: " . implode(", ", $filters);
                    ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Music Grid -->
        <?php if ($tracks->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                <?php while ($track = $tracks->fetch_assoc()): ?>
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition duration-300 group">
                    <div class="p-6">
                        <!-- Track Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="font-semibold text-lg text-gray-900 mb-1 group-hover:text-purple-600 transition duration-200">
                                    <?php echo htmlspecialchars($track['title']); ?>
                                </h3>
                                <p class="text-gray-600 mb-2">
                                    <?php echo htmlspecialchars($track['artist_name'] ?? $track['artist']); ?>
                                </p>
                            </div>
                            <?php if ($track['is_premium'] && !$is_premium): ?>
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full flex items-center">
                                    <i class="fas fa-crown mr-1"></i> Premium
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Track Info -->
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <span><?php echo $track['genre'] ?? 'Unknown Genre'; ?></span>
                            <span><?php echo gmdate("i:s", $track['duration']); ?></span>
                        </div>

                        <!-- Stats -->
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <span><?php echo number_format($track['plays_count']); ?> plays</span>
                            <span><?php echo date('M j, Y', strtotime($track['upload_date'])); ?></span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-purple-600">$<?php echo $track['price']; ?></span>
                            <div class="flex space-x-2">
                                <button class="p-2 text-gray-400 hover:text-red-500 transition duration-200" title="Add to favorites">
                                    <i class="far fa-heart"></i>
                                </button>
                                <a href="track.php?id=<?php echo $track['id']; ?>" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-200 font-semibold">
                                    <i class="fas fa-shopping-cart mr-2"></i>Buy
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-center items-center space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&genre=<?php echo $genre; ?>&search=<?php echo urlencode($search); ?>" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-chevron-left mr-2"></i>Previous
                    </a>
                <?php endif; ?>

                <span class="px-4 py-2 text-gray-600">
                    Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                </span>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&genre=<?php echo $genre; ?>&search=<?php echo urlencode($search); ?>" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                        Next<i class="fas fa-chevron-right ml-2"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- No Results -->
            <div class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-music text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No tracks found</h3>
                <p class="text-gray-600 mb-6">Try adjusting your search criteria or browse all tracks.</p>
                <a href="browse.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition duration-200 font-semibold">
                    Browse All Music
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>