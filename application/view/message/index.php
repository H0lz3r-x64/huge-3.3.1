<div class="container">
    <div class="row">
        <h1>Messages</h1>
        <!-- Add Button -->
        <button id="addButton" style="background-color: green; color: white;">Add</button>
    </div>
    <span>You have
        <span id="AllUnreadCount">
            <?= $data['unreadCount'] ?>
        </span> unread messages.
    </span>

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">

            <form id="openChatForm" method="post">
                <div class="row">
                    Open Chat with
                    <select id="user_search" name="receiver_id" style="width: 100%;">
                        <?php foreach ($data['users'] as $user): ?>
                            <option value="<?= $user->user_id . ',' . $user->user_avatar_link ?>">
                                <?= $user->user_name . ',' . $user->user_type ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="submit" value="Open" style="background-color: green; color: white;">
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            var pusher = new Pusher('6f54e32cbe2ebd14f7d6', {
                cluster: 'eu'
            });


            // foreach data['users'] user subscribe to the channel
            <?php foreach ($data['users'] as $user): ?>
                var channel = pusher.subscribe('chat_sender<?= Session::get('user_id') ?>receiver<?= $user->user_id ?>');
                channel.bind('message', function (data) {
                    let newMessage = data.message;
                    let AllCountElement = $('#AllUnreadCount')
                    let countElement = $('#unreadCount<?= $user->user_id ?>')
                    let lastMessageElement = $('#lastMessage<?= $user->user_id ?>')
                    let timestampElement = $('#timestamp<?= $user->user_id ?>')
                    let receiverName = $('#receiverName<?= $user->user_id ?>');

                    AllCountElement.text(parseInt(AllCountElement.text()) + 1);
                    countElement.text(parseInt(countElement.text()) + 1);
                    // if data.sender equals the php $user->user_id the name should be 'You', otherwise receiverName
                    let name = (newMessage.sender_id == '<?= $user->user_id ?>' ? 'You' : receiverName);
                    lastMessageElement.text(name + ": " + newMessage.message);

                    timestampElement.text(new Date(newMessage.timestamp).toLocaleString('en-US', {
                        month: 'long', day: 'numeric', year: 'numeric', hour: 'numeric',
                        minute: 'numeric', hour12: true, hourCycle: 'h12'
                    })
                        .replace(' at', ',')
                        .replace(' AM', ' am')
                        .replace(' PM', ' pm')
                    );
                });
            <?php endforeach; ?>

            $('#user_search').select2({
                templateResult: formatUser,
                templateSelection: formatUserSelection
            });

            $('#openChatForm').submit(function (event) {
                event.preventDefault(); // Prevent form submission

                var selectedOption = $('#user_search option:selected').val().split(',')[0];
                var redirectUrl = '<?= Config::get('URL') ?>message/chat/' + selectedOption;

                window.location.href = redirectUrl;
            });

            var $modal = $("#myModal");
            var $btn = $("#addButton");
            var $span = $(".close");

            $btn.click(function () {
                $modal.show();
            });

            $span.click(function () {
                $modal.hide();
            });

            $(window).click(function (event) {
                if (event.target == $modal[0]) {
                    $modal.hide();
                }
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

    <h2>Recent</h2>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 15px;
            border: 1px solid #888;
            width: 620px;
            border-radius: 15px;
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            text-wrap: nowrap;
            gap: 10px;
        }

        .chat-card {
            display: grid;
            grid-template-columns: 50px auto;
            grid-gap: 10px;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            position: relative;
            max-height: 70px;
            max-width: 876px;
            overflow: hidden;
        }

        .last-message {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 350px;
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

    <div class="container" style="margin-top: 0px;">
        <?php foreach ($data['chats'] as $chat): ?>
            <div class="chat-card">
                <div style="position: relative;">
                    <img src="<?= $chat->user_avatar_link ?>" alt="user avatar">
                    <div id="unreadCount<?= $chat->user_id ?>" style="position: absolute; top: -5px; right: -5px; background-color: red; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 20px;
                        <?php if (!$chat->unreadCount > 0): ?>
                            visibility: hidden;
                        <?php endif; ?>
                        ">
                        <?= $chat->unreadCount ?>
                    </div>

                </div>
                <div>
                    <div class="info">
                        <div id="receiverName<?= $chat->user_id ?>" class="name">
                            <?= $chat->user_name ?>
                        </div>
                        <div id="timestamp<?= $chat->user_id ?>" class="timestamp">
                            <?= date('F j, Y, g:i a', strtotime($chat->timestamp)) ?>
                        </div>
                    </div>
                    <div id="lastMessage<?= $chat->user_id ?>" class="last-message">
                        <?= $chat->last_message ?>
                    </div>
                    <a href="<?= Config::get('URL') ?>message/chat/<?= $chat->user_id ?>" class="stretched-link"></a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>