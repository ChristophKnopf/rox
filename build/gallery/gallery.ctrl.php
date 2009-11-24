<?php
/**
 * Gallery controller
 *
 * @package gallery
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class GalleryController extends RoxControllerBase {
    private $_model;
    private $_view;
    
    public function __construct() {
        parent::__construct();
        $this->_model = new GalleryModel();
        $this->_view  = new GalleryView($this->_model);
        $this->loggedInMember = $this->_model->getLoggedInMember();
        $this->username = $this->loggedInMember ? $this->loggedInMember->Username : false;
    }
    
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    public function index() 
    {
        // that will shrink our code
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $cw = new ViewWrap($this);

        $P->addStyles .= $vw->customStylesLightview();
        
        $Page->currentTab = 'gallery';
        $subTab = 'browse';
        $name = false;

        $loggedInMember = $this->loggedInMember;

        $request = PRequest::get()->request;
        if (!isset($request[1]))
            $request[1] = '';
        switch ($request[1]) {
            case 'ajax':
                if (!isset($request[2]))
                    PPHP::PExit();
                switch ($request[2]) {
                    case 'set':
                        $this->ajaxGallery();
                        break;
                    case 'image':
                        $this->ajaxImage();
                        break;
                }
                break;
                
            case 'thumbimg':
                PRequest::ignoreCurrentRequest();
                if (!isset($_GET['id']))
                    PPHP::PExit();
                $this->_view->thumbImg((int)$_GET['id']);
                break;
                
            case 'img':
                PRequest::ignoreCurrentRequest();
                if (!isset($_GET['id']))
                    PPHP::PExit();
                $this->_view->realImg((int)$_GET['id']);
                break;
                
            case 'upload':
                return new GalleryUploadPage();
            
            case 'uploaded':
                if (!$loggedInMember)
                    return false;
                $this->uploadedProcess();
                break;

            case 'uploaded_done':
                $galleryId = (isset($_GET['id'])) ? $_GET['id'] : false;
                $this->ajaxlatestimages($galleryId);
                PPHP::PExit();

            case 'xppubwiz':
                $this->_view->xpPubWiz();
                break;
                
            case 'flickr':
                return new GalleryAvatarsPage();

            case 'avatars': 
                if ($loggedInMember) {
                    $page = new GalleryAvatarsPage();
                    $userId = $loggedInMember->get_userid();
                    $page->statement = $this->_model->getLatestItems($userId);
                    return $page;
                } else $this->redirect('gallery');

            case 'create':
                if (!$loggedInMember)
                    return false;
                if (isset($request[2])) {
                    $vars['gallery'] = $this->_model->updateGalleryProcess();
                }
                $insertId = isset($vars['gallery']) ? $vars['gallery'] : mysql_insert_id();
                $loc_rel = 'gallery/show/sets/'.$insertId;
                header('Location: ' . PVars::getObj('env')->baseuri . $loc_rel);
                PVars::getObj('page')->output_done = true;
                break;              
                
            case 'manage':
                if (!$loggedInMember)
                    return false;
                $page = new GalleryManagePage();
                $page->loggedInMember = $loggedInMember;
                return $page;

            case 'show':
            default:
                if (!isset($request[2]))
                    $request[2] = '';
                ob_start();
                switch ($request[2]) {
                    case 'image':
                        if (!isset($request[3])) {
                            $this->redirect('gallery');
                        }
                        if (!$image = $this->_model->imageData($request[3])) {
                            return new GalleryImageNotFoundPage();
                        }
                        switch (isset($request[4]) ? $request[4] : '') {
                            case 'delete':
                                if ($deleted = $this->_model->deleteOneProcess($image)) {
                                    $this->message = 'Gallery_ImageDeleted';
                                    return $this->useroverview($loggedInMember->get_userId());
                                } else {
                                    $this->message = 'Gallery_ImageNotDeleted';
                                    return $this->image($image->id);
                                }
                            case 'edit':
                                $this->_model->editProcess($image);
                            case 'comment':
                                // $this->_model->commentProcess($image);
                            default:
                                return $this->image($image);
                        }
                        break;
                        
                    case 'galleries':
                    case 'sets':
                        if (!isset($request[3]) || !$gallery = $this->_model->getGallery($request[3])) {
                            return $this->allgalleries();
                        }
                        if (isset($request[4])) {
                            switch ($request[4]) {
                                case 'delete':
                                    $status = (isset($request[5]) && $request[5] == 'true') ? true : false;
                                    return $this->deleteGallery($gallery,$status);
                                case 'edit':
                                    if (isset($request[5]) && $request[5] == 'images') {
                                        // update/remove the pictures that belong to a gallery
                                        $result = $this->_model->updateGalleryProcess();
                                    } else {
                                        // edit the gallery information
                                        $this->_model->editGalleryProcess();
                                    }
                                    break;
                                case 'remove':
                                    $this->_model->editGalleryProcess();
                                    break;
                                case 'details':
                                    return $this->gallerydetails($gallery);
                                default:
                            }                      
                        } 
                        return $this->gallery($gallery,(isset($request[4]) && $request[4] == 'upload'));
                                                
                    case 'user':
                        $subTab = 'user';
                        $membersmodel = new MembersModel();
                        if (isset($request[3]) && preg_match(User::HANDLE_PREGEXP, $request[3]) && ($member = $membersmodel->getMemberWithUsername($request[3])) && $userId = $member->get_userid()) {
                            $this->username = $member->Username;
                            $this->userId = $userId;
                            if (isset($request[4]) && (substr($request[4], 0, 5) != '=page')) {
                                switch ($request[4]) {
                                    // case 'sets':
                                        // $this->_model->updateGalleryProcess();
                                        // break;
                                    case 'galleries':
                                    case 'sets':
                                        return $this->usergalleries($userId);
                                    case 'pictures':
                                    case 'images':
                                    default:
                                        return $this->userimages($userId);
                                }
                            }
                            return $this->useroverview($userId);
                            break;
                        }
                        
                    default:
                        return $this->overview();
                        break;
                }
        }
        $P->teaserBar .= $vw->teaser($name);
        // submenu
        $P->subMenu .= $vw->showsubmenu($subTab);
    }

    /**
     * handles showing gallery overview page
     *
     * @access public
     * @return object $page
     */
    public function overview()
    {
        $page = new GalleryOverviewPage();
        if ($this->loggedInMember) {
            $page->images = $this->_model->getLatestItems($this->loggedInMember->get_userId());
            $page->galleries = $this->_model->getUserGalleries($this->loggedInMember->Username);
            $page->cnt_pictures = $page->images->numRows();
        } else {
            $page->galleries = $this->_model->getUserGalleries();                            
        }
        $page->loggedInMember = $this->_model->getLoggedInMember();
        $page->statement = $this->_model->getLatestItems();
        return $page;
    }
    
    /**
     * handles showing a page for a single gallery
     *
     * @param Gallery $gallery - gallery to work on
     *
     * @access public
     * @return object $page
     */
    public function gallery(Gallery $gallery,$upload = false)
    {
        $page = new GallerySetPage(); // TODO: Deal with the PageNames. We could easily name this GalleryPage but this reminds of the name of the app itself. How to proceed with this?
        
        $user_id_foreign = $gallery->user_id_foreign;
        $page->myself = ($this->loggedInMember && $this->loggedInMember->get_userId() == $user_id_foreign) ? $this->loggedInMember->Username : false;
        $page->username = MOD_member::getUserHandle($user_id_foreign);
        $page->gallery = $gallery;
        $page->statement = $this->_model->getLatestItems('',$gallery->id);
        $page->cnt_pictures = $page->statement ? $page->statement->numRows() : 0;
        $page->upload = ($upload or !$page->cnt_pictures) ? true : false;        
        $page->member = $this->_model->getMemberWithUserId($gallery->user_id_foreign);
        $page->d = $this->_model->getLatestGalleryItem($gallery->id);
        $page->num_rows = $this->_model->getGalleryItems($gallery->id,1);
        return $page;
    }
    
    /**
     * handles the deletion of a gallery
     *
     * @param Gallery $gallery - gallery to delete
     *
     * @access public
     * @return object $page
     */
    public function deleteGallery(Gallery $gallery, $status)
    {
        $page = new GalleryDeletePage();
        $page->member = $this->_model->getMemberWithUserId($gallery->user_id_foreign);
        $page->loggedInMember = $this->loggedInMember;
        $user_id_foreign = $gallery->user_id_foreign;
        $page->myself = ($this->loggedInMember && ($this->loggedInMember->get_userId() == $user_id_foreign)) ? $this->loggedInMember->Username : false;
        $page->gallery = $gallery;
        if ($status) $page->deleted = $this->_model->deleteGalleryProcess($gallery->id);
        return $page;
    }
    
    /**
     * handles showing a page for a single gallery
     *
     * @access public
     * @return object $page
     */
    public function gallerydetails(Gallery $gallery)
    {
        $page = new GallerySetDetailsPage();
        
        //Check if current TB-user-id and Gallery-user-id are the same
        $loggedInMember = $this->loggedInMember;
        $user_id_foreign = $gallery->user_id_foreign;
        $myself = ($loggedInMember && ($loggedInMember->get_userId() == $user_id_foreign)) ? $loggedInMember->Username : false;
        $page->gallery = $gallery;
        $page->statement = $this->_model->getLatestItems('',$gallery->id);
        $page->cnt_pictures = $page->statement ? $page->statement->numRows() : 0;
        $page->upload = ((isset($request[4]) && $request[4] == 'upload') or !$page->cnt_pictures) ? true : false;
        $page->myself = $myself;
        $page->member = $this->_model->getMemberWithUserId($gallery->user_id_foreign);
        $page->d = $this->_model->getLatestGalleryItem($gallery->id);
        $page->num_rows = $this->_model->getGalleryItems($gallery->id,1);
        return $page;
    }
    
    
    /**
     * handles showing all galleries available
     *
     * @access public
     * @return object $page
     */
    public function allgalleries()
    {
        $page = new GalleryAllGalleriesPage();
        $page->username = $this->username;
        $page->galleries = $this->_model->getUserGalleries();
        $page->loggedInMember = $this->loggedInMember;
        return $page;
    }
    
    /**
     * handles showing an overview of images and galleries of a user
     *
     * @access public
     * @return object $page
     */
    public function useroverview($userId)
    {
        $words = $this->getWords();
        $page = new GalleryUserOverviewPage();
        $page->username = $this->username;
        $page->infoMessage = $words->get($this->message);
        $page->galleries = $this->_model->getUserGalleries($userId);
        $page->statement = $this->_model->getLatestItems($userId);
        $page->cnt_pictures = $page->statement ? $page->statement->numRows() : 0;
        $page->loggedInMember = $this->loggedInMember;
        return $page;        
    }
    
    /**
     * handles showing all galleries of a user
     *
     * @access public
     * @return object $page
     */
    public function usergalleries($userId)
    {
        $page = new GalleryUserGalleriesPage();
        $page->username = $this->username;
        $page->galleries = $this->_model->getUserGalleries($userId);
        $page->statement = $this->_model->getLatestItems($userId);
        $page->cnt_pictures = $page->statement? $page->statement->numRows() : 0;
        $page->model = $this->_model;
        
        // $P->content .= $vw->allGalleries($galleries);
        // $P->content .= $vw->userControls($request[3], 'galleries');
        // $P->content .= $vw->userOverviewSimple($statement, $request[3], '');
        $page->loggedInMember = $this->loggedInMember;
        return $page;
    }
    
    /**
     * handles showing all images of a user
     *
     * @access public
     * @return object $page
     */
    public function userimages($userId)
    {
        $page = new GalleryUserImagesPage();
        $page->username = $this->username;
        $page->galleries = $this->_model->getUserGalleries($userId);
        $page->statement = $this->_model->getLatestItems($userId);
        $page->cnt_pictures = $page->statement ? $page->statement->numRows() : 0;
        $page->loggedInMember = $this->loggedInMember;
        return $page;        
    }
    
    /**
     * handles showing a page for a single image
     *
     * @access public
     * @return object $page
     */
    public function image($image)
    {
        $page = new GalleryImagePage();
        $page->image = $image;
        $page->infoMessage = $this->message;
        $page->previous = $this->_model->getPreviousItems($image->id,$limit=1,$image->user_id_foreign);
        $page->next = $this->_model->getNextItems($image->id,$limit=1,$image->user_id_foreign);
        return $page;
    }
    
    public function uploadedProcess($args, $action, $mem_redirect, $mem_resend)
    {
        // Process the uploaded pictures, display errors
        $userId = $this->_model->getLoggedInMember()->id;
        $vars = $args->post;
        $this->_model->uploadProcess($vars);
        die();
    }
    
    /**
     * handles showing all images of a user
     *
     * @access public
     * @return string
     */
    public function ajaxlatestimages($galleryId = false)
    {
        $loggedInMember = $this->loggedInMember;
        if ($galleryId) $statement = $this->_model->getLatestItems(false,$galleryId);
        else $statement = $this->_model->getLatestItems($loggedInMember->get_userId());
        require_once 'templates/overview.php';
    }

    private function ajaxImage() {
        // Modifying a PHOTOSET(GALLERY) using an ajax-request
        PRequest::ignoreCurrentRequest();
        if (!$member = $this->loggedInMember)
            return false;
    	// Modifying an IMAGE using an ajax-request
        if( isset($_GET['item']) ) {
            $id = $_GET['item'];
            if ($member->get_userId() == $this->_model->imageOwner($id)) {
                if( isset($_GET['title']) ) {
                    $str = htmlentities($_GET['title'], ENT_QUOTES, "UTF-8");
                    if (!empty($str)) {
                    $this->_model->ajaxModImage($id,$str,'');
                    $str2 = utf8_decode(addslashes(preg_replace("/\r|\n/s", "",nl2br($str))));
                    echo $str2;
                    } else echo 'Can`t be empty! Click to edit!';
                }
                if( isset($_GET['text']) ) {
                    $str = htmlentities($_GET['text'], ENT_QUOTES, "UTF-8");
                    $this->_model->ajaxModImage($id,'',$str);
                    $str = utf8_decode(addslashes(preg_replace("/\r|\n/s", "",nl2br($str))));
                    echo $str;
                }
            PPHP::PExit();
            }
        }
        echo 'Error!';
        PPHP::PExit();
    }
    
    // NEW FUNCTIONS
    
    private function ajaxGallery() {
        // Modifying a PHOTOSET(GALLERY) using an ajax-request
        PRequest::ignoreCurrentRequest();
        if (!$member = $this->loggedInMember)
            return false;
        if (isset($_GET['item']) ) {
            $id = $_GET['item'];
            if ($member->get_userId() == $this->_model->galleryOwner($id)) {
                if( isset($_GET['title']) ) {
                    $str = htmlentities($_GET['title'], ENT_QUOTES, "UTF-8");
                    if (!empty($str)) {
                    $this->_model->ajaxModGallery($id,$str,'');
                    $str2 = utf8_decode(addslashes(preg_replace("/\r|\n/s", "",nl2br($str))));
                    echo $str2;
                    } else echo 'Can`t be empty! Click to edit!';
                } elseif( isset($_GET['text']) ) {
                    $str = htmlentities($_GET['text'], ENT_QUOTES, "UTF-8");
                    if (empty($str)) {
                    $str = ' ';
                    }
                    $this->_model->ajaxModGallery($id,'',$str);
                    echo $str;
                }
            PPHP::PExit();
            }
        }
        echo 'Error!';
        PPHP::PExit();
    }
    
    /**
     * createGalleryCallback
     *
     * @param Object $args
     * @param Object $action 
     * @param Object $mem_redirect memory for the page after redirect
     * @param Object $mem_resend memory for resending the form
     * @return string relative request for redirect
     */
    public function createGalleryCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $vars = $args->post;
        $request = $args->request;
        //$errors = $this->model->checkCreateGalleryForm($vars);
        // Not a lot to check at this point:

        $errors = array();
        $desc = (isset($vars['g-description'])) ? $vars['g-description'] : false;
        if (!isset($vars['g-title']) || $vars['g-title'] == "") $errors[] = 'ErrorGalleryNoTitleSet';

        if (count($errors) > 0) {
            // show form again
            $vars['errors'] = $errors;
            $mem_redirect->post = $vars;
            return false;
        }

        if (!$galleryId = $this->_model->createGallery($vars['g-title'], $desc)) return false;
        return 'gallery/show/sets/'.$galleryId;
    }


    // callback processes moved from the model

    /**
     * xxx
     *
     * @access public
     */
    public function editProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling())
        {
            if (!$this->_model->getLoggedInMember())
            {
                return false;
            }

            $vars = &PPostHandler::getVars($callbackId);
            return $this->_model->editProcess($vars);
        }
        else
        {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    /**
     * xxx
     *
     * @access public
     */
    public function editGalleryProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling())
        {
            if (!$this->_model->getLoggedInMember())
            {
                return false;
            }

            $vars = &PPostHandler::getVars($callbackId);
            return $this->_model->editGalleryProcess($vars);
        }
        else
        {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    public function updateGalleryProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling())
        {
            $vars =& PPostHandler::getVars($callbackId);
            return $this->_model->updateGalleryProcess($vars);
        }
        else
        {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    public function commentProcess($image = false)
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling()) {
            if (!$this->_model->getLoggedInMember())
            {
                return false;
            }
            $vars =& PPostHandler::getVars();
            return $this->_model->commentProcess($vars, $image);
        }
        else
        {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    public function uploadProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        $vars = &PPostHandler::getVars($callbackId);
        if (PPostHandler::isHandling())
        {
            $this->_model->uploadProcess($vars);
        }
        else
        {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }
}
