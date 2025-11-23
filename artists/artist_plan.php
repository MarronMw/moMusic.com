<?php
include 'config.php';
include 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Plans - moreMusic.com</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">MusicStream</a>
        </div>
    </nav>

    <div class="container my-5">
        <h1 class="text-center mb-5">Choose Your Artist Plan</h1>
        <div class="row">
            <?php
            $plans_query = "SELECT * FROM artist_plans ORDER BY price ASC";
            $plans_result = $conn->query($plans_query);
            
            while ($plan = $plans_result->fetch_assoc()):
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header text-center">
                        <h3><?php echo $plan['name']; ?></h3>
                        <h2 class="text-primary">$<?php echo $plan['price']; ?>/month</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-muted"><?php echo $plan['description']; ?></p>
                        <ul class="list-unstyled">
                            <li><?php echo $plan['track_limit'] ? $plan['track_limit'] . ' tracks' : 'Unlimited tracks'; ?></li>
                            <li><?php echo $plan['storage_limit_mb']; ?> MB storage</li>
                            <li><?php echo $plan['commission_rate']; ?>% platform commission</li>
                            <?php
                            $features = json_decode($plan['features'], true);
                            foreach ($features as $feature):
                            ?>
                            <li>✓ <?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="card-footer text-center">
                        <?php if ($auth->isLoggedIn()): ?>
                            <a href="artist-subscribe.php?plan_id=<?php echo $plan['id']; ?>" class="btn btn-primary w-100">Choose Plan</a>
                        <?php else: ?>
                            <a href="artist-register.php" class="btn btn-outline-primary w-100">Get Started</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>