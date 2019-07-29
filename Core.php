<?php  

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Core
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */





abstract class Core {
		


		/**
		 * _pdf
		 *
		 * Cezpdf object that generates the actual page
		 * 
		 * @var Cezpdf
		 * @access protected
		 */
		protected $_pdf; 
		
		
		/**
		 * params
		 *
		 * Nerb_Params object that contains all the operating parameters of the site
		 * 
		 * @var Params
		 * @access protected
		 */
		protected $params;
		
		/**
		 * _group
		 *
		 * array containing stamp data
		 * 
		 * (default value: array())
		 * 
		 * @var array
		 * @access protected
		 */
		protected $_group = array(); 
				
		/**
		 * _data
		 * 
		 * (default value: array())
		 * 
		 * @var array
		 * @access protected
		 */
		protected $_data = array();

		/**
		 * _debug
		 *
		 * bool flag for switching debugging mode on an off.  if on
		 * then colored squares will be generated around the elements for 
		 * visual debugging
		 * 
		 * (default value: false)
		 * 
		 * @var bool
		 * @access protected
		 */
		protected $_debug = false;




		/**
		*	Constructor initiates object
		*
		* 	IMPORTANT!!
		*
		*	[ALL] children of this class must contain a --- parent::__construct(); --- statement
		*	immediately after the __construct() function call, otherwise no pdf or paramters will
		*	be loaded into the class and it will throw an error!
		*
		*	@access		public
		*	@return 	Self
		*
		*/
		public function __construct(){ 
			
			# Params object is mandatory and will fail withou it
			if( !Nerb::isRegistered( "params" ) ){
				throw new Nerb_Error( "Object <code>Params</code> is not registered." );
			}

			# PDF object is mandatory and will fail withou it
			elseif( !Nerb::isRegistered( "pdf" ) ){
				throw new Nerb_Error( "Object <code>PDF</code> is not registered." );
			}

			# assign parameter object to $_param variable
			$this->params = Nerb::fetch( "params" );
			
			# assign pdf to $_pdf variable
			$this->_pdf = Nerb::fetch( "pdf" );
			
			return $this;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
	
		
		
		/**
		*	Returns a formatted title string
		*
		*	@access		public
		*	@return 	string
		*/
		public function __toString(){} 
		// end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	get object data
		*
		*	@access		public
		*	@param	 	string $key
		*	@return 	mixed $value is returned 
		*/
		public function __get( $key )
		{
		
			//die;
			// PARAMETERS and PARAMS are reserved words
			if( strtoupper( $key ) == "PARAMETERS" || strtoupper( $key ) == "PARAMS" ) return $this->_data;
				
			// explodes  $key into two parts based on _
			$keys = explode("_", $key, 2); 
				
			// if exploded key is set, then return value, otherwise return array key
			return isset( $this->_data[ $keys[0] ][ $keys[1] ] ) ? $this->_data[ $keys[0] ][ $keys[1] ] : $this->_data[$key];
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	


			
		/**
		*	Set object data
		*
		*	@access		public
		*	@param	 	string $key
		*	@param	 	mixed $value
		*	@return 	mixed old is returned 
		*	@throws 	Nerb_Error 
		*/
		public function __set( $key, $value )
		{
			// explodes  $key into two parts based on _
			$keys = explode("_", $key, 2); 
			
			
			// must pass valid key or error will be thrown
			if( !array_key_exists( $keys[0], $this->_data ) ){
				$error = "<b>".$key."</b> is an invalid parameter. <br /><br />Parameters for this class are:<br /><code style='color:red'>";
				foreach( $this->_data as $key=>$value ){
					$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$key."<br />";
				}
				$error .= "</code>";				
				throw new Nerb_Error( $error );
				
			} // end if		
			
			// get original value
			$old = $this->_data[ $keys[0] ][ $keys[1] ];
			
			// set new value
			$this->_data[ $keys[0] ][ $keys[1] ] = $value;
			// return old value
			return $old;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	



		/**
		*	limit the data being dumped when debugging
		*
		*	@access		public
		*	@return 	array 
		*/
		public function __debugInfo()
		{
			return array("data" => $this->_data);
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	


 
		
		/**
		*	method for  dumping variable data
		*
		*	@access		public
		*	@param	 	string $var
		*	@return 	mixed $value is returned 
		*/
		public function dump( $var ){
			
			// return value
			return $this->$var;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		

		
		/**
		*	get object data
		*
		*	@access		public
		*	@param	 	string $key
		*	@return 	mixed $value is returned 
		*/
		public function data( $key )
		{
			// return value
			return $this->_stamp->$key;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	get object data
		*
		*	@access		public
		*	@param	 	string $key
		*	@return 	mixed $value is returned 
		*/
		public function write( $x, $y )
		{
			// return value
			$this->_write( $x, $y );
			return $this;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	get object data
		*
		*	@access		public
		*	@param	 	string $key
		*	@return 	mixed $value is returned 
		*/
		protected function _write( $x, $y ){ } 
		// end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		/**
		*	function that creates a string that is returned to __toString funciton
		*
		*	@access		public
		*	@return 	mixed $value is returned 
		*/
		protected function _string(){ } 
		// end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		/**
		*	function that calculates all of the dimensions of the element an places the values
		*	into the $_data array for easy access.  
		*
		*	@access		public
		*	@return 	Object Self 
		*/
		protected function _preCalc(){ } 
		// end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
	
		
		/**
		*	cleans up illegal characters in input
		*
		*	@access		protected
		*	@param		string $scott stamp number
		*	@param		float $x
		*	@param		float $y
		*	@return 	string 
		*/
		protected function _cleanText( $text ){
			
			# cleans up illegal characters for page
			$caption = stripslashes( $text);
			$caption = str_replace( "\\", "", $text );
			$caption = html_entity_decode( $text );
			return utf8_decode( $text );
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------

	
	

		/**
		 *	gets the height of a text block
		 *
		 * @access 		protected
		 * @param 		mixed $text
		 * @param 		mixed $maxWidth
		 * @param 		int $fontSize (default: 10)
		 * @param 		string $justification (default: "center")
		 * @return 		float (height of text)
		 */
		protected function getTextHeight( $text, $maxWidth, $fontSize = 10, $justification = "center" ){
			
			# test the size of the text block.
			$yBegin = $this->_pdf->ezGetY();
			$this->_pdf->transaction( "start" );
			$this->_pdf->ezText( $text,
								 $fontSize, 
								 array( "aleft" => 0, 
								 		"aright" => $maxWidth, 
								 		"justification" => $justification
								 	  )
								);
								 
			$height = $yBegin - $this->_pdf->ezGetY();
			$this->_pdf->transaction("abort");
			
			return $height;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------

	
	

		/**
		*	Debugging function that visually shows the margins of the available working area
		*	
		*	in order for this to work properly, the derivative class must contain an array named _data
		*	and it must contain the following elements:
		*	-- height
		*	-- width
		*	-- x
		*	-- y
		*	and the array keys must be names (eg. title, etc) and they will be written in small type [debug_font_size] under
		*	the rectangle produced.  the colors will be randomly generated RGB numbers so that each element will
		*	stand out.
		*
		*	@access		protected
		*	@param		array $data
		*	@return 	Object Self
		*/
		protected function _debug( array $data ){
		
			foreach( $data as $key => $value ){
				
				if( !isset( $value["x"] ) ){
					return;
				}
				
				$r = rand( 0, 100 )/100;
				$g = rand( 0, 100 )/100;
				$b = rand( 0, 100 )/100;
				
				$this->_pdf->setStrokeColor( $r, $g, $b );
				//$this->_pdf->setLineStyle(1);
				$this->_pdf->setLineStyle( 1, 'round', '', array( 0.5, 2 ) );
				
				
				$this->_pdf->setColor( $r, $g, $b );
				$this->_pdf->rectangle( $value["x"], $value["y"], $value["width"], $value["height"] );
				
				$this->_pdf->addText( $value["x"], 
									  $value["y"] - $this->params->debug_font_size - 1, 
									  $this->params->debug_font_size, 
									  "   ".$key
									);
									
				if( $this->params->debug_show_origin ) $this->_origin( $value["x"], $value["y"], $key);
					
			} // end foreach
			
			# reset pdf elements to black
			$this->_reset();
			
			return this;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------

		
	

		
		/**
		*	Debugging function that visually shows the origin of an element with coordinates
		*	
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	Object Self
		*/
		protected function _origin( $x, $y, $display_name = "" ){
		
		$r = rand( 0, 100 )/100;
		$g = rand( 0, 100 )/100;
		$b = rand( 0, 100 )/100;
		
		$this->_pdf->setStrokeColor( $r, $g, $b );
		//$this->_pdf->setLineStyle(1);
		$this->_pdf->setLineStyle( 0.5 );
		
		
		$this->_pdf->setColor( $r, $g, $b );
		$this->_pdf->line( $x + 2 , $y - 2, $x - 2, $y + 2 );
		$this->_pdf->line( $x - 2 , $y - 2, $x + 2, $y + 2 );
		
		$this->_pdf->addText( $x + 2, 
							  $y + 1, 
							  $this->params->debug_font_size, 
							  $x.", ".$y." - ". $display_name
							);
			
			# reset pdf elements to black
			$this->_pdf->setStrokeColor( 0, 0, 0 );
			$this->_pdf->setLineStyle(1);
			$this->_pdf->setColor( 0, 0, 0 );
			
			return this;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------

		
	

		
		/**
		*	Another debugging function that produces a block of text at a certain location for inspecting variable status
		*	
		*	@access		protected
		*	@param		array $data
		*	@param		float $x
		*	@param		float $y
		*	@return 	Object Self
		*/
		protected function _status( array $data, $x, $y){
		
			foreach( $data as $key => $value ){

				$r = rand( 0, 100 )/100;
				$g = rand( 0, 100 )/100;
				$b = rand( 0, 100 )/100;
				
				$this->_pdf->setColor( $r, $g, $b );
				$this->_pdf->addText( $x, 
									  $y, 
									  $this->params->debug_font_size, 
									  $key." - ".json_encode( $value )
									);
				$y -= $this->params->debug_font_size + 1;
			} // end foreach
			
			# reset pdf elements to black
			$this->_pdf->setStrokeColor( 0, 0, 0 );
			$this->_pdf->setLineStyle(1);
			$this->_pdf->setColor( 0, 0, 0 );

			
			
			return this;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------

		
	
		/**
		*	Another debugging function that streams out the current contents of the pdf to the browser
		*	
		*	@access		protected
		*	@return 	void
		*/
		protected function _stream(){
		
				#set header and stream pdf contents
				header("Content-type: application/pdf"); 	
				$this->_pdf->ezStream();
				die;
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------




		/**
		*	a batch function for quickly setting all of the elements of the _data array to the starting origin
		*	
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _setOrigin( $x, $y ){
		
			# add offsets to origin to give final positions
			foreach( $this->_data as $key => $value ){
				$this->_data[$key]["x"] += $x;
				$this->_data[$key]["y"] += $y;
			} // end foreach
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------




		/**
		*	resets the pdf styles to the default to keep from having to keep writing the same block 
		*	over and over again
		*
		*	@access		protected
		*	@return 	object this
		*/
		protected function _reset(){
							
			// reset color and stroke
			// for legibility purposes, the default caption font is always helvetica and black
			$this->_pdf->setColor( 0, 0, 0 );
			$this->_pdf->setLineStyle( 1, 'butt', '', '', '');
			$this->_pdf->selectFont( RESOURCES."/fonts/".$this->params->system_font.".afm" );
			
			return this;
				
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	


} // end class
?>
