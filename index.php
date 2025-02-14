<?php
session_start();

// Include database connection
include('db_conn.php'); // Assuming you have a file for your DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get input values from the login form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the user exists and has approval
    $query = "SELECT user.*, employee.approve 
              FROM user 
              JOIN employee ON user.employee_id = employee.id 
              WHERE user.email = ?";
              
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($user['approve'] == 0) {
                echo "<script>alert('Your account is not approved yet or dismissed.'); window.location.href='index.php';</script>";
                exit();
            } elseif (password_verify($password, $user['password'])) {
                // Store user ID in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['userrole_id'] = $user['userrole_id'];
                // Redirect to dashboard.php
                header('Location: dashboard.php');
                exit();
            } else {
                echo "<script>alert('Invalid password.'); window.location.href='index.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('User not found.'); window.location.href='index.php';</script>";
            exit();
        }

        $stmt->close();
    }
}

// Close the DB connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automated Payroll Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="h-full w-full bg-cover bg-center bg-fixed" style="background-image: url('uploads/home.webp');">
    <header class="min-h-screen bg-blue-950 bg-opacity-70 flex flex-col">
        <!-- Navigation -->
        <nav class="flex justify-end p-5">
            <button id="signInBtn" class="btn text-lg font-semibold bg-rose-500 border-none hover:bg-rose-600">Sign In</button>
        </nav>

        <!-- Login Section -->
        <section id="loginForm" class="absolute right-0 m-5 rounded-lg shadow-lg flex items-center justify-center bg-opacity-70 hidden bg-black">
            <div class="bg-blue-100 bg-opacity-80 rounded-lg shadow-lg text-center max-w-3xl">
                <form action="index.php" method="POST" class="space-y-4">
                    <div class="px-12 pb-12 bg-white mx-auto rounded-lg w-100">
                        <div class="flex justify-end pt-2 -mr-9">
                            <i id="cancelBtn" class="fa-solid fa-xmark w-max hover:text-green-500"></i>
                        </div>
                        <div class="mb-4 text-left">
                            <h3 class="font-semibold text-2xl text-gray-800">Sign In </h3>
                            <p class="text-gray-500">Please sign in to your account.</p>
                        </div>
                        <?php if (isset($error)): ?>
                            <div class="text-red-500 text-sm mb-4"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <div class="space-y-5">
                            <div class="space-y-2 text-left">
                                <label class="text-sm font-medium text-gray-700 tracking-wide">Email</label>
                                <input class="w-full text-base px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-400" type="email" name="email" placeholder="mail@gmail.com" required>
                            </div>
                            <div class="space-y-2 text-left">
                                <label class="mb-5 text-sm font-medium text-gray-700 tracking-wide"> Password </label>
                                <input class="w-full content-center text-base px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-400" type="password" name="password" placeholder="Enter your password" required>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 bg-blue-500 focus:ring-blue-400 border-gray-300 rounded">
                                    <label for="remember_me" class="ml-2 block text-sm text-gray-800"> Remember me </label>
                                </div>
                                <div class="text-sm">
                                    <a href="#" class="text-green-500 hover:text-blue-500"> Forgot your password? </a>
                                </div>
                            </div>
                            <div>
                                <button type="submit" class="w-full flex justify-center bg-blue-400 hover:bg-green-500 text-gray-100 p-3 rounded-full tracking-wide font-semibold shadow-lg cursor-pointer transition ease-in duration-500"> Sign in </button>
                            </div>
                        </div>
                        <div class="pt-5 text-center">
                            <span> Don't have account? <a href="registration/employee.php" class="text-green-500 hover:text-blue-500">Sign up here</a> </span>
                        </div>
                    </div>
                </form>
            </div>
        </section>


        <!-- Hero Section -->
        <section class="flex-grow flex items-center justify-center px-10">
            <div class="bg-gray-900 bg-opacity-40 p-10 rounded-lg shadow-lg text-center max-w-3xl">
                <h1 class="text-4xl text-white font-semibold mb-5">Automated Payroll Management System</h1>
                <p class="text-base text-white leading-relaxed">
                    The Automated Payroll Management System is designed to simplify and enhance payroll processing for your organization. 
                    It ensures accurate salary calculations by considering grades, designations, additional duties, allowances, and deductions.
                </p>
                <p class="text-base text-white leading-relaxed mt-4">
                    Key benefits include:
                    <ul class="list-disc list-inside mt-2 text-white">
                        <li><strong>Accuracy:</strong> Eliminate manual errors with precise calculations.</li>
                        <li><strong>Efficiency:</strong> Save time with automated workflows and timely payroll processing.</li>
                        <li><strong>Insights:</strong> Access detailed reports on salary distributions and trends.</li>
                        <li><strong>Scalability:</strong> Suitable for organizations of any size, from startups to enterprises.</li>
                        <li><strong>Compliance:</strong> Adhere to labor laws and organizational policies effortlessly.</li>
                    </ul>
                </p>
                <p class="text-base text-white leading-relaxed mt-4">
                    Experience the power of automation and enhance employee satisfaction with this robust and user-friendly payroll solution.
                </p>
            </div>
        </section>

        
        <!-- Why Choose Us -->
        <div class="bg-white bg-opacity-10 p-8 text-center">
            <h2 class="text-3xl text-white font-semibold mb-4">Why Choose Us?</h2>
            <p class="text-lg text-white mb-4">
                Our payroll system is trusted by numerous organizations for its reliability and ease of use. With a dedicated support team and regular updates, 
                we ensure your payroll processes are always up to date with the latest industry standards.
            </p>
            <h3 class="text-2xl text-white font-semibold mt-6">Features You Can Count On:</h3>
            <ul class="list-disc list-inside mt-4 text-white text-lg">
                <li>Customizable allowances and deductions for unique employee needs.</li>
                <li>Multi-currency and multi-location support for global businesses.</li>
                <li>Secure data storage and access with role-based permissions.</li>
                <li>Automated tax calculations and compliance checks.</li>
            </ul>
        </div>

        <!-- Testimonials -->
        <div class="bg-white bg-opacity-20 p-8 text-center">
            <h2 class="text-3xl text-white font-semibold mb-4">What Our Users Say</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-5 bg-blue-950 bg-opacity-60 rounded shadow-md">
                    <p class="text-white italic">
                        "The best decision we made for our HR department. It has reduced processing time by 70% and improved accuracy significantly!"
                    </p>
                    <p class="text-white mt-2 font-semibold">- HR Manager, TechCorp</p>
                </div>
                <div class="p-5 bg-blue-950 bg-opacity-60 rounded shadow-md">
                    <p class="text-white italic">
                        "With this system, we can focus on strategy rather than administrative tasks. It's a game-changer!"
                    </p>
                    <p class="text-white mt-2 font-semibold">- CEO, Startup Inc.</p>
                </div>
            </div>
        </div>

        <!-- Success Metrics -->
        <div class="bg-white bg-opacity-10 p-8 text-center">
            <h2 class="text-3xl text-white font-semibold mb-4">Our Achievements</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-4xl text-white font-bold">95%</p>
                    <p class="text-white">Customer Satisfaction</p>
                </div>
                <div>
                    <p class="text-4xl text-white font-bold">1M+</p>
                    <p class="text-white">Payrolls Processed</p>
                </div>
                <div>
                    <p class="text-4xl text-white font-bold">10+</p>
                    <p class="text-white">Industries Served</p>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="bg-blue-950 bg-opacity-90 p-8 text-center">
            <h2 class="text-3xl text-white font-semibold mb-4">Ready to Get Started?</h2>
            <p class="text-lg text-white mb-4">
                Transform your payroll processes today with our reliable, accurate, and efficient system. Sign up now and join thousands of satisfied users.
            </p>
            <button class="btn text-lg font-semibold bg-rose-500 border-none hover:bg-rose-600">
                Get Started
            </button>
        </div>

        <script>
            // Show the login form when the "Sign In" button is clicked
            document.getElementById('signInBtn').addEventListener('click', function () {
                document.getElementById('loginForm').classList.remove('hidden');
            });

            // Hide the login form when the "Cancel" button is clicked
            document.getElementById('cancelBtn').addEventListener('click', function () {
                document.getElementById('loginForm').classList.add('hidden');
            });
        </script>
    </header>
</body>
</html>
