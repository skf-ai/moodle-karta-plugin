define(['jquery', 'core/templates', 'local_chatbot/markdown'], function($, Templates, Markdown) {
    function init(opts) {
        var userid = opts.userid;
        var username = opts.username;
        var coursename = opts.coursename;
        var credits = opts.credits || 0;
        var iconUrl = M.util.image_url('logo', 'local_chatbot');

        Templates.render('local_chatbot/chatbox', {iconurl: iconUrl}).done(function(html) {
            $('body').append(html);
            Templates.runTemplateJS(html);
            $('#chatbot-context').text(username + ' (' + userid + ') - ' + coursename);
            $('#chatbot-credits').text('Credits remaining: ' + credits);
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
            if (credits <= 0) {
                addMessage('bot', M.str.local_chatbot.outofcredits);
                return;
            }
            $('#chatbot-text').val('');
            addMessage('user', text);
            showThinking(function() {
                $.post(M.cfg.wwwroot + '/local/chatbot/updatecredit.php', {sesskey: M.cfg.sesskey}, function(resp) {
                    if (resp && typeof resp.credits !== 'undefined') {
                        credits = resp.credits;
                        $('#chatbot-credits').text('Credits remaining: ' + credits);
                    }
                    callChatAPI(text, function(err, reply) {
                        if (err) {
                            addMessage('bot', err);
                        } else {
                            addMessage('bot', reply);
                        }
                        console.log('Sent to backend', {text: text, userid: userid, coursename: coursename});
                    });
                }, 'json');
            });
        }

        function callChatAPI(message, callback) {
            $.ajax({
                url: M.cfg.wwwroot + '/local/chatbot/chatapi.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    sesskey: M.cfg.sesskey,
                    message: message
                },
                success: function(data) {
                    if (data && data.reply) {
                        callback(null, data.reply);
                    } else if (data && data.error) {
                        callback(data.error);
                    } else {
                        callback('Invalid response from server');
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseText || xhr.statusText || 'Unknown error';
                    callback('Error: ' + msg);
                }
            });
        }

        function addMessage(sender, text) {
            var sendername = sender === 'bot' ? 'sid' : username;
            if (sender === 'bot') {
                text = Markdown.convert(text);
            } else {
                text = Markdown.escapeHtml(text);
            }
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
