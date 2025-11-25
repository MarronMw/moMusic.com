<?php
include './../utils/auth.php';

if ($auth->isLoggedIn() && $artistAuth->isArtist()) {
    header("Location: artist_dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if ($auth->login($email, $password)) {
        if ($artistAuth->isArtist()) {
            header("Location: artist_dashboard.php");
            exit();
        } else {
            $error = "This account is not registered as an artist!";
            // $auth->logout();
        }
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Login - moMusic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="../index.php" class="inline-flex items-center text-2xl font-bold text-gray-900">
                <i class="fas fa-music text-purple-600 mr-2"></i>
                moMusic
            </a>
            <div class="mt-4">
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                    <i class="fas fa-microphone mr-2"></i>
                    Artist Portal
                </span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mt-4">Welcome back, Artist</h1>
            <p class="text-gray-600 mt-2">Sign in to your artist account</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent transition duration-200"
                            placeholder="Enter your artist email"
                        >
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <a href="forgot-password.php" class="text-sm text-purple-600 hover:text-purple-500">
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent transition duration-200"
                            placeholder="Enter your password"
                        >
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember"
                        class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Remember me
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-4 rounded-lg hover:from-purple-700 hover:to-pink-700 focus:ring-4 focus:ring-purple-200 transition duration-200 font-semibold shadow-lg"
                >
                    <i class="fas fa-microphone mr-2"></i>
                    Sign In as Artist
                </button>
            </form>

            <!-- Artist Features -->
            <div class="mt-8 p-4 bg-purple-50 rounded-lg border border-purple-200">
                <h3 class="text-sm font-semibold text-purple-900 mb-2">Artist Account Benefits:</h3>
                <ul class="text-sm text-purple-700 space-y-1">
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2 text-xs"></i>
                        Upload and manage your music
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2 text-xs"></i>
                        Track earnings and analytics
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2 text-xs"></i>
                        Connect with your fans
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2 text-xs"></i>
                        Monetize your creativity
                    </li>
                </ul>
            </div>
        </div>

        <!-- Sign Up Links -->
        <div class="text-center mt-8 space-y-2">
            <p class="text-gray-600">
                Don't have an artist account?
                <a href="register.php" class="text-purple-600 hover:text-purple-500 font-semibold ml-1">
                    Become an Artist
                </a>
            </p>
            <p class="text-gray-600">
                Regular user?
                <a href="../login.php" class="text-purple-600 hover:text-purple-500 font-semibold ml-1">
                    Listener Login
                </a>
            </p>
        </div>
    </div>

    <!-- Background Pattern -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-pink-50 opacity-50"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNlZGVkZWQiIGZpbGwtb3BhY2l0eT0iMC40Ij48Y2lyY2xlIGN4PSIzMCIgY3k9IjMwIiByPSIyIi8+PC9nPjwvZz48L3N2Zz4=')] opacity-20"></div>
    </div>
</body>
</html>