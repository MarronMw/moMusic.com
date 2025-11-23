<?php
include 'config.php';
include 'auth.php';

if (!$auth->isLoggedIn() || !$artistAuth->isArtist()) {
    header("Location: login.php");
    exit();
}

$artist_id = $artistAuth->getArtistId();

// Check if artist has active subscription and track limits
$subscription_query = "SELECT ap.track_limit, COUNT(t.id) as current_tracks 
                      FROM artist_subscriptions asp 
                      JOIN artist_plans ap ON asp.plan_id = ap.id 
                      LEFT JOIN tracks t ON asp.artist_id = t.artist_id 
                      WHERE asp.artist_id = ? AND asp.status = 'active' 
                      AND asp.end_date >= CURDATE() 
                      GROUP BY asp.id";
$stmt = $conn->prepare($subscription_query);
$stmt->bind_param("i", $artist_id);
$stmt->execute();
$subscription = $stmt->get_result()->fetch_assoc();

if (!$subscription) {
    die("No active subscription found. Please subscribe to an artist plan.");
}

if ($subscription['track_limit'] && $subscription['current_tracks'] >= $subscription['track_limit']) {
    die("You have reached your track limit. Please upgrade your plan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $album = $_POST['album'];
    $genre = $_POST['genre'];
    $price = $_POST['price'];
    $is_premium = isset($_POST['is_premium']) ? 1 : 0;
    
    // Handle file upload
    $upload_dir = 'uploads/music/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $audio_file = $_FILES['audio_file'];
    $file_extension = strtolower(pathinfo($audio_file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['mp3', 'wav', 'ogg'];
    
    if (in_array($file_extension, $allowed_extensions)) {
        $filename = uniqid() . '_' . $audio_file['name'];
        $file_path = $upload_dir . $filename;
        
        if (move_uploaded_file($audio_file['tmp_name'], $file_path)) {
            // Get audio duration (simplified - you might want to use a library for this)
            $duration = 180; // Default 3 minutes
            
            $stmt = $conn->prepare("INSERT INTO tracks (title, artist, album, genre, duration, file_path, price, is_premium, artist_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $artist_name = $_SESSION['username']; // In real app, get from artists table
            $stmt->bind_param("ssssisdii", $title, $artist_name, $album, $genre, $duration, $file_path, $price, $is_premium, $artist_id);
            
            if ($stmt->execute()) {
                $success = "Track uploaded successfully!";
            } else {
                $error = "Error uploading track: " . $conn->error;
            }
        } else {
            $error = "Error uploading file.";
        }
    } else {
        $error = "Invalid file format. Allowed: MP3, WAV, OGG";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Track - MusicStream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">MusicStream</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="artist-dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Upload New Track</h4>
                        <p class="text-muted mb-0">
                            Tracks remaining: 
                            <?php echo $subscription['track_limit'] ? $subscription['track_limit'] - $subscription['current_tracks'] : 'Unlimited'; ?>
                        </p>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Track Title *</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Album</label>
                                <input type="text" name="album" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Genre</label>
                                <select name="genre" class="form-select">
                                    <option value="Rock">Rock</option>
                                    <option value="Pop">Pop</option>
                                    <option value="Hip Hop">Hip Hop</option>
                                    <option value="Electronic">Electronic</option>
                                    <option value="Jazz">Jazz</option>
                                    <option value="Classical">Classical</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Price ($)</label>
                                <input type="number" name="price" class="form-control" step="0.01" min="0" value="0.99" required>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_premium" id="is_premium">
                                    <label class="form-check-label" for="is_premium">
                                        Premium Track (Only available to premium subscribers)
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Audio File *</label>
                                <input type="file" name="audio_file" class="form-control" accept=".mp3,.wav,.ogg" required>
                                <div class="form-text">Supported formats: MP3, WAV, OGG. Max file size: 10MB</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Upload Track</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>