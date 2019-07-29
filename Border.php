<?php

/**
 /**
 * Base class for the Stamp album professional that generates high quality pdf
 * pages for stamp albums
 *
 *
 * @package      Stamp Album Pro
 * @subpackage    Album
 * @class      Border
 * @version   1.0
 * @author   Dexter Oddwick <dexter@oddwick.com>
 * @copyright    Copyright (c)2017
 * @license      http://www.oddwick.com
 *
 * @todo
 *
 */



class Border extends Core {
		
	/**
	*	@var	array $params
	*/
			protected $_borders = array(
								// 0->no border
								// single line border
								1 => array(
										"stroke-color" => 0.6,
										"box" => array(
											array(
												"line-style" => 0.5,
												"x" => 25,
												"y" => 25,
											)
										)	
								),
								
								// double line border
								2 => array(
										"stroke-color" => 0.6,
										"box" => array(
											array(
												"line-style" => 0.5,
												"x" => 25,
												"y" => 25,
											),	
											array(
												"line-style" => 0.2,
												"x" => 30,
												"y" => 30,
											)	
										),
								),
								
								// Art Deco Geometric
								3 => array(
										"stroke-color" => 0.9,
										"box" => array(
											array(
												"line-style" => 1.65,
												"x" => 25,
												"y" => 25,
											)	
										),
										"image" => array( 
											"height" => 75, 
											"width" => 75,
											"x" => 23,
											"y" => 23,
											)
								),
								
								// French Thick
								4 => array(
										"stroke-color" => 0.8,
										"box" => array(
											array(
												"line-style" => 1.6,
												"x" => 25,
												"y" => 25,
											),	
											array(
												"line-style" => 3,
												"x" => 29,
												"y" => 29,
											),	
										),
										"image" => array( 
											"height" => 75, 
											"width" => 75,
											"x" => 24.5,
											"y" => 24.5,
											)
								),
								
								// vines
								5 => array(
										"stroke-color" => 0.8,
										"box" => array(
											array(
												"line-style" => 0.5,
												"x" => 29,
												"y" => 29,
											),	
										),
										"image" => array( 
											"height" => 75, 
											"width" => 75,
											"x" => 24.5,
											"y" => 24.5,
											)
								),
			
								// Engraved
								6 => array(
										"stroke-color" => 0.4,
										"box" => array(
											array(
												"line-style" => 0.9,
												"x" => 29,
												"y" => 29,
											)	
										),
										"image" => array( 
											"height" => 75, 
											"width" => 75,
											"x" => 21.0,
											"y" => 20.6,
											)
								),
								
								// Circular Victorian (Default)
								7 => array(
										"stroke-color" => 0.6,
										"box" => array(
											array(
												"line-style" => 0.73,
												"x" => 37.0,
												"y" => 30.5,
											),	
										),
										"image" => array( 
											"height" => 75, 
											"width" => 75,
											"x" => 7.0,
											"y" => 6.4,
											)
								),
								
								// Double Line w/ Flourish
								8 => array(
										"stroke-color" => 0.5,
										"box" => array(
											array(
												"line-style" => 2.8,
												"x" => 26.25,
												"y" => 26.25,
											),	
											array(
												"line-style" => 1.10,
												"x" => 33.75,
												"y" => 33.75,
											)	
										),
										"image" => array( 
											"height" => 75, 
											"width" => 75,
											"x" => 25,
											"y" => 25,
											)
								),
								
								// baroque floral
								9 => array(
										"stroke-color" => 0.00,
										"box" => array(
											array(
												"line-style" => 1,
												"x" => 27.50,
												"y" => 30.25,
											),	
											array(
												"line-style" => 1,
												"x" => 31.00,
												"y" => 33.75,
											)	
										),
										"image" => array( 
											"height" => 75, 
											"width" => 75,
											"x" => 16,
											"y" => 5,
											)
								),
								
								10 => array(
										"stroke-color" => 0.6,
										"box" => array(
											array(
												"line-style" => 0.5,
												"x" => 25,
												"y" => 25,
											),	
											array(
												"line-style" => 0.2,
												"x" => 30,
												"y" => 30,
											)	
										),
										"image" => array( 
											"height" => 75, 
											"width" => 75,
											"x" => 98,
											"y" => 23,
											)
								),
			);

