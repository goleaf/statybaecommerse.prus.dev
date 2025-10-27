// Alpine.js helper that synchronises the shopping cart badge with broadcast events.
(function registerCartButtonComponent() {
    /**
     * Create a reactive cart badge component that listens for the global cart-updated event.
     * @param {object|number} options - Either a numeric quantity or an object with a quantity property.
     * @returns {object}
     */
    function createCartButtonComponent(options) {
        const initialQuantity = typeof options === 'object' && options !== null
            ? options.quantity
            : options;

        return {
            qty: Number.isFinite(initialQuantity) ? Number(initialQuantity) : 0,
            /**
             * Register the event listener when Alpine initialises the component.
             */
            init() {
                this.__updateQuantity = (event) => {
                    const nextQuantity = event?.detail?.quantity;
                    if (Number.isFinite(nextQuantity)) {
                        this.qty = Number(nextQuantity);
                    }
                };

                window.addEventListener('cart-updated', this.__updateQuantity);
            },
            /**
             * Detach the event listener if the element is removed from the DOM.
             */
            destroy() {
                if (this.__updateQuantity) {
                    window.removeEventListener('cart-updated', this.__updateQuantity);
                }
            },
        };
    }

    if (typeof window !== 'undefined' && !window.createCartButtonComponent) {
        window.createCartButtonComponent = createCartButtonComponent;
    }
})();
