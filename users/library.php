<?php
require_once './../utils/auth.php';

if (!$auth->isLoggedIn()) {
    header("Location: users/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's purchased tracks
$query = "SELECT p.*, t.title, t.artist, t.duration, t.file_path 
          FROM purchases p 
          JOIN tracks t ON p.track_id = t.id 
          WHERE p.user_id = ? 
          ORDER BY p.purchase_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$purchased_tracks = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library - MusicStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Same navigation as browse.php -->

    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-2">My Library</h1>
        <p class="text-gray-600 mb-8">Your purchased music collection</p>

        <div class="bg-white rounded-lg shadow">
            <?php if ($purchased_tracks->num_rows > 0): ?>
                <div class="divide-y">
                    <?php while ($track = $purchased_tracks->fetch_assoc()): ?>
                    <div class="p-6 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-music text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold"><?php echo htmlspecialchars($track['title']); ?></h3>
                                <p class="text-gray-600"><?php echo htmlspecialchars($track['artist']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-500"><?php echo gmdate("i:s", $track['duration']); ?></span>
                            <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                                <i class="fas fa-play mr-2"></i>Play
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="p-12 text-center">
                    <i class="fas fa-music text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">No music yet</h3>
                    <p class="text-gray-600 mb-4">You haven't purchased any tracks yet.</p>
                    <a href="browse.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700">
                        Browse Music
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>