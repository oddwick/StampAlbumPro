<?php  /* 

/**
 *	class for authorizing a set of credentials against a database
 *
 *
 * @category   		SAPro
 * @package    		SAPro
 * @subpackage 		Title
 * @access			public
 * @version			1.0
 * @author			Derrick Haggerty <dhaggerty@gnert.com>
 * @copyright  	Copyright ©2012  Gnert Software Studios, Inc. (http://www.gnert.com)
 * @license    		http://www.gnert.com/docs/license
 *
 */


class array2xml {
		
		/**
		*	@var	array $_params
		*	@var	Nerb_Database_Row $_stamp
		*	@var	string $_title
		*	@var	bool $_child
		*	@var	bool $_variant
		*	@var	bool $_specialized
		*	@var	bool $_highlighted
		*	@var	bool $_image
		*	@var	bool $_debug flag for debugging mode
		*/
		protected $_params= array(
				'xml'=> "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n", // sets the flag if image is a lightbox item
				'root'=> "Result", // root object
				'pretty'=> true // formatting option
			);
		protected $_data = array(); // array of data
		protected $_xml = NULL; // cached xml generated
		
		// debugging variables
		protected $_debug = false;


		/**
		*	Constructor initiates stamp title
		*
		*	@access	public
		*	@param	Nerb_Database_Row $row
		*	@return 	void
		*/
		public function __construct($data = NULL){
				if( $data && is_array($data) ){
					$this->_data = $data;
				}
		} /* end function*/
		
		
		
		/**
		*	Set object operating parameters
		*
		*	@access	public
		*	@param	string $key
		*	@param	mixed $value
		*	@return 	mixed old is returned 
		*	@throws 	Nerb_Error
		*/
		public function __set($key, $value){
				
				// must pass valid key or error will be thrown
				if(!array_key_exists($key, $this->_params)){
					$error = "<b>'$key'</b> is an invalid parameter. <br /><br />Parameters for this class are:<br /><code style='color:red'>";
					foreach($this->_params as $key=>$value){
						$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$key."<br />";
					}
					$error .= "</code>";				
					throw new Nerb_Error($error);
				} // end if		
				
				// get original value
				$old = $this->_params[$key];
				
				// set new value
				$this->_params[$key] = $value;
				
				// return old value
				return $old;
				
		} /* end function*/
	

			
		/**
		*	Get object operating parameters
		*
		*	@access	public
		*	@param	string $key
		*	@return 	mixed returns the whole parameters array with $class->PARAMETERS or $class->PARAMS
		*/
		public function __get($key){
				// PARAMETERS and PARAMS are Nerb reserved words
				if($key == 'PARAMETERS' || $key == 'PARAMS'){ 
					return $this->_params;
				} else {
					return isset($this->_params[$key])?$this->_params[$key]:NULL;
				}
		} /* end function*/
	

			
		/**
		*	Get object operating parameters
		*
		*	@access	public
		*	@param	string $key
		*	@return 	mixed returns the whole parameters array with $class->PARAMETERS or $class->PARAMS
		*/
		public function __toString(){
				$this->_xml = $this->_format($this->_data);
				return $this->_xml;
		} /* end function*/
	

			
		
		/**
		*	Get object operating parameters
		*
		*	@access	public
		*	@param	array $array
		*	@return 	mixed returns the whole parameters array with $class->PARAMETERS or $class->PARAMS
		*/
		public function add($array){
				if(is_array($array) && $this->_data = $array){
					return true;
				}else{
					return false;
				}
		} /* end function*/
	


			
		/**
		*	Recursively processes the array into an xml string
		*
		*	@access	public
		*	@param	string $key
		*	@param	int $level recursive incrementor
		*	@return 	mixed returns the whole parameters array with $class->PARAMETERS or $class->PARAMS
		*/
		public function _format($array, $level = 0, $root=NULL){
				
				// the number of tabs repeated for a pretty display
				if($this->_params['pretty'] && $level > 0){
					$pretty = str_repeat("\t", $level);
				}
				
				
				foreach($array as $key=>$value){
					
					// if value is an array, call itself
					if( is_array($value) ){
						$string .= $pretty."<".($root?$root:$key).">";
						$string .= "\n".$this->_format($value, $level+1, !is_numeric($key)?$key:NULL);
						$string .= $pretty."</".($root?$root:$key).">\n"; // closing tag
					} else {
						if(!$value){
							$string .=  $pretty."<".$key."/>\n";
						}else{
							$string .= $pretty."<".($root?$root:$key).">".$value."</".($root?$root:$key).">\n"; // closing tag
						}
					}
				}
				return $string;
		} /* end function*/
	

			
        /**
        * The main function for converting to an XML document.
        * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
        *
        * @param array $data
        * @param string $rootNodeName - what you want the root node to be - defaultsto data.
        * @param SimpleXMLElement $xml - should only be used recursively
        * @return string XML
        */
        public static function array_to_xml($data, $rootNodeName = 'data', $xml=null, $parentXml=null)
        {

                // turn off compatibility mode as simple xml throws a wobbly if you don't.
                if (ini_get('zend.ze1_compatibility_mode') == 1)
                {
                        ini_set ('zend.ze1_compatibility_mode', 0);
                }
                //if ($rootNodeName == false) {
                //      $xml = simplexml_load_string("<s/>");
                //}
                if ($xml == null)
                {
                       $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
                }

                // loop through the data passed in.
                foreach($data as $key => $value)
                {
                        // Create a name for this item based off the attribute name or if this is a item in an array then the parent nodes name.
                        $nodeName = is_numeric($key) ? $rootNodeName . '-item' : $key;
                        $nodeName = preg_replace('/[^a-z1-9_-]/i', '', $nodeName);

                        // If this item is an array then we will be recursine to the logic is more complex.
                        if (is_array($value)) {
                                // If this node is part of an array we have to proccess is specialy.
                                if (is_numeric($key)) {
                                        // Another exception if this is teh root node and is an array.  In this case we don't have a parent node to use so we must use the current node and not update the reference. 
                                        if($parentXml == null) {
                                                $childXml = $xml->addChild($nodeName);
                                                self::array_to_xml($value, $nodeName, $childXml, $xml);
                                        // If this is a array node then we want to add the item under the parent node instead of out current node. Also we have to update $xml to reflect the change.
                                        } else {
                                                $xml = $parentXml->addChild($nodeName);
                                                self::array_to_xml($value, $nodeName, $xml, $parentXml);
                                        }
                                } else {
                                        // For a normal attribute node just add it to the parent node.
                                        $childXml = $xml->addChild($nodeName);
                                        self::array_to_xml($value, $nodeName, $childXml, $xml);
                                }
                        // If not then it is a simple value and can be directly appended to the XML tree.
                        } else {
                                $value = htmlentities($value);
                                $xml->addChild($nodeName, $value);
                        }
                }

                // Pass back as string or simple xml object.
                return $xml->asXML();
        }		
		
		
} /* end class */
?>
