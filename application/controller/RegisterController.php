<?php

/**
 * RegisterController
 * Register new user
 */
class RegisterController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class. The parent::__construct thing is necessary to
     * put checkAuthentication in here to make an entire controller only usable for logged-in users (for sure not
     * needed in the RegisterController).
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Register page
     * Show the register form, but redirect to main-page if user is already logged-in
     */
    /**
     * Renders the registration page if the user is an admin.
     * If the user is not an admin, it checks if the user is logged in.
     * If the user is logged in, it redirects to the home page.
     * If the user is not logged in, it redirects to the login page.
     *
     * @return void
     */
    public function index()
    {
        if (Session::get("user_account_type") == 7) {
            $this->View->render('register/index');
        } else {
            if (LoginModel::isUserLoggedIn()) {
                Redirect::home();
            } else {
                Redirect::to('login/index');
            }
        }
    }

    /**
     * Register page action
     * POST-request after form submit
     */
    public function register_action()
    {
        if (Session::get("user_account_type") == 7) {
            $registration_successful = RegistrationModel::registerNewUser();

            if ($registration_successful) {
                Redirect::to('login/index');
            } else {
                Redirect::to('register/index');
            }
        }
    }

    /**
     * Verify user after activation mail link opened
     * @param int $user_id user's id
     * @param string $user_activation_verification_code user's verification token
     */
    public function verify($user_id, $user_activation_verification_code)
    {
        if (isset($user_id) && isset($user_activation_verification_code)) {
            RegistrationModel::verifyNewUser($user_id, $user_activation_verification_code);
            $this->View->render('register/verify');
        } else {
            Redirect::to('login/index');
        }
    }

    /**
     * Generate a captcha, write the characters into $_SESSION['captcha'] and returns a real image which will be used
     * like this: <img src="......./login/showCaptcha" />
     * IMPORTANT: As this action is called via <img ...> AFTER the real application has finished executing (!), the
     * SESSION["captcha"] has no content when the application is loaded. The SESSION["captcha"] gets filled at the
     * moment the end-user requests the <img .. >
     * Maybe refactor this sometime.
     */
    // public function showCaptcha()
    // {
    //     CaptchaModel::generateAndShowCaptcha();
    // }
}
