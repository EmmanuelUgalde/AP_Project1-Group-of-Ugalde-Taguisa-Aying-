<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedSync - Medical and Health Services</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Top Bar */
        .top-bar {
            background: #1e3a5f;
            color: white;
            padding: 10px 0;
            font-size: 13px;
        }

        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .top-bar-left {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .top-bar-left span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .top-bar-right {
            display: flex;
            gap: 15px;
        }

        .social-icon {
            width: 25px;
            height: 25px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s;
        }

        .social-icon:hover {
            background: #00bcd4;
        }

        header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 20px;
            font-weight: bold;
            color: #1e3a5f;
        }
        

        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #00bcd4;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-icon, .user-icon {
            width: 35px;
            height: 35px;
            background: #f5f5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-icon:hover, .user-icon:hover {
            background: #00bcd4;
            color: white;
        }

        .contact-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .phone-icon {
            width: 40px;
            height: 40px;
            background: #00bcd4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .contact-text p {
            font-size: 12px;
            color: #666;
            margin: 0;
        }

        .contact-text h4 {
            font-size: 15px;
            color: #1e3a5f;
            margin: 0;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(0,188,212,0.9) 0%, rgba(30,58,95,0.9) 100%), 
                        url('https://images.unsplash.com/photo-1551076805-e1869033e561?w=1200&h=600&fit=crop');
            background-size: cover;
            background-position: center;
            padding: 120px 0;
            color: white;
            position: relative;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .hero-text h1 {
            font-size: 52px;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-text p {
            font-size: 16px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .btn-services {
            background: white;
            color: #00bcd4;
            padding: 15px 35px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-services:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .hero-image img {
            width: 100%;
            border-radius: 20px;
        }

        /* Services Section */
        .services {
            padding: 80px 0;
            background: #f9f9f9;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-label {
            color: #00bcd4;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .section-header h2 {
            font-size: 42px;
            color: #1e3a5f;
            margin-bottom: 15px;
        }

        .section-header p {
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .service-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .service-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .service-badge {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 60px;
            background: #00bcd4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(0,188,212,0.4);
        }

        .service-content {
            padding: 30px 25px;
            text-align: center;
        }

        .service-content h3 {
            font-size: 20px;
            color: #1e3a5f;
            margin-bottom: 15px;
        }

        .service-content p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .service-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #00bcd4;
            color: white;
            border-radius: 50%;
            text-decoration: none;
            transition: background 0.3s;
        }

        .service-link:hover {
            background: #1e3a5f;
        }

        /* Extra Care Section */
        .extra-care {
            padding: 80px 0;
            background: white;
        }

        .extra-care-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .care-text-label {
            color: #00bcd4;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }

        .care-text h2 {
            font-size: 42px;
            color: #1e3a5f;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .care-text h2 span {
            color: #00bcd4;
        }

        .care-text p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .care-features {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
        }

        .care-feature {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(0,188,212,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00bcd4;
            font-size: 20px;
        }

        .feature-text h4 {
            font-size: 16px;
            color: #1e3a5f;
            margin-bottom: 5px;
        }

        .feature-text p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        .btn-learn {
            background: #00bcd4;
            color: white;
            padding: 15px 35px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-learn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,188,212,0.3);
        }

        .care-image {
            position: relative;
        }

        .care-image img {
            width: 100%;
            border-radius: 20px;
        }

        .play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            background: #00bcd4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
            cursor: pointer;
            transition: transform 0.3s;
            box-shadow: 0 0 0 20px rgba(0,188,212,0.2);
        }

        .play-button:hover {
            transform: translate(-50%, -50%) scale(1.1);
        }

        .experience-badge {
            position: absolute;
            bottom: 30px;
            left: 30px;
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .experience-badge h3 {
            font-size: 48px;
            color: #00bcd4;
            margin-bottom: 5px;
        }

        .experience-badge p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        @media (max-width: 968px) {
            .hero-content,
            .extra-care-content {
                grid-template-columns: 1fr;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .hero-text h1 {
                font-size: 38px;
            }

            .nav-links {
                display: flex;
                list-style: none;
                gap: 30px;
                align-items: center;
            }

            .top-bar-content {
                justify-content: center;
            }

            .section-header h2,
            .care-text h2 {
                font-size: 32px;
            }
        }

        @media (max-width: 568px) {
            .hero-text h1 {
                font-size: 28px;
            }

            .contact-box {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <span>📧 info@MedSync.com</span>
                    <span>📍 Cebu City, Philippines</span>
                </div>
                <div class="top-bar-right">
                    <div class="social-icon">f</div>
                    <div class="social-icon">t</div>
                    <div class="social-icon">in</div>
                    <div class="social-icon">📷</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                         <img src="/ap_project1/super_admin/includes/Screenshot1-removebg-preview.png" alt="Logo of Urban Medical" 
                         style="height: 30px; width: 50px">
                    <span>URBAN MEDICAL HOSPITAL</span>
                </div>
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="login.php">Log in</a></li>
                    <li><a href="create.php">Register</a></li>
                </ul>
                
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id = "home" class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Always in Sync<br>with Your Health</h1>
                    <p>MedSync Medical and Health Services is dedicated to providing synchronized, high-quality medical and wellness care for individuals and families.</p>
                    <a href="#services" class="btn-services">See All Services</a>
                </div>
                <div class="hero-image">
                    <img src="doctor_pic.jpg" alt="Doctor">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id ="services" class ="services">
        <div class="container">
            <div class="section-header">
                <p class="section-label">What We Do</p>
                <h2>Our Services</h2>
                <p>Delivering comprehensive healthcare with advanced technology and compassionate care for all your medical needs.</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1581594549595-35f6edc7b762?w=400&h=300&fit=crop" alt="Medical Service">
                        <div class="service-badge">🩺</div>
                    </div>
                    <div class="service-content">
                        <h3>Medical Advice &<br>Checkup</h3>
                        <p>Get professional medical advice and comprehensive health checkups from our experienced doctors.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="cardiologist_pic.jpg" alt="Cardiovascular">
                        <div class="service-badge">❤️</div>
                    </div>
                    <div class="service-content">
                        <h3>Cardiovascular<br>Care</h3>
                        <p>Specialized cardiovascular care designed to meet the unique health needs of every individual.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="pedia_pic.jpg" alt="Pediatrics">
                        <div class="service-badge">👶</div>
                    </div>
                    <div class="service-content">
                        <h3>Pediatrics</h3>
                        <p>Expert care for infants, children, and adolescents to support growth and wellness.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="laboratory_pic.jpg" alt="laboratory & Diagnostics">
                        <div class="service-badge">🧪</div>
                    </div>
                    <div class="service-content">
                        <h3>Laboratory & <br> Diagnostics </h3>
                        <p>Accurate laboratory tests and diagnostics for fast and reliable medical results.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="obgyne_pic.jpg" alt="OB-GYN">
                        <div class="service-badge">👩‍⚕️</div>
                    </div>
                    <div class="service-content">
                        <h3>Women’s Health &<br> OB-GYN </h3>
                        <p>Specialized care for pregnancy, reproductive health, and wellness.</p>
                    </div>
                </div>
                 <div class="service-card">
                    <div class="service-image">
                        <img src="mental_health.jpg" alt="Mental Health Counseling">
                        <div class="service-badge">🧠</div>
                    </div>
                    <div class="service-content">
                        <h3>Mental Health Counseling </h3>
                        <p>Counseling and therapy services to support emotional and psychological well-being.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Extra Care Section -->
    <section class="extra-care">
        <div class="container">
            <div class="extra-care-content">
                <div class="care-text">
                    <p class="care-text-label">Medical Provider</p>
                    <h2>Sync Your Health with Care</h2>
                    <p>At Urban Medical Hospital, we align advanced medical expertise with your unique needs, providing personalized care that keeps your body and mind in perfect harmony.</p>
                    <div class="care-features">
                        <div class="care-feature">
                            <div class="feature-icon">📋</div>
                            <div class="feature-text">
                                <h4>Make an Appointment</h4>
                                <p>+123 456 7890</p>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="btn-learn">Book an Appointment</a>
                </div>
                <div class="care-image">
                    <img src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=600&h=500&fit=crop" alt="Medical Team">
                    <div class="play-button">▶</div>
                    <div class="experience-badge">
                        <h3>10+</h3>
                        <p>Experience</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
