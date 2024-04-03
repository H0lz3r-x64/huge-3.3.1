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

    public static function sendMessageToGroup($senderId, $groupId, $message)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("INSERT INTO messages (sender_id, group_id, message, timestamp, read_status) VALUES (:sender, :group, :message, NOW(), 0)");
        $stmt->execute([':sender' => $senderId, ':group' => $groupId, ':message' => $message]);

        // Return the ID of the new auto-incremented message
        return $db->lastInsertId();
    }

    public static function markGroupMessagesAsRead($userId, $groupId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("UPDATE messages SET read_status = 1 WHERE group_id = :group_id AND receiver_id = :user_id AND read_status = 0");
        $stmt->execute([':group_id' => $groupId, ':user_id' => $userId]);
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
                (SELECT message FROM messages WHERE ((sender_id = user_id AND receiver_id = :user) OR (sender_id = :user AND receiver_id = user_id)) AND group_id IS NULL ORDER BY timestamp DESC LIMIT 1) as last_message,
                (SELECT sender_id FROM messages WHERE ((sender_id = user_id AND receiver_id = :user) OR (sender_id = :user AND receiver_id = user_id)) AND group_id IS NULL ORDER BY timestamp DESC LIMIT 1) as last_sender_id,
                (SELECT COUNT(*) FROM messages WHERE receiver_id = :user AND sender_id = user_id AND read_status = 0 AND group_id IS NULL) as unreadCount
            FROM messages 
            WHERE (sender_id = :user OR receiver_id = :user) AND (sender_id IS NOT NULL AND receiver_id IS NOT NULL) 
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
            $chats[$chat->user_id]->unreadCount = $chat->unreadCount;
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

    public static function createGroup($name, $firstUser)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("INSERT INTO groups (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        $groupId = $db->lastInsertId();

        $stmt = $db->prepare("INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)");
        $stmt->execute([':group_id' => $groupId, ':user_id' => $firstUser]);

        return $groupId;
    }

    public static function getGroups()
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT * FROM groups");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getGroupById($groupId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT 
            g.id as group_id, 
            g.name as group_name,
            (SELECT MAX(timestamp) FROM messages WHERE group_id = g.id) as timestamp,
            (SELECT message FROM messages WHERE group_id = g.id ORDER BY timestamp DESC LIMIT 1) as last_message,
            (SELECT sender_id FROM messages WHERE group_id = g.id ORDER BY timestamp DESC LIMIT 1) as last_sender_id,
            (SELECT COUNT(*) FROM messages WHERE group_id = g.id AND read_status = 0 AND receiver_id = :user_id) as unreadCount
        FROM groups g 
        WHERE g.id = :group_id
        ");

        $stmt->execute([':user_id' => Session::get('user_id'), ':group_id' => $groupId]);
        $group = $stmt->fetch();

        if ($group) {
            array_walk_recursive($group, 'Filter::XSSFilter');

            $groupData = new stdClass();
            $groupData->group_id = $group->group_id;
            $groupData->group_name = $group->group_name;
            $groupData->timestamp = $group->timestamp;
            $groupData->last_message = ($group->last_sender_id == Session::get('user_id') ? "You: " : $groupData->group_name . ": ") . $group->last_message;
            $groupData->group_avatar_link = null; // not implemented yet
            $groupData->unreadCount = $group->unreadCount;

            return $groupData;
        } else {
            return null;
        }
    }

    public static function getGroupsByUserId($userId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT 
            g.id as group_id, 
            g.name as group_name,
            (SELECT MAX(timestamp) FROM messages WHERE group_id = g.id) as timestamp,
            (SELECT message FROM messages WHERE group_id = g.id ORDER BY timestamp DESC LIMIT 1) as last_message,
            (SELECT sender_id FROM messages WHERE group_id = g.id ORDER BY timestamp DESC LIMIT 1) as last_sender_id,
            (SELECT COUNT(*) FROM messages WHERE group_id = g.id AND read_status = 0 AND receiver_id = :user_id) as unreadCount
        FROM groups g 
        JOIN group_members gm ON g.id = gm.group_id 
        WHERE gm.user_id = :user_id
        GROUP BY group_id 
        ORDER BY timestamp DESC
    ");
        $stmt->execute([':user_id' => $userId]);
        $groups = array();

        foreach ($stmt->fetchAll() as $group) {
            array_walk_recursive($group, 'Filter::XSSFilter');

            $groups[$group->group_id] = new stdClass();
            $groups[$group->group_id]->group_id = $group->group_id;
            $groups[$group->group_id]->group_name = $group->group_name;
            $groups[$group->group_id]->timestamp = $group->timestamp;
            $groups[$group->group_id]->last_message = ($group->last_sender_id == $userId ? "You: " : $groups[$group->group_id]->group_name . ": ") . $group->last_message;
            $groups[$group->group_id]->unreadCount = $group->unreadCount;
            $groups[$group->group_id]->group_avatar_link = null; // not implemented yet
        }
        return $groups;
    }

    public static function getGroupMembers($groupId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT u.* FROM users u JOIN group_members gm ON u.user_id = gm.user_id WHERE gm.group_id = :group_id");
        $stmt->execute([':group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addGroupMember($groupId, $userId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)");
        $stmt->execute([':group_id' => $groupId, ':user_id' => $userId]);
    }

    public static function removeGroupMember($groupId, $userId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("DELETE FROM group_members WHERE group_id = :group_id AND user_id = :user_id");
        $stmt->execute([':group_id' => $groupId, ':user_id' => $userId]);
    }

    // delete group and everything related to it
    public static function deleteGroup($groupId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("DELETE FROM groups WHERE id = :id");
        $stmt->execute([':id' => $groupId]);

        $stmt = $db->prepare("DELETE FROM group_members WHERE group_id = :group_id");
        $stmt->execute([':group_id' => $groupId]);

        $stmt = $db->prepare("DELETE FROM messages WHERE group_id = :group_id");
        $stmt->execute([':group_id' => $groupId]);
    }

    public static function getGroupMessages($groupId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT messages.*, users.user_name as sender_name FROM messages 
                              JOIN users ON messages.sender_id = users.user_id 
                              WHERE group_id = :group_id ORDER BY timestamp ASC");
        $stmt->execute([':group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}