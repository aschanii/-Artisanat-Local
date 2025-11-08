// Gestion des paiements
class PaymentManager {
    constructor() {
        this.stripe = null;
        this.elements = null;
        this.cardElement = null;
        this.paymentIntent = null;
        this.init();
    }

    init() {
        this.initializeStripe();
        this.setupEventListeners();
        this.setupPaymentForms();
    }

    initializeStripe() {
        // Initialisation de Stripe (version simulée pour la démo)
        // En production, vous utiliseriez la vraie clé Stripe
        this.stripe = {
            elements: () => ({
                create: (type, options) => ({
                    mount: (selector) => {
                        const element = document.createElement('div');
                        element.className = 'card-element';
                        element.innerHTML = `
                            <div class="card-input">
                                <input type="text" placeholder="Numéro de carte" class="card-number">
                                <div class="card-details">
                                    <input type="text" placeholder="MM/AA" class="card-expiry">
                                    <input type="text" placeholder="CVC" class="card-cvc">
                                </div>
                                <input type="text" placeholder="Nom sur la carte" class="card-name">
                            </div>
                        `;
                        document.querySelector(selector).appendChild(element);
                        return element;
                    },
                    on: (event, handler) => {
                        // Simulation des événements
                        if (event === 'change') {
                            setTimeout(() => handler({complete: true, error: null}), 100);
                        }
                    }
                })
            }),
            confirmCardPayment: (clientSecret, data) => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        resolve({
                            paymentIntent: {
                                status: 'succeeded',
                                id: 'pi_' + Date.now()
                            },
                            error: null
                        });
                    }, 2000);
                });
            }
        };

        this.elements = this.stripe.elements();
        this.setupCardElement();
    }

    setupCardElement() {
        if (document.getElementById('card-element')) {
            this.cardElement = this.elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#424770',
                        '::placeholder': {
                            color: '#aab7c4',
                        },
                    },
                },
            });
            
            this.cardElement.mount('#card-element');
            
            this.cardElement.on('change', (event) => {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });
        }
    }

    setupEventListeners() {
        // Événements pour les méthodes de paiement
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', this.handlePaymentMethodChange.bind(this));
        });

        // Soumission du formulaire de paiement
        const paymentForm = document.getElementById('payment-form');
        if (paymentForm) {
            paymentForm.addEventListener('submit', this.handlePaymentSubmit.bind(this));
        }

        // Validation en temps réel des champs carte
        document.addEventListener('input', this.validateCardFields.bind(this));
    }

    setupPaymentForms() {
        // Initialiser les formulaires de paiement spécifiques
        this.setupCardForm();
        this.setupPayPalForm();
        this.setupTransferForm();
    }

    setupCardForm() {
        const cardForm = document.getElementById('card-payment-form');
        if (cardForm) {
            cardForm.addEventListener('submit', this.handleCardPayment.bind(this));
        }
    }

    setupPayPalForm() {
        const paypalForm = document.getElementById('paypal-payment-form');
        if (paypalForm) {
            paypalForm.addEventListener('submit', this.handlePayPalPayment.bind(this));
        }
    }

    setupTransferForm() {
        const transferForm = document.getElementById('transfer-payment-form');
        if (transferForm) {
            transferForm.addEventListener('submit', this.handleTransferPayment.bind(this));
        }
    }

    handlePaymentMethodChange(event) {
        const method = event.target.value;
        this.showPaymentSection(method);
    }

    showPaymentSection(method) {
        // Masquer toutes les sections de paiement
        document.querySelectorAll('.payment-section').forEach(section => {
            section.style.display = 'none';
        });

        // Afficher la section correspondante
        const activeSection = document.getElementById(`${method}-payment-section`);
        if (activeSection) {
            activeSection.style.display = 'block';
        }

        // Mettre à jour les boutons d'action
        this.updatePaymentButton(method);
    }

    updatePaymentButton(method) {
        const submitButton = document.querySelector('#payment-form button[type="submit"]');
        if (!submitButton) return;

        const buttonTexts = {
            'card': 'Payer par carte',
            'paypal': 'Payer avec PayPal',
            'transfer': 'Confirmer la commande'
        };

        submitButton.innerHTML = `
            <i class="fas fa-lock"></i>
            ${buttonTexts[method] || 'Procéder au paiement'}
        `;
    }

    async handlePaymentSubmit(event) {
        event.preventDefault();
        
        const form = event.target;
        const paymentMethod = form.querySelector('input[name="payment_method"]:checked').value;
        
        switch (paymentMethod) {
            case 'card':
                await this.processCardPayment(form);
                break;
            case 'paypal':
                await this.processPayPalPayment(form);
                break;
            case 'transfer':
                await this.processTransferPayment(form);
                break;
            default:
                this.showError('Méthode de paiement non supportée');
                break;
        }
    }

    async processCardPayment(form) {
        this.showLoading(true);
        
        try {
            // Récupérer les données de la commande
            const orderData = this.getOrderData();
            
            // Créer une intention de paiement
            const paymentIntent = await this.createPaymentIntent(orderData.total);
            
            if (!paymentIntent.success) {
                throw new Error(paymentIntent.error);
            }
            
            // Confirmer le paiement
            const result = await this.confirmCardPayment(
                paymentIntent.payment_intent.client_secret,
                form
            );
            
            if (result.error) {
                throw new Error(result.error.message);
            }
            
            // Paiement réussi
            await this.handlePaymentSuccess(orderData, result.paymentIntent);
            
        } catch (error) {
            this.showError(error.message);
            console.error('Erreur paiement carte:', error);
        } finally {
            this.showLoading(false);
        }
    }

    async processPayPalPayment(form) {
        this.showLoading(true);
        
        try {
            const orderData = this.getOrderData();
            
            // Simuler l'ouverture de PayPal
            const paypalResult = await this.processPayPal(orderData);
            
            if (paypalResult.success) {
                await this.handlePaymentSuccess(orderData, paypalResult);
            } else {
                throw new Error(paypalResult.error || 'Erreur PayPal');
            }
            
        } catch (error) {
            this.showError(error.message);
        } finally {
            this.showLoading(false);
        }
    }

    async processTransferPayment(form) {
        this.showLoading(true);
        
        try {
            const orderData = this.getOrderData();
            
            // Traitement du virement bancaire
            const transferResult = await this.processBankTransfer(orderData);
            
            if (transferResult.success) {
                await this.handlePaymentSuccess(orderData, transferResult);
            } else {
                throw new Error(transferResult.error || 'Erreur virement');
            }
            
        } catch (error) {
            this.showError(error.message);
        } finally {
            this.showLoading(false);
        }
    }

    async createPaymentIntent(amount) {
        const response = await fetch('api/payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'create_intent',
                amount: Math.round(amount * 100), // Convertir en centimes
                currency: 'eur'
            })
        });
        
        return await response.json();
    }

    async confirmCardPayment(clientSecret, form) {
        // Simulation de confirmation de carte
        return new Promise((resolve) => {
            setTimeout(() => {
                // 90% de chance de succès
                const success = Math.random() < 0.9;
                
                if (success) {
                    resolve({
                        paymentIntent: {
                            id: 'pi_' + Date.now(),
                            status: 'succeeded',
                            amount: 1000
                        }
                    });
                } else {
                    resolve({
                        error: {
                            type: 'card_error',
                            message: 'Votre carte a été refusée. Veuillez réessayer.'
                        }
                    });
                }
            }, 2000);
        });
    }

    async processPayPal(orderData) {
        // Simulation du processus PayPal
        return new Promise((resolve) => {
            setTimeout(() => {
                // Ouvrir une fenêtre simulée PayPal
                this.showPayPalWindow().then(result => {
                    if (result.success) {
                        resolve({
                            success: true,
                            transaction_id: 'pp_' + Date.now(),
                            payer_email: result.email
                        });
                    } else {
                        resolve({
                            success: false,
                            error: 'Paiement PayPal annulé'
                        });
                    }
                });
            }, 1000);
        });
    }

    showPayPalWindow() {
        // Simulation d'une fenêtre PayPal
        return new Promise((resolve) => {
            const confirmed = confirm('Simulation PayPal : Cliquez sur OK pour accepter le paiement, Annuler pour refuser.');
            
            if (confirmed) {
                resolve({
                    success: true,
                    email: 'acheteur@example.com'
                });
            } else {
                resolve({
                    success: false
                });
            }
        });
    }

    async processBankTransfer(orderData) {
        // Simulation de traitement virement
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({
                    success: true,
                    transaction_id: 'tr_' + Date.now(),
                    status: 'pending',
                    instructions: `
                        Veuillez effectuer le virement sur :
                        IBAN: FR76 3000 4000 0100 0000 0000 000
                        BIC: BNPAFRPP
                        Montant: ${orderData.total}€
                        Référence: CMD-${orderData.order_number}
                    `
                });
            }, 1500);
        });
    }

    async handlePaymentSuccess(orderData, paymentResult) {
        // Enregistrer la commande
        const orderResult = await this.createOrder(orderData);
        
        if (orderResult.success) {
            // Rediriger vers la page de confirmation
            window.location.href = `order-confirmation.php?order=${orderResult.order_number}`;
        } else {
            throw new Error('Erreur lors de la création de la commande');
        }
    }

    async createOrder(orderData) {
        const response = await fetch('api/orders.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });
        
        return await response.json();
    }

    getOrderData() {
        const form = document.getElementById('checkout-form');
        const formData = new FormData(form);
        
        // Récupérer les articles du panier
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        return {
            customer_name: formData.get('name'),
            customer_email: formData.get('email'),
            customer_phone: formData.get('phone'),
            shipping_address: [
                formData.get('address'),
                formData.get('postal_code'),
                formData.get('city'),
                formData.get('country')
            ].join(', '),
            items: cart.map(item => ({
                product_id: item.id,
                quantity: item.quantity,
                price: item.price
            })),
            subtotal: this.calculateSubtotal(cart),
            shipping: this.calculateShipping(cart),
            total: this.calculateTotal(cart)
        };
    }

    calculateSubtotal(cart) {
        return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    calculateShipping(cart) {
        const subtotal = this.calculateSubtotal(cart);
        return subtotal >= 50 ? 0 : 4.90;
    }

    calculateTotal(cart) {
        return this.calculateSubtotal(cart) + this.calculateShipping(cart);
    }

    validateCardFields(event) {
        if (event.target.classList.contains('card-number')) {
            this.formatCardNumber(event.target);
        } else if (event.target.classList.contains('card-expiry')) {
            this.formatExpiryDate(event.target);
        } else if (event.target.classList.contains('card-cvc')) {
            this.validateCVC(event.target);
        }
    }

    formatCardNumber(input) {
        let value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = '';
        
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formattedValue += ' ';
            }
            formattedValue += value[i];
        }
        
        input.value = formattedValue;
    }

    formatExpiryDate(input) {
        let value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        
        input.value = value;
    }

    validateCVC(input) {
        input.value = input.value.replace(/[^0-9]/gi, '').substring(0, 4);
    }

    showLoading(show) {
        const submitButton = document.querySelector('#payment-form button[type="submit"]');
        const loadingElement = document.getElementById('payment-loading');
        
        if (submitButton) {
            submitButton.disabled = show;
            if (show) {
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
            }
        }
        
        if (loadingElement) {
            loadingElement.style.display = show ? 'block' : 'none';
        }
    }

    showError(message) {
        // Afficher une erreur de paiement
        const errorElement = document.getElementById('payment-errors') || this.createErrorElement();
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        
        setTimeout(() => {
            errorElement.style.display = 'none';
        }, 5000);
    }

    createErrorElement() {
        const errorElement = document.createElement('div');
        errorElement.id = 'payment-errors';
        errorElement.className = 'alert alert-error';
        errorElement.style.marginTop = '1rem';
        
        const paymentForm = document.getElementById('payment-form');
        if (paymentForm) {
            paymentForm.appendChild(errorElement);
        }
        
        return errorElement;
    }

    showSuccess(message) {
        const successElement = document.createElement('div');
        successElement.className = 'alert alert-success';
        successElement.textContent = message;
        successElement.style.marginTop = '1rem';
        
        const paymentForm = document.getElementById('payment-form');
        if (paymentForm) {
            paymentForm.appendChild(successElement);
        }
        
        setTimeout(() => {
            successElement.remove();
        }, 3000);
    }
}

