<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Contains request handling class
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: requesthandler.lib.php 186 2006-12-11 13:37:47Z david $
 */
/**
 * Request handling class
 * 
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */
class PRequest {
    private static $_instance;
    
    private $_cliArgs;
    private $_request;
    
    private function __construct() {
        if (isset($_SERVER['argc']) && isset($_SERVER['argv']) && is_array($_SERVER['argv']) && $_SERVER['argc'] != 0) {
            $args = $_SERVER['argv'];
            unset($args[0]);
            parse_str(implode('&', $args), $args);
            $this->_cliArgs = $args;
        } else {
            $request = self::parseRequest();
            PVars::register('request', $request);
            $this->_request = $request;
            
            if (isset($_SESSION['thisRequest'])) {
                $_SESSION['lastRequest'] = $_SESSION['thisRequest'];
            }
            $_SESSION['thisRequest'] = $request;
        }
    }
    
    public static function get() {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
    
    public function __get($name) {
        $name = '_'.$name;
        if (!isset($this->$name))
            return false;
        return $this->$name;
    }
    
    public function __set($name, $value) {
        $name = '_'.$name;
        if (!isset($this->$name))
            return false;
        $this->$name = $value;
    }
    
    public static function back() {
        $loc = self::backURL();
        header('Location: '.$loc);
        PPHP::PExit();
    }
    
    public static function backURL() {
        $env = PVars::getObj('env');
        $loc = $env->baseuri;
        if (isset($_SESSION['lastRequest']) && serialize($_SESSION['thisRequest']) != serialize($_SESSION['lastRequest'])) {
            $loc.=implode('/', $_SESSION['lastRequest']);
        }
        return $loc;        
    }
    
    public static function home() {
        $base = PVars::getObj('env')->baseuri;
        header('Location: '.$base);
        PPHP::PExit();
    }
    
    public static function ignoreCurrentRequest() {
        $_SESSION['thisRequest'] = $_SESSION['lastRequest'];
    }
    
    public function isCli() {
        return (isset($this->_cliArgs) && is_array($this->_cliArgs));
    }
    
    /**
     * Trying to parse all possible request types
     */
    public static function parseRequest() {
        $c = PVars::getObj('config_request');
        if (!$c)
            throw new PException('Config error!');
        $req = parse_url($_SERVER['REQUEST_URI']);
        if (isset($req['query'])) unset($req['query']);
        if (isset($req['fragment'])) unset($req['fragment']);
        $req = PFunctions::glueParsedUrl($req);
        $d = $_SERVER['SCRIPT_NAME'];
        $d = dirname($d);
        $p = strpos($req, $d);
        if ($p !== false)
            $req = substr($req, $p + strlen($d));
        if ($c->prefix) {
            $p = strpos($req, $c->prefix);
            if ($p !== false) {
                $req = substr($req, $p+strlen($c->prefix));
            }
        }
        if (substr($req, 0, 1) == '/') {
            $req = substr($req, 1);
        }
        $req = explode('/', $req);
        $newReq = array();
        foreach ($req as $r) {
        	$r = rawurldecode($r);
        	
        	// Ignore words with too low charactercodes (control characters etc)
        	$len = strlen($r);
        	for ($i = 0; $i < $len; $i++) {
        		$c = ord($r{$i});
        		if ($c < 38 && $c != 32) { // 32=Space
        			continue 2;
        		}
        	}
        	
            if (trim($r) != '')
            	$newReq[] = $r;
            
        }
        return $newReq;
    }
    
}
?>