document.addEventListener('alpine:init', () => {
    Alpine.data('order', () => ({
        popup: false,

        show() {
            this.popup = true;
       },

    }))
})