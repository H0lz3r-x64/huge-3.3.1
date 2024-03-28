<div class="container">
    <!-- Info bar -->
    <div class="chat-card">

        <img src="<?= $data['user']->user_avatar_link ?>" alt="user avatar">
        <div class="info">
            <div class="name">
                <?= $data['user']->user_name ?>
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
            <input type="hidden" name="receiver_id" id="receiver_id" value="<?= $data['user']->user_id ?>">
            <textarea name="message-text" id="message-text" placeholder="Type a message..." required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            // sends an AJAX request to the markAsRead method when the chat view is loaded.
            $.ajax({
                url: '<?= Config::get('URL') ?>message/markAsRead',
                type: 'post',
                data: {
                    receiver_id: '<?= $data['user']->user_id ?>'
                }
            });

            // scroll the history down on page load and after new message got sent
            $('.message-container').scrollTop($('.message-container')[0].scrollHeight);

            function appendMessage(message) {
                var messageClass = message.sender_id == '<?= Session::get('user_id') ?>' ? 'sent' : 'received';
                var messageHtml = '<div class="message ' + messageClass + '">' +
                    '<div class="message-content">' + message.message + '</div>' +
                    '<div class="message-timestamp">' + message.timestamp + '</div>' +
                    '</div>';
                $('.message-container').append(messageHtml);
                // scroll the history down on page load and after new message got sent
                $('.message-container').scrollTop($('.message-container')[0].scrollHeight);
            }

            var pusher = new Pusher('6f54e32cbe2ebd14f7d6', {
                cluster: 'eu'
            });

            var channel = pusher.subscribe('chat_sender<?= Session::get('user_id') ?>receiver<?= $data['user']->user_id ?>');
            channel.bind('message', function (data) {
                // Fetch new messages
                var newMessage = data.message;
                appendMessage(newMessage);
            });


            // when the chat history is hovered, focus on it to enable scrolling with the mouse wheel
            $('.chat-history').hover(function () {
                $(this).focus();
            });

            $('#message-form').on('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    url: '<?= Config::get('URL') ?>message/sendMessage',
                    type: 'post',
                    data: {
                        receiver_id: $('#receiver_id').val(),
                        message: $('#message-text').val()
                    },
                    success: function (response) {
                        // Clear the message input
                        $('#message-text').val('');
                    },
                    error: function (xhr, status, error) {
                        console.log('An error occurred: ' + error);
                        alert('An error occurred: ' + error);
                    }
                });
            });


        });
    </script>

    <style>
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