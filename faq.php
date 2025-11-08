<?php
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Handmade Haven</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1>Frequently Asked Questions</h1>
            <p>Find answers to common questions about our platform</p>
        </div>

        <div class="faq-categories">
            <div class="faq-category active" data-category="general">General</div>
            <div class="faq-category" data-category="ordering">Ordering</div>
            <div class="faq-category" data-category="shipping">Shipping</div>
            <div class="faq-category" data-category="artisan">For Artisans</div>
        </div>

        <div class="faq-content">
            <div class="faq-section active" id="general">
                <h2>General Questions</h2>
                <div class="faq-item">
                    <div class="faq-question">What is Handmade Haven?</div>
                    <div class="faq-answer">
                        <p>Handmade Haven is a marketplace connecting artisans with customers who appreciate handmade, unique products. We support independent makers and provide a platform for them to sell their creations.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">How do I create an account?</div>
                    <div class="faq-answer">
                        <p>Click on "Register" in the top navigation and fill out the form. You can choose to register as a customer or as an artisan if you want to sell your products.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Is there a fee to use Handmade Haven?</div>
                    <div class="faq-answer">
                        <p>It's free to browse and create an account. Artisans pay a commission fee on sales, which helps us maintain and improve the platform.</p>
                    </div>
                </div>
            </div>

            <div class="faq-section" id="ordering">
                <h2>Ordering & Payments</h2>
                <div class="faq-item">
                    <div class="faq-question">What payment methods do you accept?</div>
                    <div class="faq-answer">
                        <p>We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and Apple Pay.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Can I modify or cancel my order?</div>
                    <div class="faq-answer">
                        <p>You can modify or cancel your order within 1 hour of placing it. After that, please contact the artisan directly through their store page.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Are the prices on the site final?</div>
                    <div class="faq-answer">
                        <p>Yes, all prices are in USD and include any applicable taxes. Shipping costs are calculated at checkout.</p>
                    </div>
                </div>
            </div>

            <div class="faq-section" id="shipping">
                <h2>Shipping & Delivery</h2>
                <div class="faq-item">
                    <div class="faq-question">How long does shipping take?</div>
                    <div class="faq-answer">
                        <p>Shipping times vary by artisan and shipping method selected. Most standard shipments arrive within 5-7 business days in the US.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Do you ship internationally?</div>
                    <div class="faq-answer">
                        <p>Yes, we ship to over 50 countries worldwide. International shipping costs and times vary by destination.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Can I track my order?</div>
                    <div class="faq-answer">
                        <p>Yes, once your order ships, you'll receive a tracking number via email. You can also track your order from your account dashboard.</p>
                    </div>
                </div>
            </div>

            <div class="faq-section" id="artisan">
                <h2>For Artisans</h2>
                <div class="faq-item">
                    <div class="faq-question">How do I become an artisan on Handmade Haven?</div>
                    <div class="faq-answer">
                        <p>Register for an account and select "I want to sell my handmade products." You'll need to provide some information about your craft and agree to our artisan terms.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">What are the commission rates?</div>
                    <div class="faq-answer">
                        <p>We charge a 10% commission on each sale. This includes payment processing fees and platform maintenance.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">How do I get paid?</div>
                    <div class="faq-answer">
                        <p>Payments are processed bi-weekly via direct deposit or PayPal. You can set up your payment method in your artisan dashboard.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-contact">
            <h2>Still have questions?</h2>
            <p>Can't find the answer you're looking for? Please <a href="contact.php">contact our support team</a>.</p>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // FAQ functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Category tabs
            const categories = document.querySelectorAll('.faq-category');
            const sections = document.querySelectorAll('.faq-section');
            
            categories.forEach(category => {
                category.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-category');
                    
                    // Update active category
                    categories.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding section
                    sections.forEach(section => {
                        section.classList.remove('active');
                        if (section.id === categoryId) {
                            section.classList.add('active');
                        }
                    });
                });
            });
            
            // FAQ item toggle
            const faqItems = document.querySelectorAll('.faq-item');
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                question.addEventListener('click', function() {
                    item.classList.toggle('active');
                });
            });
        });
    </script>
</body>
</html>