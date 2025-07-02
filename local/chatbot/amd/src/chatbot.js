define(['jquery', 'core/templates'], function($, Templates) {
    function init(opts) {
        var userid = opts.userid;
        var username = opts.username;
        var coursename = opts.coursename;
        var iconUrl = M.util.image_url('logo', 'local_chatbot');

        Templates.render('local_chatbot/chatbox', {iconurl: iconUrl}).done(function(html) {
            $('body').append(html);
            Templates.runTemplateJS(html);
            $('#chatbot-context').text(username + ' (' + userid + ') - ' + coursename);
            setupEvents();
        });

        function setupEvents() {
            var $icon = $('#chatbot-icon');
            var $window = $('#chatbot-window');
            $icon.on('click', function() {
                $window.toggleClass('hidden');
            });

            $('#chatbot-send').on('click', sendMessage);
            $('#chatbot-text').on('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }

        function sendMessage() {
            var text = $('#chatbot-text').val().trim();
            if (!text) {return;}
            $('#chatbot-text').val('');
            addMessage('user', text);
            showThinking(function() {
                addMessage('bot', 'Hi there');
                console.log('Sent to backend', {text: text, userid: userid, coursename: coursename});
            });
        }

        function addMessage(sender, text) {
            var sendername = sender === 'bot' ? 'sid' : username;
            Templates.render('local_chatbot/message', {sender: sender, text: text, sendername: sendername}).done(function(html) {
                $('#chatbot-messages').append(html);
                Templates.runTemplateJS(html);
                var container = $('#chatbot-messages')[0];
                container.scrollTop = container.scrollHeight;
            });
        }

        function showThinking(callback) {
            var steps = [
                'Understanding your question...',
                'Formulating a response...',
                'Referencing course modules...'
            ];
            var $thinking = $('#chatbot-thinking');
            var index = 0;
            function next() {
                if (index < steps.length) {
                    $thinking.text(steps[index]);
                    index++;
                    setTimeout(next, 1000);
                } else {
                    $thinking.text('');
                    callback();
                }
            }
            next();
        }
    }

    return {init: init};
});
