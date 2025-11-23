<?php
include 'config.php';
include 'auth.php';

if (!$auth->isLoggedIn() || !$artistAuth->isArtist()) {
    header("Location: login.php");
    exit();
}

$artist_id = $artistAuth->getArtistId();

// Get earnings summary
$earnings_query = "SELECT 
    SUM(CASE WHEN type = 'purchase' THEN amount ELSE 0 END) as purchase_earnings,
    SUM(CASE WHEN type = 'stream' THEN amount ELSE 0 END) as stream_earnings,
    SUM(amount) as total_earnings
FROM track_earnings 
WHERE artist_id = ?";

$stmt = $conn->prepare($earnings_query);
$stmt->bind_param("i", $artist_id);
$stmt->execute();
$earnings_summary = $stmt->get_result()->fetch_assoc();

// Get recent earnings
$recent_earnings_query = "SELECT te.*, t.title 
                         FROM track_earnings te 
                         JOIN tracks t ON te.track_id = t.id 
                         WHERE te.artist_id = ? 
                         ORDER BY te.created_at DESC 
                         LIMIT 10";
$recent_stmt = $conn->prepare($recent_earnings_query);
$recent_stmt->bind_param("i", $artist_id);
$recent_stmt->execute();
$recent_earnings = $recent_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earnings - MusicStream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">MusicStream</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="artist-dashboard.php">Dashboard</a>
                <a class="nav-link" href="upload-track.php">Upload Track</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h1>Earnings Dashboard</h1>
        
        <!-- Earnings Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <h3>$<?php echo number_format($earnings_summary['purchase_earnings'] ?? 0, 2); ?></h3>
                        <p>Purchase Earnings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info">
                    <div class="card-body text-center">
                        <h3>$<?php echo number_format($earnings_summary['stream_earnings'] ?? 0, 2); ?></h3>
                        <p>Stream Earnings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body text-center">
                        <h3>$<?php echo number_format($earnings_summary['total_earnings'] ?? 0, 2); ?></h3>
                        <p>Total Earnings</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Earnings -->
        <div class="card">
            <div class="card-header">
                <h5>Recent Earnings</h5>
            </div>
            <div class="card-body">
                <?php if ($recent_earnings->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Track</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($earning = $recent_earnings->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($earning['title']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $earning['type'] === 'purchase' ? 'success' : 'info'; ?>">
                                            <?php echo ucfirst($earning['type']); ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($earning['amount'], 2); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($earning['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No earnings recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Withdrawal Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Withdraw Earnings</h5>
            </div>
            <div class="card-body">
                <p>Available balance: <strong>$0.00</strong> (Withdrawal feature coming soon)</p>
                <button class="btn btn-primary" disabled>Request Withdrawal</button>
                <small class="text-muted d-block mt-2">Minimum withdrawal amount: $50.00</small>
            </div>
        </div>
    </div>
</body>
</html>