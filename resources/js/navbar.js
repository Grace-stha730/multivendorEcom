document.addEventListener('alpine:init',()=>{
    Alpine.data('navbar', () => ({
        open: true,

        toggle(){
            this.open = !this.open;
        },
    }))
})