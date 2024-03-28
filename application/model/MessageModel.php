<?php
class MessageModel
{
    public static function sendMessage($senderId, $receiverId, $message): int
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message, timestamp, read_status) VALUES (:sender, :receiver, :message, NOW(), 0)");
        $stmt->execute([':sender' => $senderId, ':receiver' => $receiverId, ':message' => $message]);

        // Return the ID of the new auto-incremented message
        return $db->lastInsertId();
    }

    public static function getMessageById($id)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT DISTINCT * FROM messages WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function getMessagesByUser($userId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();

        if ($userId == Session::get('user_id')) {
            // If chatting with self, only select messages where both sender_id and receiver_id are the user's ID
            $stmt = $db->prepare("SELECT * FROM messages WHERE sender_id = :user AND receiver_id = :user ORDER BY timestamp ASC");
        } else {
            // Otherwise, select messages where either sender_id or receiver_id is the user's ID and the other is the other user's ID
            $stmt = $db->prepare("SELECT * FROM messages WHERE (sender_id = :user AND receiver_id = :other) OR (sender_id = :other AND receiver_id = :user) ORDER BY timestamp ASC");
        }

        $stmt->execute([':user' => Session::get('user_id'), ':other' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUsersUserMessaged($userId)
    {

        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT CASE 
                WHEN sender_id = :user THEN receiver_id ELSE sender_id END AS user_id, MAX(timestamp) as timestamp,
                (SELECT message FROM messages WHERE (sender_id = user_id AND receiver_id = :user) OR (sender_id = :user AND receiver_id = user_id) ORDER BY timestamp DESC LIMIT 1) as last_message,
                (SELECT sender_id FROM messages WHERE (sender_id = user_id AND receiver_id = :user) OR (sender_id = :user AND receiver_id = user_id) ORDER BY timestamp DESC LIMIT 1) as last_sender_id
            FROM messages 
            WHERE sender_id = :user OR receiver_id = :user
            GROUP BY user_id ORDER BY timestamp DESC
        ");
        $stmt->execute([':user' => $userId]);
        $chats = array();

        foreach ($stmt->fetchAll() as $chat) {
            array_walk_recursive($chat, 'Filter::XSSFilter');

            $userdata = UserModel::getPublicProfileOfUser($chat->user_id);
            $chats[$chat->user_id] = new stdClass();
            $chats[$chat->user_id]->user_id = $chat->user_id;
            $chats[$chat->user_id]->user_name = $userdata->user_name;
            $chats[$chat->user_id]->timestamp = $chat->timestamp;
            $chats[$chat->user_id]->last_message = ($chat->last_sender_id == $userId ? "You: " : $chats[$chat->user_id]->user_name . ": ") . $chat->last_message;
            $chats[$chat->user_id]->user_avatar_link = $userdata->user_avatar_link;
        }
        return $chats;
    }

    public static function markAsRead($userId, $senderId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("UPDATE messages SET read_status = 1 WHERE receiver_id = :receiver_id AND sender_id = :sender_id AND read_status = 0");
        $stmt->execute([':receiver_id' => $userId, ':sender_id' => $senderId]);
    }

    public static function getUnreadCount($userId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = :user AND read_status = 0");
        $stmt->execute([':user' => $userId]);
        return $stmt->fetchColumn();
    }

    public static function getNewMessages($receiverId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT * FROM messages WHERE receiver_id = :receiver_id AND read_status = 0");
        $stmt->execute([':receiver_id' => $receiverId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

}