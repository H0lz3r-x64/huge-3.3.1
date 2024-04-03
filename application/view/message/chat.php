<div class="container">
    <!-- Info bar -->
    <div class="chat-card">
        <!-- Display group avatar for group chats -->
        <?php if ($data['isGroup'] ? !empty($data['group']->group_avatar_link) : !empty($data['user']->user_avatar_link)): ?>
            <img src="<?= $data['isGroup'] ? $data['group']->group_avatar_link : $data['user']->user_avatar_link ?>"
                alt="user avatar">
        <?php endif; ?>
        <div class="info">
            <div class="name">
                <!-- Display group name for group chats -->
                <?= $data['isGroup'] ? $data['group']->group_name : $data['user']->user_name ?>
            </div>
        </div>
        <!-- Go Back button -->
        <button class="go-back-button" onclick="window.location.href='<?= Config::get('URL') ?>message/index'">&#8592;
            Go Back</button>
    </div>

    <!-- Messages -->
    <div class="chat-history">
        <div class="message-container">
            <?php foreach ($data['messages'] as $message): ?>
                <div class="message <?= $message['sender_id'] == Session::get('user_id') ? 'sent' : 'received' ?>">
                    <?php if ($data['isGroup']): ?>
                        <div class="message-sender">
                            <?= $message['sender_name'] ?>
                        </div>
                    <?php endif; ?>
                    <div class="message-content">
                        <?= htmlentities($message['message']) ?>
                    </div>
                    <div class="message-timestamp">
                        <?= date('F j, Y, g:i a', strtotime($message['timestamp'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="chat-control">
        <form id="message-form" class="message-form">
            <input type="hidden" id="is_group" value="<?= $data['isGroup'] ? 'true' : 'false' ?>">
            <input type="hidden" name="receiver_id" id="receiver_id"
                value="<?= $data['isGroup'] ? $data['group']->group_id : $data['user']->user_id ?>">
            <textarea name="message-text" id="message-text" placeholder="Type a message..." required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>

    <script>
        function markAsRead(receiverId, isGroup) {
            $.ajax({
                url: isGroup ? '<?= Config::get('URL') ?>message/markGroupMessagesAsRead' : '<?= Config::get('URL') ?>message/markAsRead',
                type: 'post',
                data: {
                    receiver_id: receiverId
                }
            });
        }

        function appendMessage(message) {
            var messageTime = new Date(message.timestamp).toLocaleString('en-US', {
                month: 'long', day: 'numeric', year: 'numeric', hour: 'numeric',
                minute: 'numeric', hour12: true, hourCycle: 'h12'
            })
                .replace(' at', ',')
                .replace(' AM', ' am')
                .replace(' PM', ' pm');
            var messageClass = message.sender_id == '<?= Session::get('user_id') ?>' ? 'sent' : 'received';
            var messageHtml = '<div class="message ' + messageClass + '">' +
                <?php if ($data['isGroup']): ?>
                '<div class="message-sender">' + message.sender_name + '</div>' +
                <?php endif; ?>
            '<div class="message-content">' + message.message + '</div>' +
                '<div class="message-timestamp">' + messageTime + '</div>' +
                '</div>';
            $('.message-container').append(messageHtml);
            // scroll the history down on page load and after new message got sent
            $('.message-container').scrollTop($('.message-container')[0].scrollHeight);

            markAsRead('<?= $data['isGroup'] ? $data['group']->group_id : $data['user']->user_id ?>');
        }

        $(document).ready(function () {
            markAsRead('<?= $data['isGroup'] ? $data['group']->group_id : $data['user']->user_id ?>');

            // scroll the history down on page load and after new message got sent
            $('.message-container').scrollTop($('.message-container')[0].scrollHeight);


            var pusher = new Pusher('6f54e32cbe2ebd14f7d6', {
                cluster: 'eu'
            });

            var channel = pusher.subscribe('chat_sender<?= Session::get('user_id') ?>receiver<?= $data['isGroup'] ? $data['group']->group_id : $data['user']->user_id ?>');
            channel.bind('message', function (data) {
                // Fetch new messages
                var newMessage = data.message;

                // Check if the sender of the message is not the current user
                if (newMessage.sender_id != '<?= Session::get('user_id') ?>') {
                    appendMessage(newMessage);
                }
            });

            // when the chat history is hovered, focus on it to enable scrolling with the mouse wheel
            $('.chat-history').hover(function () {
                $(this).focus();
            });

            $('#message-form').on('submit', function (e) {
                e.preventDefault();

                // Get the message text
                var messageText = $('#message-text').val();

                // Clear the message input
                $('#message-text').val('');

                // Check if it's a group chat
                var isGroup = $('#is_group').val() === 'true';

                // Send the message
                $.ajax({
                    url: isGroup ? '<?= Config::get('URL') ?>message/sendMessageToGroup' : '<?= Config::get('URL') ?>message/sendMessage',
                    type: 'post',
                    data: {
                        receiver_id: $('#receiver_id').val(),
                        message: messageText
                    },
                    error: function (xhr, status, error) {
                        console.log('An error occurred: ' + error);
                        alert('An error occurred: ' + error);
                    }
                });


                // Append the message to the chat immediately
                appendMessage({
                    message: messageText,
                    timestamp: new Date(),
                    sender_id: '<?= Session::get('user_id') ?>',
                    sender_name: '<?= Session::get('user_name') ?>'
                });
            });

            $('#message-text').on('keydown', function (e) {
                // Check if the Enter key was pressed
                if (e.key === 'Enter') {
                    // Check if the Shift key was not held down
                    if (!e.shiftKey) {
                        // Prevent the default action (newline)
                        e.preventDefault();

                        // Get the message text
                        var messageText = $('#message-text').val().trim();

                        // Check if messageText is not empty
                        if (messageText) {
                            // Submit the form
                            $('#message-form').submit();
                        } else {
                            // If the message text is empty, report form validity to trigger the built-in tooltip
                            $('#message-form')[0].reportValidity();
                        }
                    }
                }
            });

        });
    </script>

    <style>
        .message-sender {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .go-back-button {
            justify-self: end;
            /* Change this from left to right */
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            background-color: #007bff !important;
            color: white;
            cursor: pointer;
        }

        .go-back-button:hover {
            background-color: #222 !important;
        }

        .chat-history {
            position: relative;
            margin: 0 auto;
            max-height: 70vh;
        }

        .message-container {
            position: relative;
            display: flex;
            flex-direction: column;
            grid-gap: 10px;
            overflow-y: auto;
            max-height: calc(70vh - 40px);
            padding: 20px;

        }

        .message {
            padding: 20px;
        }

        .chat-history::before,
        .chat-history::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            height: 30px;
            /* adjust as needed */
            width: 100%;
            /* cover the entire width */
            pointer-events: none;
            z-index: 1;
            /* ensure the gradient is above the content */
        }

        .chat-history::before {
            top: 0;
            background: linear-gradient(to bottom, white 10px, rgba(255, 255, 255, 0) 30px);
        }

        .chat-history::after {
            bottom: 0px;
            background: linear-gradient(to top, white 10px, rgba(255, 255, 255, 0) 30px);
        }

        .message {
            display: flex;
            flex-direction: column;
            padding: 10px;
            border-radius: 5px;
            max-width: 50%;
            min-width: 160px;
        }

        .message.sent {
            align-self: end;
            background-color: #0084ff;
            color: white;
        }

        .message.received {
            align-self: start;
            background-color: #e5e5ea;
        }

        .message-content {
            word-wrap: break-word;
        }

        .message.received .message-timestamp {
            align-self: end;
            font-size: 0.8em;
            color: #777;
        }

        .message.sent .message-timestamp {
            align-self: end;
            font-size: 0.8em;
            color: #F1F1F4;
        }

        .chat-card {
            display: grid;
            grid-template-columns: 50px auto 89px;
            grid-gap: 10px;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            position: relative;
        }

        .chat-card img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }

        .chat-card .info {
            display: flex;
            justify-content: space-between;
        }

        .chat-card .info .name {
            font-weight: bold;
        }

        .message-form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            padding-bottom: 0px;
            border-top: 1px solid #ddd;
        }

        .message-form textarea {
            flex-grow: 1;
            margin-right: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }

        .message-form button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #0084ff;
            color: white;
            cursor: pointer;
        }

        #message-text {
            height: 40px;
        }
    </style>
</div>