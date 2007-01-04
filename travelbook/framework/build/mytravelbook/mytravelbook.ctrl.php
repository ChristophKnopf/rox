<?php
/**
 * myTravelbook controller
 *
 * @package mytravelbook
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: mytravelbook.ctrl.php 151 2006-07-26 19:18:35Z kang $
 */
class MytravelbookController extends PAppController {
    private $_model;
    private $_view;
    
    public function __construct() {
        parent::__construct();
        $this->_model = new Mytravelbook();
        $this->_view  = new MytravelbookView($this->_model);

        // if a stylesheet is requested (in subdir style), pipe it through
        $request = PRequest::get()->request;
        if (isset($request[0]) && $request[0] == 'styles') {
            $req = implode('/', $request);
            if (isset($_SESSION['lastRequest']))
                PRequest::ignoreCurrentRequest();
            $this->_view->passthroughCSS($req);
        } 

        $this->_model->loadDefaults();
    }
    
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    public function index() {
        $request = PRequest::get()->request;
        if (!isset($request[1]))
            $request[1] = '';
        switch ($request[1]) {
            case 'in':
                if (!isset($request[2]))
                    $request[2] = 'en';
                $_SESSION['lang'] = $request[2];
                PRequest::back();
                break;
            
            default:
                if (!isset($request[0]))
                    $request[0] = '';
                // static pages
                switch($request[0]) {
                    case 'about':
                        ob_start();
                        $this->_view->aboutpage();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;
                        break;
		 
                    case 'help':
                        ob_start();
                        $this->_view->globalhelppage();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;
                        break;

                    default:
                        ob_start();
                        $this->_view->startpage();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;
                        break;                	
                }
                break;
        }
    }
    
    public function buildContent() {
        return true;
    }

    public function topMenu() {
        $this->_view->topMenu();
    }
}
?>