	/**
	 * Constructor initiates border object
	 *
	 * @access  	public
	 * @return  	Border
	 */
	/*
	public function __construct(){

		// set parameters
		$this->params = $params;

		return $this;

	} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	*/




	/**
	 * writes the border to the pdf file
	 *
	 * @access 	 	public
	 * @param  		int $force forces the border to other than the default
	 * @return  	void
	 */
	public function write( $force = NULL ){

		# 	force border style if no border has been specified
		#	otherwise use border specified in params
		$border =  $force ? $force : $this->params->border_style;
		
		
		#	if border = none, exit
		if( !$border ){
			return false;
		}


		#	get the border options
		$opt = $this->_borders[ $border ];


		# 	set the stroke color (all strokes are gray for right now)
		$this->_pdf->setStrokeColor( $opt["stroke-color"], $opt["stroke-color"], $opt["stroke-color"] );

		
		#	iterate through the box elements to create border boxes
		foreach( $opt["box"] as $box ){

			#	 set the stroke width 
			$this->_pdf->setLineStyle( $box["line-style"] );

			
			# create the bounding rectangle ( x1, y1, width, height )
			$this->_pdf->rectangle( 
				$box["x"] + $this->params->margin_adjust,
				$box["y"],
				$this->params->max_x - ( $box["x"] * 2 ) - $this->params->margin_adjust,
				$this->params->max_y - ( $box["y"] * 2 ) 
			);
		} // end for each
		//Nerb::inspect( $this->params, true );
		
		#	if there is an image given, it will be added based on border style number
		#	border iamges must be in the format RESOURCES/borders/border_[border_number]_[tl|tr|br|bl].jpg in order to be added to the page
		#
		# 	todo:  add image mask for different corners
		if( $opt["image"] ){
			
			#	top left
			$this->_pdf->addJpegFromFile( 
				RESOURCES."/borders/border_".$border."_tl.jpg", 
				$opt["image"]["x"] + $this->params->margin_adjust, 
				$this->params->max_y - $opt["image"]["y"] - $opt["image"]["height"], 
				$opt["image"]["height"], 
				$opt["image"]["width"] 
			);
			
			#	bottom right
			$this->_pdf->addJpegFromFile( 
				RESOURCES."/borders/border_".$border."_br.jpg", 
				$this->params->max_x - $opt["image"]["x"] - $opt["image"]["height"], 
				$opt["image"]["y"], 
				$opt["image"]["height"], 
				$opt["image"]["width"] 
			);
			
			
			#	if flourishes are on all 4 corners
			if( $this->params->border_flourishes == 4 ){
				
				#	top right
				$this->_pdf->addJpegFromFile( 
					RESOURCES."/borders/border_".$border."_tr.jpg", 
					$this->params->max_x - $opt["image"]["x"] - $opt["image"]["width"], 
					$this->params->max_y - $opt["image"]["y"] - $opt["image"]["height"], 
					$opt["image"]["height"], 
					$opt["image"]["width"] 
				);
				
				# 	bottom left
				$this->_pdf->addJpegFromFile(					
					RESOURCES."/borders/border_".$border."_bl.jpg", 
					$opt["image"]["x"] + $this->params->margin_adjust, 
					$opt["image"]["y"], 
					$opt["image"]["height"], 
					$opt["image"]["width"] 
				);
			}// end if flourishes
		}// end if image
		
		
		#	display copyright on bottom of page
		if( $this->params->show_copyright ){
			$this->_pdf->selectFont( RESOURCES."/fonts/Helvetica.afm");
			$copyright = str_replace( "*", date( "Y", time()), $this->params->copyright );
			$this->_pdf->setColor(0.6, 0.6, 0.6);
			$this->_pdf->addText( $this->params->max_x - $this->_pdf->getTextWidth( 5, $copyright ) - 80, 14, $this->params->copyright_font_size, $copyright );
		}
				

		#	reset color
		$this->_pdf->setColor( 0, 0, 0 );
		
		

	} // end function -----------------------------------------------------------------------------------------------------------------------------------------------




} /* end class */
?>
