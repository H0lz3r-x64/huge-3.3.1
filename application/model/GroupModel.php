<?php
class GroupModel
{
    public function createGroup($name)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("INSERT INTO groups (name) VALUES (?)");
        $stmt->execute([$name]);
    }

    public function addUserToGroup($userId, $groupId)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
        $stmt->execute([$groupId, $userId]);
    }

    public function sendMessageToGroup($senderId, $groupId, $message)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT user_id FROM group_members WHERE group_id = ?");
        $stmt->execute([$groupId]);
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($userIds as $userId) {
            $this->sendMessage($senderId, $userId, $message);
        }
    }

    private function sendMessage($senderId, $receiverId, $message)
    {
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message, timestamp, read) VALUES (?, ?, ?, NOW(), 0)");
        $stmt->execute([$senderId, $receiverId, $message]);
    }
}