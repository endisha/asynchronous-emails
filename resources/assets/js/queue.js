var queue = Vue.component('list',{
    template: '#app',
    data() {
      return{
          title: 'list',
          statuses: ASYNCHRONOUS_EMAILS_PARAMS.statuses,
          list: [],
          active: true,
          count: -1,
          current: 1,
          pages: 0,
          isLoading: false,
          errorMessage: '',
          successMessage: '',
          filters: {
              created_at: '',
              status: ''
          },
          missingNonce: false,
          deleting: false,
          deletingId: 0,
          sending: false,
          sendingId: 0
      }
    },
    mounted() {
        this.loadList();
        var self = this;
        if(jQuery("html").attr("dir") == 'rtl'){
          (function( factory ) {
            if ( typeof define === "function" && define.amd ) {
                define([ "../jquery.ui.datepicker" ], factory );
            } else {
                factory( jQuery.datepicker );
            }
          }(self.datepicker));
        }
        jQuery('.created_at').datepicker({
          dateFormat : 'yy-mm-dd',
          onSelect:function(selectedDate, datePicker) {            
            self.filters.created_at = selectedDate;
          }
        });
    },
    methods: {
      loadList(clearMessages=true) {
        var that = this;
        this.count = -1;
        this.isLoading = true;
        if(clearMessages){
          this.successMessage = '';
          this.errorMessage = '';
        }
            jQuery.post(ASYNCHRONOUS_EMAILS_PARAMS.url, {
                nonce: ASYNCHRONOUS_EMAILS_PARAMS.nonce, 
                action: 'asynchronous_emails_ajax_list', 
                page: this.current, 
                filters: this.filters
            }, function(data){
                that.isLoading = false;
                that.active = data.data.active;
                if (data.success) {
                    that.list = data.data.records;
                    that.count = data.data.count;
                    that.current = data.data.current;
                    that.pages = data.data.pages;
                } else {
                    that.count = 0;
                    if(data.data.missing_nonce){
                        that.missingNonce = true;
                    }
                    if(data.data.message){
                        that.errorMessage = data.data.message;
                    }else{
                        that.errorMessage = 'Something went wrong!';
                    }
                }
            },'JSON');
        },
        _delete(id){
            var message = ASYNCHRONOUS_EMAILS_PARAMS?.messages?.delete_message?? "Are you sure you want to delete the record?";
            if( ! confirm( message ) ) {
                return;
            }           
            var that = this;
            this.deleting = true;
            this.deletingId = id;
            this.errorMessage = '';
            this.successMessage = '';
            jQuery.post(ASYNCHRONOUS_EMAILS_PARAMS.url, {
                nonce: ASYNCHRONOUS_EMAILS_PARAMS.nonce, 
                action: 'asynchronous_emails_ajax_delete_task_record', 
                id: id
            }, function(data){
                that.deleting = false;
                that.deletingId = 0;
                if (data.success) {
                    that.successMessage = data.data.message;
                    that.reloadPage(that.current);
                } else {
                    if(data.data.missing_nonce){
                        that.missingNonce = true;
                    }
                    if (data.data.message) {
                        that.errorMessage = data.data.message;
                    }else{
                        that.errorMessage = 'Something went wrong!';
                    }
                }
            },'JSON');
        },
        _resned(id){
            var message = ASYNCHRONOUS_EMAILS_PARAMS?.messages?.resend_message?? "Are you sure you want to send the email again?";
            if( ! confirm( message ) ) {
                return;
            }           
            var that = this;
            this.sending = true;
            this.sendingId = id;
            this.errorMessage = '';
            this.successMessage = '';
            jQuery.post(ASYNCHRONOUS_EMAILS_PARAMS.url, {
                nonce: ASYNCHRONOUS_EMAILS_PARAMS.nonce, 
                action: 'asynchronous_emails_ajax_resend_task_record', 
                id: id
            }, function(data){
                that.sending = false;
                that.sendingId = 0;
                if (data.success) {
                    that.successMessage = data.data.message;
                    that.reloadPage(that.current);
                } else {
                    if(data.data.missing_nonce){
                        that.missingNonce = true;
                    }
                    if (data.data.message) {
                        that.errorMessage = data.data.message;
                    }else{
                        that.errorMessage = 'Something went wrong!';
                    }
                }
            },'JSON');
        },
        statusClass(item) {
            return 'label label-' + item.status;
        },
        reloadPage(page) {
            this.current = page;
            this.loadList(false);
        },
        loadPage(page) {
            this.current = page;
            this.loadList();
        },
        filterList() {
            this.current = 1;
            this.loadList();
        },
        filterReset() {
            this.current = 1;
            this.filters = { status: '', created_at: ''};
            this.loadList();
        },
        datepicker( datepicker ) {
            datepicker.regional['ar'] = {
                closeText: 'إغلاق',
                prevText: '&#x3C;السابق',
                nextText: 'التالي&#x3E;',
                currentText: 'اليوم',
                monthNames: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                'يوليو', 'أغسطس', 'سبتمبر',  'أكتوبر', 'نوفمبر', 'ديسمبر'],
                monthNamesShort: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                'يوليو', 'أغسطس', 'سبتمبر',  'أكتوبر', 'نوفمبر', 'ديسمبر'],
                dayNames: ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
                dayNamesShort: ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
                dayNamesMin: ['ح', 'ن', 'ث', 'ر', 'خ', 'ج', 'س'],
                weekHeader: 'أسبوع',
                dateFormat: 'dd/mm/yy',
                firstDay: 6,
                isRTL: true,
                showMonthAfterYear: false,
                yearSuffix: ''
            };
            datepicker.setDefaults(datepicker.regional['ar']);
            return datepicker.regional['ar'];
        }
    }
});

const routes = [
    {
        name: 'list', 
        path: '/', 
        component: queue
    }
]

const router = new VueRouter({
    routes
});

var app = new Vue({
    el: '#queue',
    router
})