// Initialisation du gestionnaire de paiement
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('payment-form')) {
        window.paymentManager = new PaymentManager();
    }
});

// Styles pour les éléments de paiement
const paymentStyles = `
.card-element {
    border: 1px solid #e1e5e9;
    border-radius: 4px;
    padding: 10px;
    background: white;
}

.card-input input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
    font-size: 16px;
}

.card-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.payment-section {
    display: none;
}

.payment-section.active {
    display: block;
}

#payment-loading {
    text-align: center;
    padding: 20px;
    display: none;
}

.paypal-button {
    background: #FFC439;
    color: #000;
    border: none;
    padding: 15px 30px;
    border-radius: 4px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    width: 100%;
    transition: background 0.3s;
}

.paypal-button:hover {
    background: #F2BA36;
}

.transfer-instructions {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 20px;
    margin-top: 15px;
}

.payment-method {
    margin-bottom: 15px;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-method.selected {
    border-color: #007bff;
    background: #f8f9ff;
}

.payment-method input[type="radio"] {
    margin-right: 10px;
}

.payment-method label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 500;
}

.payment-method .method-icon {
    font-size: 24px;
    margin-right: 10px;
    width: 40px;
    text-align: center;
}
`;

// Injection des styles
const paymentStyleSheet = document.createElement('style');
paymentStyleSheet.textContent = paymentStyles;
document.head.appendChild(paymentStyleSheet);