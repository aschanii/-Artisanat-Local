// Gestion avancée du panier
class CartManager {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('cart')) || [];
        this.init();
    }

    init() {
        this.updateCartDisplay();
        this.attachEventListeners();
        this.setupCartAutoSave();
    }

    attachEventListeners() {
        // Délégation d'événements pour les boutons d'ajout au panier
        document.addEventListener('click', (e) => {
            if (e.target.closest('.add-to-cart-btn')) {
                const button = e.target.closest('.add-to-cart-btn');
                const productId = button.dataset.productId;
                const quantity = button.dataset.quantity || 1;
                this.addToCart(productId, parseInt(quantity));
            }

            if (e.target.closest('.remove-from-cart')) {
                const button = e.target.closest('.remove-from-cart');
                const productId = button.dataset.productId;
                this.removeFromCart(productId);
            }
        });

        // Événements pour la modification des quantités
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('cart-quantity')) {
                const input = e.target;
                const productId = input.dataset.productId;
                const quantity = parseInt(input.value);
                
                if (quantity > 0) {
                    this.updateQuantity(productId, quantity);
                } else {
                    this.removeFromCart(productId);
                }
            }
        });

        // Événements pour les boutons d'incrémentation
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('quantity-increase')) {
                const button = e.target;
                const input = button.previousElementSibling;
                const productId = input.dataset.productId;
                const newQuantity = parseInt(input.value) + 1;
                input.value = newQuantity;
                this.updateQuantity(productId, newQuantity);
            }

            if (e.target.classList.contains('quantity-decrease')) {
                const button = e.target;
                const input = button.nextElementSibling;
                const productId = input.dataset.productId;
                const newQuantity = parseInt(input.value) - 1;
                
                if (newQuantity > 0) {
                    input.value = newQuantity;
                    this.updateQuantity(productId, newQuantity);
                } else {
                    this.removeFromCart(productId);
                }
            }
        });
    }

    async addToCart(productId, quantity = 1) {
        try {
            // Récupérer les informations du produit
            const product = await this.getProductDetails(productId);
            
            if (!product) {
                this.showNotification('Produit non trouvé', 'error');
                return;
            }

            // Vérifier le stock
            if (product.stock < quantity) {
                this.showNotification('Stock insuffisant', 'error');
                return;
            }

            const existingItem = this.cart.find(item => item.id == productId);
            
            if (existingItem) {
                const newQuantity = existingItem.quantity + quantity;
                
                if (newQuantity > product.stock) {
                    this.showNotification('Quantité demandée supérieure au stock disponible', 'error');
                    return;
                }
                
                existingItem.quantity = newQuantity;
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    artisan_name: product.artisan_name,
                    stock: product.stock,
                    quantity: quantity
                });
            }

            this.saveCart();
            this.updateCartDisplay();
            this.showNotification('Produit ajouté au panier', 'success');
            
            // Animation d'ajout au panier
            this.animateAddToCart(productId);

        } catch (error) {
            console.error('Erreur lors de l\'ajout au panier:', error);
            this.showNotification('Erreur lors de l\'ajout au panier', 'error');
        }
    }

    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id != productId);
        this.saveCart();
        this.updateCartDisplay();
        this.showNotification('Produit retiré du panier', 'success');
    }

    updateQuantity(productId, quantity) {
        const item = this.cart.find(item => item.id == productId);
        if (item) {
            if (quantity > item.stock) {
                this.showNotification('Stock insuffisant', 'error');
                return;
            }
            item.quantity = quantity;
            this.saveCart();
            this.updateCartDisplay();
        }
    }

    clearCart() {
        this.cart = [];
        this.saveCart();
        this.updateCartDisplay();
        this.showNotification('Panier vidé', 'success');
    }

    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.cart));
    }

    updateCartDisplay() {
        this.updateCartCount();
        this.updateCartTotal();
        this.updateCartItems();
    }

    updateCartCount() {
        const cartCountElements = document.querySelectorAll('.cart-count, #cart-count');
        const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
        
        cartCountElements.forEach(element => {
            element.textContent = totalItems;
            element.style.display = totalItems > 0 ? 'flex' : 'none';
        });
    }

    updateCartTotal() {
        const totalElements = document.querySelectorAll('.cart-total, #cart-total');
        const total = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        totalElements.forEach(element => {
            element.textContent = this.formatPrice(total);
        });
    }

    updateCartItems() {
        const cartContainer = document.getElementById('cart-items');
        if (!cartContainer) return;

        if (this.cart.length === 0) {
            cartContainer.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Votre panier est vide</p>
                </div>
            `;
            return;
        }

        cartContainer.innerHTML = this.cart.map(item => `
            <div class="cart-item" data-product-id="${item.id}">
                <img src="${item.image || 'images/placeholder.jpg'}" alt="${item.name}" class="item-image">
                <div class="item-details">
                    <h4>${item.name}</h4>
                    <p class="artisan">Par ${item.artisan_name}</p>
                    <div class="item-price">${this.formatPrice(item.price)}</div>
                </div>
                <div class="quantity-controls">
                    <button class="quantity-btn quantity-decrease" data-product-id="${item.id}">-</button>
                    <input type="number" class="cart-quantity" data-product-id="${item.id}" 
                           value="${item.quantity}" min="1" max="${item.stock}">
                    <button class="quantity-btn quantity-increase" data-product-id="${item.id}">+</button>
                </div>
                <div class="item-total">${this.formatPrice(item.price * item.quantity)}</div>
                <button class="remove-btn remove-from-cart" data-product-id="${item.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `).join('');
    }

    async getProductDetails(productId) {
        try {
            const response = await fetch(`api/products.php?id=${productId}`);
            if (!response.ok) throw new Error('Produit non trouvé');
            return await response.json();
        } catch (error) {
            console.error('Erreur récupération produit:', error);
            return null;
        }
    }

    formatPrice(price) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(price);
    }

    showNotification(message, type = 'info') {
        // Créer une notification toast
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">&times;</button>
        `;

        // Styles de la notification
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '1rem 1.5rem',
            background: this.getNotificationColor(type),
            color: 'white',
            borderRadius: 'var(--border-radius)',
            boxShadow: 'var(--shadow-lg)',
            zIndex: '10000',
            display: 'flex',
            alignItems: 'center',
            gap: '0.5rem',
            animation: 'slideInRight 0.3s ease'
        });

        document.body.appendChild(notification);

        // Fermeture automatique
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);

        // Fermeture manuelle
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    getNotificationColor(type) {
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        return colors[type] || '#17a2b8';
    }

    animateAddToCart(productId) {
        // Animation d'ajout au panier (fly to cart)
        const button = document.querySelector(`[data-product-id="${productId}"] .add-to-cart-btn`);
        if (!button) return;

        const buttonRect = button.getBoundingClientRect();
        const cartIcon = document.querySelector('.cart-btn');

        if (cartIcon) {
            const cartRect = cartIcon.getBoundingClientRect();

            const animationElement = document.createElement('div');
            animationElement.className = 'cart-animation';
            animationElement.innerHTML = '🛒';
            
            Object.assign(animationElement.style, {
                position: 'fixed',
                left: `${buttonRect.left}px`,
                top: `${buttonRect.top}px`,
                fontSize: '1.5rem',
                zIndex: '10000',
                pointerEvents: 'none'
            });

            document.body.appendChild(animationElement);

            // Animation
            const animation = animationElement.animate([
                {
                    transform: 'translate(0, 0) scale(1)',
                    opacity: 1
                },
                {
                    transform: `translate(${cartRect.left - buttonRect.left}px, ${cartRect.top - buttonRect.top}px) scale(0.5)`,
                    opacity: 0
                }
            ], {
                duration: 800,
                easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)'
            });

            animation.onfinish = () => {
                animationElement.remove();
            };
        }
    }

    setupCartAutoSave() {
        // Sauvegarde automatique du panier avant déchargement de la page
        window.addEventListener('beforeunload', () => {
            this.saveCart();
        });
    }

    getCartSummary() {
        const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = subtotal > 50 ? 0 : 4.90;
        const total = subtotal + shipping;

        return {
            totalItems,
            subtotal,
            shipping,
            total,
            items: this.cart
        };
    }
}

// Initialisation du gestionnaire de panier
document.addEventListener('DOMContentLoaded', function() {
    window.cartManager = new CartManager();
});

// Styles CSS pour les animations
const cartStyles = `
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.cart-animation {
    animation-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.notification {
    transition: all 0.3s ease;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cart-quantity {
    width: 50px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 0.25rem;
}

.remove-btn {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.5rem;
    cursor: pointer;
}

.empty-cart {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

.empty-cart i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
`;

// Injection des styles
const styleSheet = document.createElement('style');
styleSheet.textContent = cartStyles;
document.head.appendChild(styleSheet);