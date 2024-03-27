<style>
    .chat-history {
        display: flex;
        flex-direction: column;
        padding: 10px;
        border: 1px solid #ddd;
        margin-bottom: 10px;
    }

    .chat-history .message {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .chat-history .message .text {
        flex-grow: 1;
    }

    .chat-history .message .timestamp {
        color: #888;
        font-size: 0.8em;
        margin-left: 10px;
    }

    .chat-history .message.you {
        justify-content: flex-end;
    }
</style>

<div class="container">
    <div class="chat-history">
        <?php foreach ($data['messages'] as $message): ?>
            <div class="message <?= $message->sender_id == $data['current_user_id'] ? 'you' : '' ?>">
                <div class="text">
                    <?= $message->text ?>
                </div>
                <div class="timestamp">
                    <?= date('F j, Y, g:i a', strtotime($message->timestamp)) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <form action="<?= Config::get('URL') ?>message/send/<?= $data['chat_id'] ?>" method="post">
        <input type="text" name="text" required>
        <input type="submit" value="Send">
    </form>
</div>