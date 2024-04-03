<?php
class MessageController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class. The parent::__construct thing is necessary to
     * put checkAuthentication in here to make an entire controller only usable for logged-in users (for sure not
     * needed in the LoginController).
     */
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        $this->View->render(
            'message/index',
            array(
                'unreadCount' => MessageModel::getUnreadCount(Session::get('user_id')),
                'users' => UserModel::getPublicProfilesOfAllUsers(),
                'chats' => MessageModel::getUsersUserMessaged(Session::get('user_id')),
                'groups' => MessageModel::getGroupsByUserId(Session::get('user_id'))
            )
        );
    }

    public function chat($receiver_id)
    {
        $isGroup = false;
        $this->View->render(
            'message/chat',
            array(
                'messages' => MessageModel::getMessagesByUser($receiver_id),
                'user' => UserModel::getPublicProfileOfUser($receiver_id),
                'isGroup' => $isGroup
            )
        );
    }

    public function groupchat($group_id)
    {
        $isGroup = true;
        $this->View->render(
            'message/chat',
            array(
                'messages' => MessageModel::getGroupMessages($group_id),
                'group' => MessageModel::getGroupById($group_id),
                'members' => MessageModel::getGroupMembers($group_id),
                'isGroup' => $isGroup
            )
        );
    }

    public function sendMessage()
    {
        try {
            $message = Request::post('message');
            $sender_id = Session::get('user_id');
            $receiver_id = Request::post('receiver_id');

            if (empty($message)) {
                throw new Exception('Message cannot be empty.');
            }

            $msgId = MessageModel::sendMessage(
                $sender_id,
                $receiver_id,
                $message
            );

            $new_message = MessageModel::getMessageById($msgId);

            echo json_encode(['status' => 'success']);


            // Trigger a Pusher event
            $options = array(
                'cluster' => 'eu',
                'useTLS' => true
            );
            $pusher = new Pusher\Pusher(
                '6f54e32cbe2ebd14f7d6',
                '7820e477bc4efdabc29f',
                '1778841',
                $options
            );

            $data['message'] = $new_message;
            // 2 channels; one for the receiver to receive the message and one for the sender to receive their own message
            $chat_channels = ['chat_sender' . $sender_id . 'receiver' . $receiver_id, 'message', 'chat_sender' . $receiver_id . 'receiver' . $sender_id, 'message'];
            $pusher->trigger($chat_channels, 'message', $data);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'errorMessage' => $e->getMessage()]);
            return;
        }
    }

    public function sendMessageToGroup()
    {
        try {
            $message = Request::post('message');
            $sender_id = Session::get('user_id');
            $group_id = Request::post('receiver_id');

            if (empty($message)) {
                throw new Exception('Message cannot be empty.');
            }

            $msgId = MessageModel::sendMessageToGroup($sender_id, $group_id, $message);
            $new_message = MessageModel::getMessageById($msgId);

            echo json_encode(['status' => 'success']);

            // Trigger a Pusher event
            $options = array(
                'cluster' => 'eu',
                'useTLS' => true
            );
            $pusher = new Pusher\Pusher(
                '6f54e32cbe2ebd14f7d6',
                '7820e477bc4efdabc29f',
                '1778841',
                $options
            );

            $data['message'] = $new_message;
            // 1 channel; one for the group to receive the message
            $chat_channel = 'group_chat_' . $group_id;
            $pusher->trigger($chat_channel, 'message', $data);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'errorMessage' => $e->getMessage()]);
            return;
        }
    }

    public function markAsRead()
    {
        MessageModel::markAsRead(
            Session::get('user_id'),
            Request::post('receiver_id')
        );

        echo json_encode(['status' => 'success']);
    }

    public function markGroupMessagesAsRead()
    {
        $user_id = Session::get('user_id');
        $group_id = Request::post('group_id');

        MessageModel::markGroupMessagesAsRead($user_id, $group_id);

        echo json_encode(['status' => 'success']);
    }

    public function getNewMessages()
    {
        // Get the receiver ID from the POST data
        $receiverId = $_POST['receiver_id'];

        // Fetch the new messages from the database
        $new_messages = MessageModel::getNewMessages($receiverId);

        // Return the messages as a JSON string
        echo json_encode(['status' => 'success', 'messages' => $new_messages]);
    }
}