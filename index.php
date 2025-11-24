<?php
include './utils/config.php';
include './utils/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>moMusic.com - Listen, Create, Earn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2',
                        accent: '#f093fb',
                        dark: '#2d3748',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="font-sans antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-music text-2xl text-primary mr-2"></i>
                        <span class="text-xl font-bold text-gray-900">moMusic</span>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="#features" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition duration-300">Features</a>
                        <a href="#pricing" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition duration-300">Pricing</a>
                        <a href="#artists" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition duration-300">For Artists</a>
                        <a href="#tracks" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition duration-300">Browse</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($auth->isLoggedIn()): ?>
                        <span class="text-gray-700 text-sm">
                            Welcome, <?php echo $_SESSION['username']; ?>
                            <?php if ($auth->isPremium()): ?>
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full ml-2">PREMIUM</span>
                            <?php endif; ?>
                            <?php if ($artistAuth->isArtist()): ?>
                                <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full ml-2">ARTIST</span>
                            <?php endif; ?>
                        </span>
                        <a href="dashboard.php" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-secondary transition duration-300">
                            Dashboard
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-primary text-sm font-medium transition duration-300">
                            Sign In
                        </a>
                        <a href="register.php" class="bg-primary text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-secondary transition duration-300">
                            Get Started
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg text-white hero-pattern relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="animate-float">
                    <h1 class="text-5xl lg:text-6xl font-bold leading-tight mb-6">
                        Where Music
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-accent to-yellow-400">
                            Lives & Earns
                        </span>
                    </h1>
                    <p class="text-xl text-gray-200 mb-8 leading-relaxed">
                        Discover millions of songs, share your creations, and build your music career. 
                        Join the platform that empowers both listeners and creators.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <?php if (!$auth->isLoggedIn()): ?>
                            <a href="register.php" class="bg-white text-primary px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transition duration-300 shadow-lg text-center">
                                Start Listening Free
                            </a>
                            <a href="artist/register.php" class="border-2 border-white text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-white hover:text-primary transition duration-300 text-center">
                                Become an Artist
                            </a>
                        <?php else: ?>
                            <a href="browse.php" class="bg-white text-primary px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transition duration-300 shadow-lg text-center">
                                Browse Music
                            </a>
                            <?php if (!$artistAuth->isArtist()): ?>
                                <a href="artist/register.php" class="border-2 border-white text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-white hover:text-primary transition duration-300 text-center">
                                    Start Uploading
                                </a>
                            <?php else: ?>
                                <a href="artist/dashboard.php" class="border-2 border-white text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-white hover:text-primary transition duration-300 text-center">
                                    Artist Dashboard
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="relative">
                    <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20 shadow-2xl">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="bg-white/20 rounded-xl p-6 text-center backdrop-blur-sm">
                                <i class="fas fa-headphones text-3xl mb-4 text-accent"></i>
                                <h3 class="font-bold text-lg mb-2">10M+</h3>
                                <p class="text-gray-200 text-sm">Songs Available</p>
                            </div>
                            <div class="bg-white/20 rounded-xl p-6 text-center backdrop-blur-sm">
                                <i class="fas fa-users text-3xl mb-4 text-accent"></i>
                                <h3 class="font-bold text-lg mb-2">500K+</h3>
                                <p class="text-gray-200 text-sm">Active Artists</p>
                            </div>
                            <div class="bg-white/20 rounded-xl p-6 text-center backdrop-blur-sm">
                                <i class="fas fa-globe text-3xl mb-4 text-accent"></i>
                                <h3 class="font-bold text-lg mb-2">150+</h3>
                                <p class="text-gray-200 text-sm">Countries</p>
                            </div>
                            <div class="bg-white/20 rounded-xl p-6 text-center backdrop-blur-sm">
                                <i class="fas fa-money-bill-wave text-3xl mb-4 text-accent"></i>
                                <h3 class="font-bold text-lg mb-2">$5M+</h3>
                                <p class="text-gray-200 text-sm">Paid to Artists</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Choose moMusic?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    We've built the perfect platform for music lovers and creators alike
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition duration-300 border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-primary to-secondary rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-music text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Unlimited Streaming</h3>
                    <p class="text-gray-600 mb-6">
                        Access millions of songs from artists worldwide. Stream your favorite tracks anytime, anywhere.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Ad-free listening
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            High quality audio
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Offline downloads
                        </li>
                    </ul>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition duration-300 border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-primary to-secondary rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-upload text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">For Artists</h3>
                    <p class="text-gray-600 mb-6">
                        Upload your music, reach global audiences, and earn from every stream and download.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Easy upload system
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Detailed analytics
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Fair revenue share
                        </li>
                    </ul>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition duration-300 border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-primary to-secondary rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-crown text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Premium Experience</h3>
                    <p class="text-gray-600 mb-6">
                        Unlock exclusive features, premium content, and enhanced audio quality with our subscription plans.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Exclusive tracks
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Early access
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Priority support
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Tracks Section -->
    <section id="tracks" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Trending Now</h2>
                <p class="text-xl text-gray-600">Discover what everyone is listening to</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $query = "SELECT t.*, a.artist_name FROM tracks t 
                         LEFT JOIN artists a ON t.artist_id = a.id 
                         ORDER BY t.plays_count DESC LIMIT 6";
                $result = $conn->query($query);
                
                while ($track = $result->fetch_assoc()):
                ?>
                <div class="bg-gray-50 rounded-2xl p-6 shadow-lg hover:shadow-xl transition duration-300 group border border-gray-200">
                    <?php if ($track['is_premium']): ?>
                        <div class="absolute top-4 right-4 bg-gradient-to-r from-yellow-400 to-yellow-600 text-black px-3 py-1 rounded-full text-xs font-bold">
                            <i class="fas fa-crown mr-1"></i> PREMIUM
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-gradient-to-r from-primary to-secondary rounded-xl flex items-center justify-center">
                            <i class="fas fa-play text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-bold text-gray-900 text-lg group-hover:text-primary transition duration-300">
                                <?php echo htmlspecialchars($track['title']); ?>
                            </h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($track['artist_name'] ?? $track['artist']); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                        <span><?php echo gmdate("i:s", $track['duration']); ?></span>
                        <span><?php echo number_format($track['plays_count']); ?> plays</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-primary">$<?php echo $track['price']; ?></span>
                        <?php if ($auth->isLoggedIn()): ?>
                            <a href="track.php?id=<?php echo $track['id']; ?>" 
                               class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-secondary transition duration-300 font-medium">
                                Listen
                            </a>
                        <?php else: ?>
                            <a href="login.php" 
                               class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition duration-300 font-medium">
                                Sign In
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="text-center mt-12">
                <a href="browse.php" class="bg-primary text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-secondary transition duration-300 inline-flex items-center">
                    Browse All Tracks
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Simple, Transparent Pricing</h2>
                <p class="text-xl text-gray-300">Choose the plan that works best for you</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Listener Plan -->
                <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 hover:border-primary transition duration-300">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold mb-4">Listener</h3>
                        <div class="text-4xl font-bold text-primary mb-2">Free</div>
                        <p class="text-gray-400">Perfect for casual listening</p>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center text-gray-300">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Access to free tracks
                        </li>
                        <li class="flex items-center text-gray-300">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Basic audio quality
                        </li>
                        <li class="flex items-center text-gray-300">
                            <i class="fas fa-times text-red-500 mr-3"></i>
                            Limited skips
                        </li>
                        <li class="flex items-center text-gray-300">
                            <i class="fas fa-times text-red-500 mr-3"></i>
                            With ads
                        </li>
                    </ul>
                    <a href="register.php" class="block w-full bg-gray-700 text-white text-center py-3 rounded-xl hover:bg-gray-600 transition duration-300 font-medium">
                        Get Started
                    </a>
                </div>

                <!-- Premium Plan -->
                <div class="bg-gradient-to-b from-primary to-secondary rounded-2xl p-8 border-2 border-accent transform scale-105 relative">
                    <div class="absolute top-4 right-4 bg-accent text-black px-3 py-1 rounded-full text-sm font-bold">
                        MOST POPULAR
                    </div>
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold mb-4">Premium</h3>
                        <div class="text-4xl font-bold text-white mb-2">$9.99<span class="text-xl">/month</span></div>
                        <p class="text-gray-200">Unlimited everything</p>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center text-white">
                            <i class="fas fa-check text-white mr-3"></i>
                            Unlimited streaming
                        </li>
                        <li class="flex items-center text-white">
                            <i class="fas fa-check text-white mr-3"></i>
                            Premium tracks
                        </li>
                        <li class="flex items-center text-white">
                            <i class="fas fa-check text-white mr-3"></i>
                            High quality audio
                        </li>
                        <li class="flex items-center text-white">
                            <i class="fas fa-check text-white mr-3"></i>
                            Ad-free experience
                        </li>
                    </ul>
                    <a href="subscribe.php?plan=monthly" class="block w-full bg-white text-primary text-center py-3 rounded-xl hover:bg-gray-100 transition duration-300 font-medium">
                        Go Premium
                    </a>
                </div>

                <!-- Artist Plan -->
                <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 hover:border-purple-500 transition duration-300">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold mb-4">Artist Pro</h3>
                        <div class="text-4xl font-bold text-purple-500 mb-2">$14.99<span class="text-xl">/month</span></div>
                        <p class="text-gray-400">For serious creators</p>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center text-gray-300">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Upload 50 tracks
                        </li>
                        <li class="flex items-center text-gray-300">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Advanced analytics
                        </li>
                        <li class="flex items-center text-gray-300">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            15% commission
                        </li>
                        <li class="flex items-center text-gray-300">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Promotion tools
                        </li>
                    </ul>
                    <a href="artist/plans.php" class="block w-full bg-purple-600 text-white text-center py-3 rounded-xl hover:bg-purple-700 transition duration-300 font-medium">
                        Become Artist
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 gradient-bg text-white">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-bold mb-6">Ready to Start Your Musical Journey?</h2>
            <p class="text-xl text-gray-200 mb-8 max-w-2xl mx-auto">
                Join millions of music lovers and creators who are already using moMusic to discover, share, and monetize their passion for music.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <?php if (!$auth->isLoggedIn()): ?>
                    <a href="register.php" class="bg-white text-primary px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transition duration-300 shadow-lg">
                        Create Free Account
                    </a>
                    <a href="artist/register.php" class="border-2 border-white text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-white hover:text-primary transition duration-300">
                        Start as Artist
                    </a>
                <?php else: ?>
                    <a href="browse.php" class="bg-white text-primary px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transition duration-300 shadow-lg">
                        Discover Music
                    </a>
                    <?php if (!$artistAuth->isArtist()): ?>
                        <a href="artist/register.php" class="border-2 border-white text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-white hover:text-primary transition duration-300">
                            Become an Artist
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-music text-2xl text-primary mr-2"></i>
                        <span class="text-xl font-bold">moMusic</span>
                    </div>
                    <p class="text-gray-400">
                        The ultimate platform for music lovers and creators to connect, share, and earn.
                    </p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">For Listeners</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="browse.php" class="hover:text-white transition duration-300">Browse Music</a></li>
                        <li><a href="#pricing" class="hover:text-white transition duration-300">Premium Plans</a></li>
                        <li><a href="login.php" class="hover:text-white transition duration-300">Sign In</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">For Artists</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="artist/register.php" class="hover:text-white transition duration-300">Artist Signup</a></li>
                        <li><a href="artist/plans.php" class="hover:text-white transition duration-300">Artist Plans</a></li>
                        <li><a href="artist/upload.php" class="hover:text-white transition duration-300">Upload Music</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition duration-300">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition duration-300">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white transition duration-300">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 moMusic. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Smooth Scroll -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>