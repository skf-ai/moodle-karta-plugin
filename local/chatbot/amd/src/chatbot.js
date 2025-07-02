define(['jquery'], function($) {
    function init(opts) {
        var userid = opts.userid;
        var coursename = opts.coursename;

        var icon = $('<div id="chatbot-icon">\u263A</div>');
        var chatwin = $('<div id="chatbot-window" style="display:none"><div id="chatbot-messages"></div><div id="chatbot-input"><input type="text" id="chatbot-text"/><button id="chatbot-send">Send</button></div></div>');
        $('body').append(icon).append(chatwin);

        icon.css({position:'fixed',bottom:'20px',right:'20px',cursor:'pointer',background:'#0073e6',color:'#fff',width:'40px',height:'40px','border-radius':'20px','text-align':'center','line-height':'40px','font-size':'20px'});
        chatwin.css({position:'fixed',bottom:'70px',right:'20px',width:'300px',height:'300px',background:'#fff',border:'1px solid #ccc','border-radius':'5px','display':'flex','flex-direction':'column'});
        $('#chatbot-messages').css({flex:'1',padding:'10px','overflow-y':'auto'});
        $('#chatbot-input').css({display:'flex'});
        $('#chatbot-input input').css({flex:'1'});

        icon.on('click', function(){
            chatwin.toggle();
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
            var msg = $("<div class=\"msg msg-" + sender + "\"></div>").text(text);
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
