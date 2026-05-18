document.addEventListener('alpine:init', () => {
    Alpine.data('cart', () => ({
        popup: false,
        checkout: false,

        show() {
            this.popup = true;
       },

       checkoutTrue(){
        this.checkout = true
       },

       checkoutFalse(){
        this.checkout = false
       },
    }))
})