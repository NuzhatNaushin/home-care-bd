<?php

$type = isset($_GET['type']) ? $_GET['type'] : 'patient';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Home Care BD</title>

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
        
        .registration-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 30px 0;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(44, 79, 135, 0.25);
            border-color: var(--primary-color);
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
        }
        
        .nav-pills .nav-link {
            color: var(--primary-color);
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
                        <a class="nav-link" href="index.php">Home</a>
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
                        <a class="btn btn-outline-primary" href="index.php#login-section">Sign In</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="registration-container">
                        <h2 class="text-center mb-4">Create Your Account</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?php
                                    switch ($error) {
                                        case 'email_exists':
                                            echo 'Email address already exists. Please use a different email or try logging in.';
                                            break;
                                        case 'password_mismatch':
                                            echo 'Passwords do not match. Please try again.';
                                            break;
                                        case 'invalid_email':
                                            echo 'Please enter a valid email address.';
                                            break;
                                        case 'empty_fields':
                                            echo 'Please fill in all required fields.';
                                            break;
                                        default:
                                            echo 'An error occurred during registration. Please try again.';
                                    }
                                ?>
                            </div>
                        <?php endif; ?>
               
                        <ul class="nav nav-pills nav-justified mb-4" id="registerTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($type === 'patient') ? 'active' : ''; ?>" 
                                        id="patient-tab" data-bs-toggle="pill" data-bs-target="#patient" 
                                        type="button" role="tab" aria-controls="patient" 
                                        aria-selected="<?php echo ($type === 'patient') ? 'true' : 'false'; ?>">
                                    Register as Patient
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($type === 'attendant') ? 'active' : ''; ?>" 
                                        id="attendant-tab" data-bs-toggle="pill" data-bs-target="#attendant" 
                                        type="button" role="tab" aria-controls="attendant" 
                                        aria-selected="<?php echo ($type === 'attendant') ? 'true' : 'false'; ?>">
                                    Register as Caregiver
                                </button>
                            </li>
                        </ul>
                        

                        <div class="tab-content" id="registerTabContent">
            
                            <div class="tab-pane fade <?php echo ($type === 'patient') ? 'show active' : ''; ?>" id="patient" role="tabpanel" aria-labelledby="patient-tab">
                                <form action="register_process.php" method="post" class="needs-validation" novalidate>
                                    <input type="hidden" name="role" value="patient">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                            <div class="invalid-feedback">
                                                Please provide your full name.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                            <div class="invalid-feedback">
                                                Please choose a username.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                            <div class="invalid-feedback">
                                                Please provide a valid email address.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" required>
                                            <div class="invalid-feedback">
                                                Please provide your phone number.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <div class="invalid-feedback">
                                                Please provide a password.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="confirm_password" class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            <div class="invalid-feedback">
                                                Please confirm your password.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="birth_date" class="form-label">Date of Birth</label>
                                            <input type="date" class="form-control" id="birth_date" name="birth_date" required>
                                            <div class="invalid-feedback">
                                                Please provide your date of birth.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="age" class="form-label">Age</label>
                                            <input type="number" class="form-control" id="age" name="age" required>
                                            <div class="invalid-feedback">
                                                Please provide your age.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="emergency_contact" class="form-label">Emergency Contact Number</label>
                                        <input type="tel" class="form-control" id="emergency_contact" name="emergency_contact" required>
                                        <div class="invalid-feedback">
                                            Please provide an emergency contact number.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="medical_condition" class="form-label">Medical Condition (optional)</label>
                                        <textarea class="form-control" id="medical_condition" name="medical_condition" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                        <label class="form-check-label" for="terms">I agree to the <a href="terms.php">Terms & Conditions</a> and <a href="privacy.php">Privacy Policy</a></label>
                                        <div class="invalid-feedback">
                                            You must agree before submitting.
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Register as Patient</button>
                                    </div>
                                </form>
                            </div>
                            
                   
                            <div class="tab-pane fade <?php echo ($type === 'attendant') ? 'show active' : ''; ?>" id="attendant" role="tabpanel" aria-labelledby="attendant-tab">
                                <form action="register_process.php" method="post" class="needs-validation" novalidate>
                                    <input type="hidden" name="role" value="attendant">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="att_name" name="name" required>
                                            <div class="invalid-feedback">
                                                Please provide your full name.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="att_username" name="username" required>
                                            <div class="invalid-feedback">
                                                Please choose a username.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="att_email" name="email" required>
                                            <div class="invalid-feedback">
                                                Please provide a valid email address.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" id="att_phone" name="phone" required>
                                            <div class="invalid-feedback">
                                                Please provide your phone number.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="att_password" name="password" required>
                                            <div class="invalid-feedback">
                                                Please provide a password.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="confirm_password" class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="att_confirm_password" name="confirm_password" required>
                                            <div class="invalid-feedback">
                                                Please confirm your password.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="birth_date" class="form-label">Date of Birth</label>
                                            <input type="date" class="form-control" id="att_birth_date" name="birth_date" required>
                                            <div class="invalid-feedback">
                                                Please provide your date of birth.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="specialization" class="form-label">Specialization</label>
                                            <select class="form-select" id="specialization" name="specialization" required>
                                                <option value="">Select Specialization</option>
                                                <option value="Elderly Care">Elderly Care</option>
                                                <option value="Disability Support">Disability Support</option>
                                                <option value="Post-Surgery Assistance">Post-Surgery Assistance</option>
                                                <option value="Dementia Care">Dementia Care</option>
                                                <option value="Pediatric Care">Pediatric Care</option>
                                                <option value="Physiotherapy">Physiotherapy</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select your specialization.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="qualification" class="form-label">Qualification</label>
                                            <input type="text" class="form-control" id="qualification" name="qualification" required>
                                            <div class="invalid-feedback">
                                                Please provide your qualification.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="experience" class="form-label">Years of Experience</label>
                                            <input type="number" class="form-control" id="experience" name="experience" required>
                                            <div class="invalid-feedback">
                                                Please provide your years of experience.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="att_terms" name="terms" required>
                                        <label class="form-check-label" for="att_terms">I agree to the <a href="terms.php">Terms & Conditions</a> and <a href="privacy.php">Privacy Policy</a></label>
                                        <div class="invalid-feedback">
                                            You must agree before submitting.
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success">Register as Caregiver</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p>Already have an account? <a href="index.php#login-section">Sign In</a></p>
                        </div>
                    </div>
                </div>
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
    
    <script>
     
        (function () {
            'use strict'

            
            var forms = document.querySelectorAll('.needs-validation')

 
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }

                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>