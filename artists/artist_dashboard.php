<?php
include './../utils/auth.php';

if (!$auth->isLoggedIn() || !$artistAuth->isArtist()) {
    header("Location: login.php");
    exit();
}

$artist_id = $artistAuth->getArtistId();

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
    <title>Artist Dashboard - MusicStream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">MusicStream</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="upload-track.php">Upload Track</a>
                <a class="nav-link" href="artist-earnings.php">Earnings</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h1>Artist Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h4><?php echo $stats['total_tracks'] ?? 0; ?></h4>
                        <p>Total Tracks</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h4><?php echo $stats['total_plays'] ?? 0; ?></h4>
                        <p>Total Plays</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h4>$<?php echo $stats['total_earnings'] ?? '0.00'; ?></h4>
                        <p>Total Earnings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h4>$<?php echo $stats['current_balance'] ?? '0.00'; ?></h4>
                        <p>Available Balance</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="upload-track.php" class="btn btn-primary me-2">Upload New Track</a>
                        <a href="artist-earnings.php" class="btn btn-success me-2">View Earnings</a>
                        <a href="artist-tracks.php" class="btn btn-info">Manage Tracks</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tracks -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Tracks</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_tracks->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Plays</th>
                                            <th>Price</th>
                                            <th>Upload Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($track = $recent_tracks->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($track['title']); ?></td>
                                            <td><?php echo $track['plays_count']; ?></td>
                                            <td>$<?php echo $track['price']; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($track['upload_date'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No tracks uploaded yet. <a href="upload-track.php">Upload your first track!</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>