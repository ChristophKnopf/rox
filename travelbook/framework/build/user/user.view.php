<?php
/**
 * user view
 *
 * @package user
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: user.view.php 167 2006-10-30 10:53:47Z david $
 */
class UserView extends PAppView 
{
    /**
     * Instance of User model
     * 
     * @var User
     */
    private $_model;
    
    /**
     * @param User $model
     */
    public function __construct(User $model) 
    {
        $this->_model = $model;
    }

    public function avatar($userId)
    {
    	if (!$this->_model->hasAvatar($userId)) {
    		header('Content-type: image/png');
            @copy(HTDOCS_BASE.'images/misc/empty_avatar'.(isset($_GET['xs']) ? '_xs' : '').'.png', 'php://output');
            PPHP::PExit();
    	}
        $file = (int)$userId;
        if (isset($_GET['xs']))
            $file .= '_xs';
        $img = new MOD_images_Image($this->_model->avatarDir->dirName().'/'.$file);
        if (!$img->isImage()) {
            header('Content-type: image/png');
            @copy(HTDOCS_BASE.'images/misc/empty_avatar'.(isset($_GET['xs']) ? '_xs' : '').'.png', 'php://output');
            PPHP::PExit();
        }
        $size = $img->getImageSize();
        header('Content-type: '.image_type_to_mime_type($size[2]));
        $this->_model->avatarDir->readFile($file);
        PPHP::PExit();
    }

    public function friends($friends)
    {
    	require TEMPLATE_DIR.'apps/user/friends.php';
    }

    /**
     * Loading login form template
     * 
     * @param void
     */
    public function loginForm() 
    {
        require TEMPLATE_DIR.'apps/user/loginform.php';
    }

    
    /**
     * Loading register confirm error template
     * 
     * @param void
     */
    public function registerConfirm($error = false) 
    {
        require TEMPLATE_DIR.'apps/user/confirmerror.php';
    }
    
    /**
     * Loading register form template
     * 
     * @param void
     */
    public function registerForm() 
    {
        require TEMPLATE_DIR.'apps/user/registerform.php';
    }

    /**
     * Sends a confirmation e-mail
     * 
     * @param string $userId
     */
    public function registerMail($userId) 
    {
        $User = $this->_model->getUser($userId);
        if (!$User)
            return false;
        $handle = $User->handle;
        $email  = $User->email;
        $key    = APP_User::getSetting($userId, 'regkey');
        if (!$key)
            return false;
        $key = $key->value;
        $confirmUrl = PVars::getObj('env')->baseuri.'user/confirm/'.$handle.'/'.$key;
            
        $registerMailText = array();
        require SCRIPT_BASE.'text/'.PVars::get()->lang.'/apps/user/register.php';
        $from    = $registerMailText['from_name'].' <'.PVars::getObj('config_mailAddresses')->registration.'>';
        $subject = $registerMailText['subject'];
        
        $Mail = new MOD_mail_Multipart;
        $logoCid = $Mail->addAttachment(HTDOCS_BASE.'images/logo.png', 'image/png');
        
        ob_start();
        require TEMPLATE_DIR.'apps/user/mail/register_html.php';
        $mailHTML = ob_get_contents();
        ob_end_clean();
        $mailText = '';
        require TEMPLATE_DIR.'apps/user/mail/register_plain.php';

        $Mail->addMessage($mailText);
        $Mail->addMessage($mailHTML, 'text/html');
        $Mail->buildMessage();

        $Mailer = Mail::factory(PVars::getObj('config_smtp')->backend, PVars::get()->config_smtp);
        if (is_a($Mailer, 'PEAR_Error')) {
            $e = new PException($Mailer->getMessage());
            $e->addMessage($Mailer->getDebugInfo());
            throw $e;
        }
        $rcpts = $email;
        $header = $Mail->header;
        $header['From'] = $from;
        $header['To'] = $email;
        $header['Subject'] = $subject;
        $header['Message-Id'] = '<reg'.$userId.'.'.sha1(uniqid(rand())).'@myTravelbook>';
        $r = @$Mailer->send($rcpts, $header, $Mail->message);
        if (is_object($r) && is_a($r, 'PEAR_Error')) {
            $e = new PException($r->getMessage());
            $e->addInfo($r->getDebugInfo());
            throw $e;
        }
    }

    public function searchResult($res)
    {
    	require TEMPLATE_DIR.'apps/user/searchresult.php';
    }

    public function settingsForm() 
    {
        $User = APP_User::get();
        if ($User) {
            $location = $this->_model->getLocation($User->getId());
        } else {
            $location = false;
        }
    	require TEMPLATE_DIR.'apps/user/settingsform.php';
    }

    public function userPage($userHandle) 
    {
        if (!$userId = APP_User::userId($userHandle))
            return false;
        $userHandle = $this->_model->getRealHandle($userId);
        require TEMPLATE_DIR.'apps/user/userpage.php';
    }


}
?>
