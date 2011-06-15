<?php
require_once(dirname(__FILE__)."/lib/sso/hashencoder.class.php");
require_once(dirname(__FILE__)."/lib/webservice/webservice.class.php");
require_once(dirname(__FILE__)."/lib/webservice/jhsfwebservice.class.php");

// This class is totally independant of the SOAP library used to retrieve data from Hive

class repository_hive extends repository {
	
	private static $resultsPerPage = 10;	
	
	// Constructor	
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
		global $SESSION;
        parent::__construct($repositoryid, $context, $options);
	}
	
	public function check_login() {
		return true;
	}

	// Logout: clear the session and kill the Hive connection
	public function logout(){
		global $SESSION;		
		WebService::logout($this->id);
		unset($SESSION->{'repositoryClient'.$this->id}); // empty the object in the session
    }

    public function get_listing($path='', $page=1) {
        global $OUTPUT;		
        $ret  = array();
		$ret['path'] = array(array('name'=>'Root', 'path'=>''));	
		$ret['list'] = array();	
		$ret['dynload'] = true;		
        $ret['nologin'] = true;
		$ret['norefresh'] = true;
		//$ret['manage'] = $CFG->wwwroot.'/repository/hive/hiveresources.php';
		
		// Initialization of list of bureaus
		if (empty($path)) {
			$result = WebService::getBureauList($this->id);
			
			$bureauList = $result['bureauList']; 
			
			$bureauArray = $bureauList['bureauList']['list']; // several results				
			
			foreach ($bureauArray as $bureauValue) {
				$ret['list'][] = array(
					'title' => $bureauValue['name'],
					'path' => $bureauValue['id'].':'.urlencode($bureauValue['name']),
					'date' => '',
					'size' => 50,
					'thumbnail'=>$OUTPUT->pix_url('f/folder-32').'',
					'children' => array()
				);
			}							
		} else {
			$trail = '';
			$parts = explode('/', $path);
			foreach ($parts as $part) {
				if (!empty($part)) {
					$idname = explode(':', $part);
					$trail .= ('/'.$part);
					$ret['path'][] = array('name'=>urldecode($idname[1]), 'path'=>$trail);

					if (empty($bureauId))
					{
						$bureauId = $idname[0];	// Bureau ID		
					}
					else
					{
						$categoryId = $idname[0];	// Category ID		
					}
				}
			}

			// Retrieve categories and items list
			$ret['list'] = $this->_getCategoryList($path, $bureauId, $categoryId);
		}		

		return $ret;
    }
	
	// Retrieve categories and items list
	private function _getCategoryList($path,$bureauId,$parentCategoryId) {
		global $OUTPUT;
	
		$list = array();

		/* pagination is not working
		$start = 1 + max(0, $page - 1) * repository_hive::$resultsPerPage;
		$end = $start + repository_hive::$resultsPerPage - 1;
		*/	

		$categoryList = WebService::getCategoryDetailsList($this->id,$bureauId,-1,-1,$parentCategoryId);
		
		$categoryArray = $categoryList['categoryDetailsList']['list']; // several results
		
		//$count = 1;
		foreach ($categoryArray as $categoryValue) {
			//if ($count >= $start && $count <= $end) {
				$list[] = array(
					'title' => $categoryValue['name'],
					'path' => $path.'/'.$categoryValue['id'].':'.urlencode($categoryValue['name']),
					'date' => '',
					'size' => 50,
					'thumbnail'=>$OUTPUT->pix_url('f/folder-32').'',
					'children' => array()
				);					
			//}
			//$count = $count + 1;
		}

		// Retrieve items list
		/* pagination is not working
		//if ($categoryList['totalNumberOfCategories'] < $end && $bureauId != $parentCategoryId) {
		$start = $start - $categoryList['totalNumberOfCategories'];
		$end = $end - $categoryList['totalNumberOfCategories'];
		if ($start <= 0)
		{
			$start = 1;
		}

		$itemList = WebService::getItemDetailsList($this->repositoryClient,$bureauId,$parentCategoryId,$start,$end - $start + 1);
		*/

		$itemList = WebService::getItemDetailsList($this->id,$bureauId,$parentCategoryId,-1,-1);

		$itemArray = $itemList['itemDetailsList']['list']; // several results
					
		foreach ($itemArray as $itemValue) {					
			if ($itemValue['urlType'] == 'true')
			{ $filename = 'url.html'; }
			else
			{ $filename = $itemValue['filename']; }

			$list[] = array(
				'shorttitle'=>$itemValue['title'],
				'title'=>$filename,
				'thumbnail' => $OUTPUT->pix_url(file_extension_icon($filename, 32))->out(false),
				'source'=>$itemValue['bureauId'].'|'.$itemValue['aliasId'].'|'.urlencode($filename)
			);
		}

		return $list;
		//array('error'=>$error,'categoryList'=>$list,'totalNumberOfCategoriesAndItems'=>$categoryList['totalNumberOfCategories']+$itemList['totalNumberOfItems']);
	}
	
    public function get_link($comboId) {
		global $CFG;

		$ids = explode('|', $comboId);

		return $CFG->wwwroot.'/repository/hive/view.php/'.$this->id.'/'.$ids[0].'/'.$ids[1].'/'.$ids[2];
    }
	
	public function search($search_text, $page = 1) {
		global $OUTPUT;

        $ret = array();
		$list = array();
        
        $ret['nologin'] = true;

		if (empty($page)) {
            $page = 1;
        }

		// Pagination not working
		//$searchlist = WebService::standardSearch($this->repositoryClient,'2','2',$search_text,1+($page-1)*repository_hive::$resultsPerPage,repository_hive::$resultsPerPage);
		
		$searchlist = WebService::standardSearch($this->id,$search_text,-1,-1);
		$searchArray = $searchlist['searchResults']['list'];
		
		foreach ($searchArray as $value) {
			$list[] = array(
				'shorttitle'=>$value['title'],
				'title'=>$value['filename'],
				'thumbnail' => $OUTPUT->pix_url(file_extension_icon($value['filename'], 32))->out(false),
				'source'=>$value['bureauId'].'|'.$value['aliasId'].'|'.urlencode($value['filename'])
			);
		}

		// Pagination
		// NOT WORKING
		//$ret['pages'] = ceil($searchlist['totalNumberOfResults']/repository_hive::$resultsPerPage);

		$ret['list'] = $list;
        return $ret;
    }
	
	// Federated search with other repositories or other Hive instances not allowed 
    public function global_search() {		
		return false;
    }
	
    public function get_file($id, $file = '') {
		$ids = explode('|',$id);
		$content = WebService::download($this->id,$ids[1]);

        $path = $this->prepare_file($file);
        $fp = fopen($path, 'w');
		fwrite($fp, $content);
        fclose($fp);

        return array('path'=>$path, 'url'=>'');
	}
	
    /**
     * Enable mulit-instance
     *
     * @return array
     */
    public static function get_instance_option_names() {
        return array('hwsurl', 'ssousername', 'ssosharedsecret', 'hashtype', 'hashencoding', 'ssoguestuser');
    }
	
    /**
     * define a configuration form
     *
     * @return bool
     */
    public function instance_config_form($mform) {
		$strrequired = get_string('required');
        $mform->addElement('text', 'hwsurl', get_string('hwsurl', 'repository_hive'), array('size' => '50'));
        $mform->addElement('static', 'hwsurl_info', '', get_string('hwsurl_info', 'repository_hive'));
		$mform->addElement('text', 'ssousername', get_string('ssousername', 'repository_hive'), array('size' => '20'));
        $mform->addElement('password', 'ssosharedsecret', get_string('ssosharedsecret', 'repository_hive'), array('size' => '20'));
        $mform->addElement('select', 'hashtype', get_string('hashtype', 'repository_hive'), array('SHA'=>'SHA', 'MD5'=>'MD5'));
        $mform->addElement('hidden', 'hashencoding', 'UTF-8');
        $mform->addElement('text', 'ssoguestuser', get_string('ssoguestuser', 'repository_hive'), array('size' => '20'));

        $mform->addRule('hwsurl', $strrequired, 'required', null, 'client');
        $mform->addRule('ssousername', $strrequired, 'required', null, 'client');
        $mform->addRule('ssosharedsecret', $strrequired, 'required', null, 'client');
        $mform->addRule('hashtype', $strrequired, 'required', null, 'client');
        $mform->addRule('hashencoding', $strrequired, 'required', null, 'client');
        $mform->setDefault('hashtype', 'MD5');
		return true;
    }
}
