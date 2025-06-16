<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Care BD - Your Trusted Healthcare Partner</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c4f87;
            --secondary-color: #4caf50;
            --light-bg: #f5f9ff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .nav-link {
            color: #333;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: var(--secondary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .hero-section {
            background: linear-gradient(rgba(44, 79, 135, 0.8), rgba(44, 79, 135, 0.9)), url('images/healthcare-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
        
        .feature-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            height: 100%;
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            background-color: var(--light-bg);
            color: var(--primary-color);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin: 15px 0;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 50px 0 20px;
        }
        
        .footer-links a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .social-icons a {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            transition: background-color 0.3s;
        }
        
        .social-icons a:hover {
            background-color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-heartbeat me-2"></i>Home Care BD
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="jobs.php">Find Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendants.php">Find Caregivers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary" href="#login-section">Sign In</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-primary" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

 
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Quality Healthcare Services at Your Doorstep</h1>
            <p class="lead mb-5">Connecting patients with qualified caregivers for personalized home care services in Bangladesh</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="register.php?type=patient" class="btn btn-light btn-lg px-4">I Need Care</a>
                <a href="register.php?type=attendant" class="btn btn-success btn-lg px-4">I Provide Care</a>
            </div>
        </div>
    </section>

   
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">How Home Care BD Works</h2>
                <p class="text-muted">We make it easy to find and connect with qualified healthcare providers</p>
            </div>
            <div class="row g-4">
        
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon mx-auto">
                            <i class="fas fa-user-md fa-2x"></i>
                        </div>
                        <h4>Find Qualified Caregivers</h4>
                        <p class="text-muted">Browse through profiles of verified and experienced healthcare attendants specializing in various medical conditions.</p>
                    </div>
                </div>
         
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon mx-auto">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                        <h4>Book Services Easily</h4>
                        <p class="text-muted">Schedule appointments, discuss your needs, and book caregivers with our simple and secure platform.</p>
                    </div>
                </div>
          
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon mx-auto">
                            <i class="fas fa-hand-holding-medical fa-2x"></i>
                        </div>
                        <h4>Receive Quality Care</h4>
                        <p class="text-muted">Get personalized care services at home from professionals who understand your medical needs.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Our Services</h2>
                <p class="text-muted">Comprehensive healthcare solutions for various needs</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-light p-3 d-inline-block mb-3">
                                <i class="fas fa-user-nurse text-primary fa-2x"></i>
                            </div>
                            <h4 class="card-title">Elderly Care</h4>
                            <p class="card-text text-muted">Specialized care for elderly patients, including assistance with daily activities, medication management, and companionship.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-light p-3 d-inline-block mb-3">
                                <i class="fas fa-procedures text-primary fa-2x"></i>
                            </div>
                            <h4 class="card-title">Post-Surgery Care</h4>
                            <p class="card-text text-muted">Professional assistance during recovery after surgeries, including wound care, mobility support, and monitoring.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-light p-3 d-inline-block mb-3">
                                <i class="fas fa-wheelchair text-primary fa-2x"></i>
                            </div>
                            <h4 class="card-title">Disability Support</h4>
                            <p class="card-text text-muted">Compassionate care for individuals with disabilities, focusing on enhancing independence and quality of life.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="services.php" class="btn btn-outline-primary">View All Services</a>
            </div>
        </div>
    </section>


    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">What Our Clients Say</h2>
                <p class="text-muted">Hear from patients and caregivers who've used our platform</p>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <img src="images/avatar-placeholder.jpg" alt="Client" class="rounded-circle" width="60" height="60">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0">Kamal Hossain</h5>
                                <p class="text-muted mb-0">Patient</p>
                            </div>
                        </div>
                        <p class="mb-0">"Finding a reliable caregiver for my elderly mother was so easy with Home Care BD. The attendant is professional, caring, and has made a significant difference in my mother's life."</p>
                        <div class="mt-3 text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <img src="images/avatar-placeholder.jpg" alt="Client" class="rounded-circle" width="60" height="60">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0">Md. Rahim Uddin</h5>
                                <p class="text-muted mb-0">Caregiver</p>
                            </div>
                        </div>
                        <p class="mb-0">"Home Care BD has transformed my career as a healthcare provider. The platform connects me with patients who truly need my skills, and the payment system is transparent and reliable."</p>
                        <div class="mt-3 text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <img src="images/avatar-placeholder.jpg" alt="Client" class="rounded-circle" width="60" height="60">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0">Naimur Rahman</h5>
                                <p class="text-muted mb-0">Patient</p>
                            </div>
                        </div>
                        <p class="mb-0">"After my surgery, I needed post-operative care but was worried about finding the right help. Home Care BD matched me with a skilled nurse who provided excellent care throughout my recovery."</p>
                        <div class="mt-3 text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="login-section" class="py-5 bg-white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="login-container">
                        <h2 class="text-center mb-4">Sign In</h2>
                        
                        <?php
            
                        if (isset($_GET['error'])) {
                            $error = $_GET['error'];
                            echo '<div class="alert alert-danger">';
                            if ($error == 'invalid') {
                                echo 'Invalid email or password. Please try again.';
                            } elseif ($error == 'empty') {
                                echo 'Please fill in all required fields.';
                            } else {
                                echo 'An error occurred. Please try again.';
                            }
                            echo '</div>';
                        }
                        
                        if (isset($_GET['success'])) {
                            echo '<div class="alert alert-success">Registration successful! Please sign in.</div>';
                        }
                        ?>
                        
                        <form action="login_process.php" method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Sign In</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="forgot_password.php">Forgot Password?</a>
                            </div>
                        </form>
                        <hr>
                        <div class="text-center">
                            <p>Don't have an account?</p>
                            <div class="row">
                                <div class="col-6">
                                    <a href="register.php?type=patient" class="btn btn-outline-primary w-100">Register as Patient</a>
                                </div>
                                <div class="col-6">
                                    <a href="register.php?type=attendant" class="btn btn-outline-success w-100">Register as Caregiver</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" style="background-color: var(--primary-color);">
        <div class="container text-center text-white">
            <h2 class="fw-bold mb-4">Ready to Experience Quality Home Care?</h2>
            <p class="lead mb-4">Join thousands of satisfied users across Bangladesh</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="register.php?type=patient" class="btn btn-light btn-lg">Register as Patient</a>
                <a href="register.php?type=attendant" class="btn btn-success btn-lg">Register as Caregiver</a>
            </div>
        </div>
    </section>


    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h4 class="mb-4"><i class="fas fa-heartbeat me-2"></i>Home Care BD</h4>
                    <p>Connecting patients with qualified caregivers for personalized home care services in Bangladesh.</p>
                    <div class="social-icons mt-4">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-4">Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="index.php">Home</a></li>
                        <li class="mb-2"><a href="about.php">About Us</a></li>
                        <li class="mb-2"><a href="services.php">Services</a></li>
                        <li class="mb-2"><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-4">For Patients</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="find_caregiver.php">Find Caregivers</a></li>
                        <li class="mb-2"><a href="post_job.php">Post a Job</a></li>
                        <li class="mb-2"><a href="patient_faq.php">Patient FAQ</a></li>
                        <li class="mb-2"><a href="safety_tips.php">Safety Tips</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-4">For Caregivers</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="find_jobs.php">Find Jobs</a></li>
                        <li class="mb-2"><a href="caregiver_resources.php">Resources</a></li>
                        <li class="mb-2"><a href="caregiver_faq.php">Caregiver FAQ</a></li>
                        <li class="mb-2"><a href="verification.php">Get Verified</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5 class="mb-4">Contact Info</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Dhaka, Bangladesh</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +880 1712-345678</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@homecarebd.com</li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; 2025 Home Care BD. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="terms.php">Terms & Conditions</a></li>
                        <li class="list-inline-item"><span class="mx-2">|</span></li>
                        <li class="list-inline-item"><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>