document.addEventListener('alpine:init',() =>{
    Alpine.data('product',() => ({
        listView: true,
        createView:false,
        updateView:false,
        popup:false,

        showCreateView(){
            this.listView =false;
            this.createView = true;
            this.updateView = false;
        },

        showListView(){
            this.listView = true;
            this.createView = false;
            this.updateView = false;
        },

        showUpdateView(){
            this.listView = false;
            this.createView = false;
            this.updateView = true;
        }

        // showListView(){
        //     this.listView = true;
        //     this.createView = false;
        // },
    }))
})