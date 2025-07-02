define(['jquery'], function($) {
    function init(opts) {
        var userid = opts.userid;
        var coursename = opts.coursename;

        var icon = $('<div id="chatbot-icon"></div>');
        var chatwin = $('<div id="chatbot-window" style="display:none"><div id="chatbot-messages"></div><div id="chatbot-input"><input type="text" id="chatbot-text"/><button id="chatbot-send">Send</button></div></div>');
        $('body').append(icon).append(chatwin);

        icon.css({position:'fixed',bottom:'20px',right:'20px',cursor:'pointer',background:'#0073e6',color:'#fff',width:'40px',height:'40px','border-radius':'20px','text-align':'center','line-height':'40px','font-size':'20px','background-image':'url(https://siddhantaknowledge.org/wp-content/uploads/2024/05/logo_skf-circle.webp)','background-size':'cover','background-position':'center'});
        chatwin.css({position:'fixed',bottom:'70px',right:'20px',width:'300px',height:'300px',background:'#fff',border:'1px solid #ccc','border-radius':'5px','display':'flex','flex-direction':'column'});
        $('#chatbot-messages').css({flex:'1',padding:'10px','overflow-y':'auto','display':'flex','flex-direction':'column'});
        $('#chatbot-input').css({display:'flex'});
        $('#chatbot-input input').css({flex:'1'});

        icon.on('click', function(){
            chatwin.toggle();
            if (chatwin.is(':visible')) {
                $('.context-info').remove();
                displayContext();
            }
        });

        $('#chatbot-send').on('click', function(){
            var text = $('#chatbot-text').val();
            if (!text) {return;}
            addMessage('user', text);
            $('#chatbot-text').val('');
            addThinking();
            setTimeout(function(){
                removeThinking();
                addMessage('bot', 'Hi there');
                console.log('Sent to backend', {text:text, userid:userid, coursename:coursename});
            }, 2000);
        });

        function addMessage(sender, text){
            var label;
            if (sender === 'user') {
                label = 'User ' + userid;
            } else if (sender === 'bot') {
                label = 'Raghu';
            } else {
                label = sender;
            }
            var msg = $('<div class="msg msg-' + sender + '"><div class="msg-label"></div><div class="msg-text"></div></div>');
            msg.find('.msg-label').text(label);
            msg.find('.msg-text').text(text);
            msg.css({'max-width':'80%','margin':'5px','padding':'8px','border-radius':'10px','display':'inline-block'});
            if(sender === 'user') {
                msg.css({'align-self':'flex-end','background':'#dcf8c6'});
            } else {
                msg.css({'align-self':'flex-start','background':'#f1f0f0'});
            }
            msg.find('.msg-label').css({'font-size':'0.75em','color':'#555'});
            $('#chatbot-messages').append(msg);
            $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);
        }

        function displayContext(){
            var context = 'userid=' + userid + ', coursename=' + coursename;
            var msg = $('<div class="context-info"></div>').text('Context: ' + context);
            $('#chatbot-messages').append(msg);
            $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);
        }

        function addThinking(){
            var msg = $('<div id="chatbot-thinking">Agent is thinking...</div>');
            $('#chatbot-messages').append(msg);
            $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);
        }

        function removeThinking(){
            $('#chatbot-thinking').remove();
        }
    }

    return {init:init};
});
