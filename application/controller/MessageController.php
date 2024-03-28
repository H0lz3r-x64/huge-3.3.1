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
                'chats' => MessageModel::getUsersUserMessaged(Session::get('user_id'))
            )
        );
    }

    public function chat($receiver_id)
    {
        $this->View->render(
            'message/chat',
            array(
                'messages' => MessageModel::getMessagesWithUser($receiver_id),
                'user' => UserModel::getPublicProfileOfUser($receiver_id)
            )
        );
    }

    public function sendMessage()
    {
        try {
            $message = Request::post('message');

            if (empty($message)) {
                throw new Exception('Message cannot be empty.');
            }

            MessageModel::sendMessage(
                Session::get('user_id'),
                Request::post('receiver_id'),
                $message
            );

            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
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
}