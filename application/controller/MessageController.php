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
                'messages' => MessageModel::getMessages(Session::get('user_id')),
                'unreadCount' => MessageModel::getUnreadCount(Session::get('user_id')),
                'users' => UserModel::getPublicProfilesOfAllUsers(),
                'chats' => MessageModel::getUsersUserMessaged(Session::get('user_id'))
            )
        );
    }

    public function sendMessage()
    {
        MessageModel::sendMessage(
            Session::get('user_id'),
            Request::post('receiver_id'),
            Request::post('message')
        );

        Redirect::to('message');
    }

    public function markAsRead()
    {
        MessageModel::markAsRead(Request::post('message_id'));
        Redirect::to('message');
    }
}