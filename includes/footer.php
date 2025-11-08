    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo">
                        <a href="index.php">
                            <i class="fas fa-hands"></i>
                            <span><?php echo SITE_NAME; ?></span>
                        </a>
                    </div>
                    <p class="footer-description">
                        Plateforme dédiée aux artisans locaux pour vendre leurs créations uniques et faites main. 
                        Soutenez l'artisanat et découvrez des pièces exceptionnelles.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Pinterest">
                            <i class="fab fa-pinterest"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Navigation</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="products.php">Boutique</a></li>
                        <li><a href="artisans.php">Artisans</a></li>
                        <li><a href="categories.php">Catégories</a></li>
                        <li><a href="about.php">À propos</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Catégories</h3>
                    <ul class="footer-links">
                        <li><a href="products.php?category=1">Céramique</a></li>
                        <li><a href="products.php?category=2">Textile</a></li>
                        <li><a href="products.php?category=3">Bois</a></li>
                        <li><a href="products.php?category=4">Métal</a></li>
                        <li><a href="products.php?category=5">Verre</a></li>
                        <li><a href="products.php?category=6">Cuir</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Informations</h3>
                    <ul class="footer-links">
                        <li><a href="shipping.php">Livraison & Retours</a></li>
                        <li><a href="terms.php">Conditions générales</a></li>
                        <li><a href="privacy.php">Politique de confidentialité</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="careers.php">Carrières</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Contact</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 Rue de l'Artisanat, 75001 Paris</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+33 1 23 45 67 89</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>contact@artisanat-local.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Lun - Ven: 9h - 18h</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <div class="copyright">
                        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Tous droits réservés.</p>
                    </div>
                    <div class="payment-methods">
                        <div class="payment-title">Paiements sécurisés</div>
                        <div class="payment-icons">
                            <i class="fab fa-cc-visa" title="Visa"></i>
                            <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                            <i class="fab fa-cc-paypal" title="PayPal"></i>
                            <i class="fab fa-cc-apple-pay" title="Apple Pay"></i>
                            <i class="fab fa-cc-stripe" title="Stripe"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="js/app.js"></script>
    
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Analytics -->
    <script>
        // Google Analytics (à configurer)
        // (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        // (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        // m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        // })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
        // ga('create', 'UA-XXXXX-Y', 'auto');
        // ga('send', 'pageview');
    </script>

    <!-- Chat en ligne -->
    <div id="chat-widget" class="chat-widget">
        <div class="chat-toggle">
            <i class="fas fa-comments"></i>
        </div>
        <div class="chat-window">
            <div class="chat-header">
                <h4>Service Client</h4>
                <button class="chat-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="chat-messages">
                <div class="message bot">
                    <p>Bonjour ! Comment pouvons-nous vous aider ?</p>
                </div>
            </div>
            <div class="chat-input">
                <input type="text" placeholder="Tapez votre message...">
                <button class="send-btn"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <style>
    .chat-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }

    .chat-toggle {
        width: 60px;
        height: 60px;
        background: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        box-shadow: var(--shadow-lg);
        transition: var(--transition);
    }

    .chat-toggle:hover {
        background: var(--primary-dark);
        transform: scale(1.1);
    }

    .chat-toggle i {
        font-size: 1.5rem;
    }

    .chat-window {
        position: absolute;
        bottom: 70px;
        right: 0;
        width: 350px;
        height: 400px;
        background: white;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-lg);
        display: none;
        flex-direction: column;
    }

    .chat-window.active {
        display: flex;
    }

    .chat-header {
        padding: 1rem;
        background: var(--primary-color);
        color: white;
        border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-header h4 {
        margin: 0;
    }

    .chat-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
    }

    .chat-messages {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .message {
        max-width: 80%;
        padding: 0.8rem 1rem;
        border-radius: var(--border-radius);
    }

    .message.bot {
        align-self: flex-start;
        background: var(--light-color);
    }

    .message.user {
        align-self: flex-end;
        background: var(--primary-color);
        color: white;
    }

    .chat-input {
        padding: 1rem;
        border-top: 1px solid var(--gray-light);
        display: flex;
        gap: 0.5rem;
    }

    .chat-input input {
        flex: 1;
        padding: 0.8rem;
        border: 1px solid var(--gray-light);
        border-radius: var(--border-radius);
    }

    .send-btn {
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: var(--border-radius);
        padding: 0.8rem 1rem;
        cursor: pointer;
        transition: var(--transition);
    }

    .send-btn:hover {
        background: var(--primary-dark);
    }

    @media (max-width: 480px) {
        .chat-window {
            width: 300px;
            height: 350px;
        }
        
        .chat-widget {
            bottom: 10px;
            right: 10px;
        }
    }
    </style>

    <script>
    // Chat widget functionality
    document.addEventListener('DOMContentLoaded', function() {
        const chatToggle = document.querySelector('.chat-toggle');
        const chatWindow = document.querySelector('.chat-window');
        const chatClose = document.querySelector('.chat-close');
        const sendBtn = document.querySelector('.send-btn');
        const chatInput = document.querySelector('.chat-input input');
        const chatMessages = document.querySelector('.chat-messages');

        if (chatToggle) {
            chatToggle.addEventListener('click', function() {
                chatWindow.classList.toggle('active');
            });
        }

        if (chatClose) {
            chatClose.addEventListener('click', function() {
                chatWindow.classList.remove('active');
            });
        }

        function sendMessage() {
            const message = chatInput.value.trim();
            if (message) {
                // Add user message
                const userMessage = document.createElement('div');
                userMessage.className = 'message user';
                userMessage.innerHTML = `<p>${message}</p>`;
                chatMessages.appendChild(userMessage);

                // Clear input
                chatInput.value = '';

                // Scroll to bottom
                chatMessages.scrollTop = chatMessages.scrollHeight;

                // Simulate bot response
                setTimeout(() => {
                    const botMessage = document.createElement('div');
                    botMessage.className = 'message bot';
                    botMessage.innerHTML = '<p>Merci pour votre message. Notre équipe vous répondra dans les plus brefs délais.</p>';
                    chatMessages.appendChild(botMessage);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 1000);
            }
        }

        if (sendBtn) {
            sendBtn.addEventListener('click', sendMessage);
        }

        if (chatInput) {
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        }
    });
    </script>
</body>
</html>