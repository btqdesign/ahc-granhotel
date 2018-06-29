var j = jQuery.noConflict();
var app = new Vue({
  el: '#app',
  data: {
    id:null,
    chatbot_token: '',
    position_chatbot:'right',
    option_select:'',
    message:'',
    message_validate_token_error: 'false',
    message_update_token_success: 'false',
    message_update_token_error: 'false',
    message_alert: 'false',
    alertDanger:false,
    alertSuccess:false,
  },
  beforeMount: function () {
    this.checkExistChatbotToken();
  },
  computed: {

  },
  methods: {
    close_message_alert:function()
    {
      this.message_alert = 'false';
    },
    validateChatbotTokenField:function(chatbot_token)
    {
      var regular_expression_company_id  = /[0-9A-Fa-f]{24}/g; //validate field hexadecimal 24 caracteres
      var regular_expression_website_id  = /[0-9A-Fa-f]{24}/g; //validate field hexadecimal 24 caracteres
      var array_chatbot_token = chatbot_token.split("-");

      var company_id = array_chatbot_token[0];
      var website_id = array_chatbot_token[1];

      if (company_id && website_id)
      {
        if( regular_expression_company_id.test(company_id) && regular_expression_website_id.test(website_id) )
        {
          return true;
        }
        else{
          return false;
        }
      } else {
        return false;
      }
    },
    checkExistChatbotToken:function()
    {

      j.ajax({
        type:"POST",
        url: ajaxurl,
        data: {
          action:'exists_chatbot_token'
        },
        success:function(response){
          if (response != '')
          {
            app.option_select = 'true';
            app.chatbot_token = response;
          }else{
            app.option_select = 'false';
          }
        },
        error: function(error){
          console.log(error);
        }
      });
    },
    updateChatbotToken:function()
    {
      if(this.validateChatbotTokenField(app.chatbot_token))
      {
        j.ajax({
          type:"POST",
          url: ajaxurl,
          data: {
            action:'update_chatbot_token',
            chatbot_token:app.chatbot_token,
            position_chatbot:app.position_chatbot
          },
          success:function(response){
            if (response)
            {
              app.message_validate_token_error = 'false';
              app.message_update_token_error   = 'false';

              app.alertSuccess = true;
              app.alertDanger  = false;

              app.message_update_token_success = 'true';

            }else{

              app.message_update_token_success = 'false';
              app.message_validate_token_error = 'false';

              app.alertSuccess = false;
              app.alertDanger  = true;

              app.message_update_token_error   = 'true';
            }
            app.message_alert = 'true';
          },
          error: function(error){
            console.log(error);
          }
        });
      }else{
        app.message_update_token_error   = 'false';
        app.message_update_token_success = 'false';

        app.alertSuccess = false;
        app.alertDanger  = true;

        app.message_validate_token_error = 'true';
        app.message_alert = 'true';
      }

    }
  }
});
