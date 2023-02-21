var fetchSettings = Vue.component('fetchSettings',{
    template: '#app',
    data() {
      return{
        title: 'fetchSettings',
        settings: {
            'active': '',
            'max_attempts': '',
            'max_queue_record_age': ''
        },
        isLoading: false,
        errorMessage: '',
        successMessage: '',
        submitted: false,
        submitting: false
      }
    },
    mounted() {
        this.fetchSettings();
    },
    methods: {
        fetchSettings() {
            var that = this;
            this.isLoading = true;
            jQuery.post(ASYNCHRONOUS_EMAILS_PARAMS.url, {
                nonce: ASYNCHRONOUS_EMAILS_PARAMS.nonce, 
                action: 'asynchronous_emails_ajax_get_settings', 
            }, function(data){
                that.isLoading = false;
                if (data.success) {
                    if (Object.keys(data.data.data).length > 0) {
                        that.settings = data.data.data;
                    }
                } else {
                    if(data.data.missing_nonce){
                        if(data.data.message){
                            that.errorMessage = data.data.message;
                        }else{
                            that.errorMessage = 'Something went wrong!';
                        }
                    }
                }
            },'JSON');
        },
         updateSettings() {
            var that = this;
            this.errorMessage = '';
            this.successMessage = '';
            this.submitting = true;

            jQuery.post(ASYNCHRONOUS_EMAILS_PARAMS.url, {
                nonce: ASYNCHRONOUS_EMAILS_PARAMS.nonce, 
                action: 'asynchronous_emails_ajax_update_settings', 
                settings: that.settings
            },  function(data){
                that.submitting = false;
                if (data.success == true) {
                    that.successMessage = data.data.message;
                    that.settings = data.data.data;
                    that.submitted = true;
                }else{
                    if(data.data.message){
                        that.errorMessage = data.data.message;
                    }else{
                        that.errorMessage = 'Something went wrong!';
                    }
                }
            }, 'JSON');
        }
    }
});

const routes = [
    {
        name: 'fetchSettings', 
        path: '/', 
        component: fetchSettings
    }
]

const router = new VueRouter({
    routes
});

var app = new Vue({
    el: '#settings',
    router
})