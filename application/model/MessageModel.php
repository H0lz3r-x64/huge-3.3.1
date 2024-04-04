<?php
class MessageModel
{
    public static function sendMessage($senderId, $receiverId, $message): int
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL sendMessage(:sender, :receiver, :message)");
        $stmt->execute([':sender' => $senderId, ':receiver' => $receiverId, ':message' => $message]);
        return $db->lastInsertId();
    }

    public static function sendMessageToGroup($senderId, $groupId, $message)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL sendMessageToGroup(:sender, :group, :message)");
        $stmt->execute([':sender' => $senderId, ':group' => $groupId, ':message' => $message]);
        return $db->lastInsertId();
    }

    public static function markGroupMessagesAsRead($userId, $groupId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL markGroupMessagesAsRead(:group_id, :user_id)");
        $stmt->execute([':group_id' => $groupId, ':user_id' => $userId]);
    }

    public static function getMessageById($id)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL getMessageById(:id)");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function getMessagesByUser($userId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL getMessagesByUser(:user_id, :session_user_id)");
        $stmt->execute([':user_id' => $userId, ':session_user_id' => Session::get('user_id')]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUsersUserMessaged($userId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL getUsersUserMessaged(:user)");
        $stmt->execute([':user' => $userId]);
        $return = $stmt->fetchAll(PDO::FETCH_CLASS);
        $stmt->closeCursor();
        $chats = array();

        foreach ($return as $chat) {
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
        $stmt = $db->prepare("CALL markAsRead(:receiver_id, :sender_id)");
        $stmt->execute([':receiver_id' => $userId, ':sender_id' => $senderId]);
    }

    public static function getUnreadCount($userId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL getUnreadCount(:user_id)");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn();
    }

    public static function getNewMessages($receiverId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL getNewMessages(:receiver_id)");
        $stmt->execute([':receiver_id' => $receiverId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function getGroups()
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL getGroups()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getGroupById($groupId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL getGroupById(:group_id, :user_id)");
        $stmt->execute([':group_id' => $groupId, ':user_id' => Session::get('user_id')]);
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
        $stmt = $db->prepare("CALL getGroupsByUserId(:user_id)");
        $stmt->execute([':user_id' => $userId]);
        $return = $stmt->fetchAll(PDO::FETCH_CLASS);
        $stmt->closeCursor();
        $groups = array();

        foreach ($return as $group) {
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
        $stmt = $db->prepare("CALL getGroupMembers(:group_id)");
        $stmt->execute([':group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addGroupMember($groupId, $userId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL addGroupMember(:group_id, :user_id)");
        $stmt->execute([':group_id' => $groupId, ':user_id' => $userId]);
    }

    public static function removeGroupMember($groupId, $userId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL removeGroupMember(:group_id, :user_id)");
        $stmt->execute([':group_id' => $groupId, ':user_id' => $userId]);
    }

    public static function deleteGroup($groupId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL deleteGroup(:group_id)");
        $stmt->execute([':group_id' => $groupId]);
    }

    public static function getGroupMessages($groupId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("CALL getGroupMessages(:group_id)");
        $stmt->execute([':group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}