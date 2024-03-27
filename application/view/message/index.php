<div class="container">
    <h1>Messages</h1>
    <p>You have
        <?= $data['unreadCount'] ?> unread messages.
    </p>

    <form action="<?= Config::get('URL') ?>message/sendMessage" method="post">
        <label for="receiver_id">Recipient:</label>
        <select id="receiver_id" name="receiver_id" style="width: 200px;">
            <?php foreach ($data['users'] as $user): ?>
                <option value="<?= $user->user_id . ',' . $user->user_avatar_link ?>">
                    <?= $user->user_name . ',' . $user->user_type ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="message">Message:</label>
        <textarea id="message" name="message"></textarea>
        <input type="submit" value="Send Message">
    </form>

    <script>
        $(document).ready(function () {
            $('#receiver_id').select2({
                templateResult: formatUser,
                templateSelection: formatUserSelection
            });

            function formatUser(user) {
                if (!user.id) {
                    return user.text;
                }
                // parse user.element.value
                var values = user.element.value.split(',');
                var userId = values[0];
                var avatarLink = values[1];
                // parse user.text
                var values = user.text.split(',');
                var userName = values[0];
                var userType = values[1];
                var $user = $(
                    '<span style="display:flex; align-items: center; "><img src="' + avatarLink + '" class="img - circle" style="height: 44px;"/>⠀<div><b>' + userName + '</b><br>' + userType + '</div></span > '
                );

                return $user;
            };
            function formatUserSelection(user) {
                if (!user.id) {
                    return user.text;
                }
                // parse user.element.value
                var values = user.element.value.split(',');
                var userId = values[0];
                var avatarLink = values[1];
                // parse user.text
                var values = user.text.split(',');
                var userName = values[0];
                var userType = values[1];
                var $user = $(
                    '<span style="display:flex; align-items: center; height: 20px; position: absolute; top: 50%; transform: translateY(-50%);"><img src="' + avatarLink + '" class="img - circle" style="height: 20px;"/>⠀<b>' + userName + '</b></span>'
                );
                return $user;
            }
        });
    </script>

    <h2>Your Messages</h2>
    <!-- <?php foreach ($data['messages'] as $message): ?>
        <div class="message">
            <p><strong>From:</strong>
                <?= $message['sender_id'] ?>
            </p>
            <p><strong>To:</strong>
                <?= $message['receiver_id'] ?>
            </p>
            <p><strong>Message:</strong>
                <?= $message['message'] ?>
            </p>
            <p><strong>Timestamp:</strong>
                <?= $message['timestamp'] ?>
            </p>
            <?php if (!$message['read_status']): ?>
                <form action="<?= Config::get('URL') ?>message/markAsRead" method="post">
                    <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                    <input type="submit" value="Mark as Read">
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?> -->

    <style>
        .chat-card {
            display: grid;
            grid-template-columns: 50px auto;
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

        .chat-card .info .timestamp {
            color: #888;
            font-size: 0.8em;
        }

        .chat-card .last-message {
            color: #666;
            font-size: 0.9em;
        }

        .chat-card .stretched-link {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
        }
    </style>

    <div class="container">
        <?php foreach ($data['chats'] as $chat): ?>
            <div class="chat-card">
                <img src="<?= $chat->user_avatar_link ?>" alt="user avatar">
                <div>
                    <div class="info">
                        <div class="name">
                            <?= $chat->user_name ?>
                        </div>
                        <div class="timestamp">
                            <?= date('F j, Y, g:i a', strtotime($chat->timestamp)) ?>
                        </div>
                    </div>
                    <div class="last-message">
                        <?= $chat->last_message ?>
                    </div>
                    <a href="<?= Config::get('URL') ?>message/chat/<?= $chat->user_id ?>" class="stretched-link"></a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>