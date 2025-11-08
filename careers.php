<?php
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers - Handmade Haven</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1>Join Our Team</h1>
            <p>Help us build the world's best handmade marketplace</p>
        </div>

        <section class="careers-hero">
            <div class="hero-content">
                <h2>Work With Purpose</h2>
                <p>At Handmade Haven, we're passionate about supporting artisans and connecting them with customers who appreciate handmade quality. Join us in our mission to empower creators worldwide.</p>
            </div>
        </section>

        <section class="benefits">
            <h2>Why Work With Us?</h2>
            <div class="benefits-grid">
                <div class="benefit-item">
                    <i class="fas fa-heart"></i>
                    <h3>Mission-Driven</h3>
                    <p>Work that makes a real difference in artisans' lives</p>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-users"></i>
                    <h3>Great Culture</h3>
                    <p>Collaborative, supportive, and creative environment</p>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-home"></i>
                    <h3>Remote First</h3>
                    <p>Work from anywhere with flexible hours</p>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-gift"></i>
                    <h3>Artisan Discounts</h3>
                    <p>Discounts on products from our talented artisans</p>
                </div>
            </div>
        </section>

        <section class="open-positions">
            <h2>Open Positions</h2>
            <div class="positions-list">
                <div class="position-card">
                    <h3>Senior PHP Developer</h3>
                    <div class="position-meta">
                        <span class="location">Remote</span>
                        <span class="type">Full-time</span>
                        <span class="department">Engineering</span>
                    </div>
                    <p>We're looking for an experienced PHP developer to help scale our platform and build new features for our community.</p>
                    <a href="position.php?id=1" class="btn btn-primary">Apply Now</a>
                </div>

                <div class="position-card">
                    <h3>Community Manager</h3>
                    <div class="position-meta">
                        <span class="location">Remote</span>
                        <span class="type">Full-time</span>
                        <span class="department">Community</span>
                    </div>
                    <p>Help grow and engage our community of artisans and customers through social media, events, and support.</p>
                    <a href="position.php?id=2" class="btn btn-primary">Apply Now</a>
                </div>

                <div class="position-card">
                    <h3>UX/UI Designer</h3>
                    <div class="position-meta">
                        <span class="location">Remote</span>
                        <span class="type">Full-time</span>
                        <span class="department">Design</span>
                    </div>
                    <p>Create beautiful, intuitive experiences for our users across web and mobile platforms.</p>
                    <a href="position.php?id=3" class="btn btn-primary">Apply Now</a>
                </div>
            </div>
        </section>

        <section class="application-process">
            <h2>Application Process</h2>
            <div class="process-steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Apply</h3>
                    <p>Submit your application and resume</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Interview</h3>
                    <p>Video call with our hiring team</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Challenge</h3>
                    <p>Complete a role-specific task</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Offer</h3>
                    <p>Receive and review your offer</p>
                </div>
            </div>
        </section>

        <section class="culture">
            <h2>Our Culture</h2>
            <div class="culture-content">
                <p>We believe in transparency, collaboration, and continuous learning. Our team is distributed across the globe, united by our mission to support handmade craftsmanship.</p>
                <div class="culture-values">
                    <div class="value">
                        <h4>Empathy First</h4>
                        <p>We prioritize understanding our users' needs</p>
                    </div>
                    <div class="value">
                        <h4>Quality Matters</h4>
                        <p>We take pride in delivering excellent work</p>
                    </div>
                    <div class="value">
                        <h4>Growth Mindset</h4>
                        <p>We're always learning and improving</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="careers-contact">
            <h2>Don't See the Right Role?</h2>
            <p>We're always interested in meeting talented people. Send your resume to <a href="mailto:careers@handmadehaven.com">careers@handmadehaven.com</a>.</p>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>