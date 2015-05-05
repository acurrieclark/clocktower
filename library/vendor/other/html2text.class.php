<?php

/************************************************************
 Library to convert HTML into an approximate text equivalent
v2.0 update 04/03/2009 with major new functionality
*************************************************************

Please see http://www.howtocreate.co.uk/php/ for details
Please see http://www.howtocreate.co.uk/php/html2texthowto.html for detailed instructions
Please see http://www.howtocreate.co.uk/jslibs/termsOfUse.html for terms and conditions of use

Note that although version 1 of this script was included with permission in a GPL bundle,
the script still retains its own license terms, separate from the terms of other software
included in the bundle. This version is not available under GPL.

*/

/**
* html2text
* classed by acurrieclark 03/04/13
*/
class html2text
{

static $elements = Array();


	static function construct()
	{
		self::$elements['the document'] = Array(true,true); //drop first and last margins in the document - recommended: true,true
		self::$elements['unknown element'] = Array(false,false,false,0,0,false,'','',false,false,false); //used for all unknown or default elements

		//list all elements that need special (non-default) handling:
		self::$elements['html'] = Array(true,false,false,10,10,false,'','',false,false,false);
		self::$elements['title'] = Array(true,false,false,1,1,false,'',"\r\n          --------------------",false,false,false);
		self::$elements['script'] =
		self::$elements['style'] =
		self::$elements['datalist'] = Array(false,false,true,0,0,false,'','',false,false,false);
		self::$elements['h1'] = Array(true,false,false,2,2,false,'** ',' **',false,false,false);
		self::$elements['h2'] = Array(true,false,false,2,2,false,'*','*',false,false,false);
		self::$elements['h3'] = Array(true,false,false,2,2,false,'-','-',false,false,false);
		self::$elements['h4'] =
		self::$elements['h5'] =
		self::$elements['h6'] =
		self::$elements['p'] =
		self::$elements['ul'] =
		self::$elements['dl'] =
		self::$elements['table'] =
		self::$elements['blockquote'] =
		self::$elements['legend'] =
		self::$elements['dir'] =
		self::$elements['menu'] =
		self::$elements['article'] =
		self::$elements['aside'] =
		self::$elements['datagrid'] =
		self::$elements['details'] =
		self::$elements['dialog'] =
		self::$elements['figure'] =
		self::$elements['footer'] =
		self::$elements['nav'] =
		self::$elements['section'] = Array(true,false,false,2,2,false,'','',false,false,false);
		self::$elements['blockquote'] = Array(true,false,false,2,2,false,'before_quote','}}',true,false,false);
		self::$elements['form'] = Array(true,false,false,2,2,false,'before_form','',true,false,false);
		self::$elements['pre'] =
		self::$elements['listing'] =
		self::$elements['plaintext'] =
		self::$elements['xmp'] = Array(true,true,false,2,2,false,'','',false,false,false);
		self::$elements['head'] =
		self::$elements['body'] =
		self::$elements['noframes'] =
		self::$elements['div'] =
		self::$elements['fieldset'] =
		self::$elements['dt'] =
		self::$elements['caption'] =
		self::$elements['thead'] =
		self::$elements['body'] =
		self::$elements['tfoot'] =
		self::$elements['tr'] =
		self::$elements['address'] =
		self::$elements['center'] =
		self::$elements['marquee'] =
		self::$elements['header'] = Array(true,false,false,1,1,false,'','',false,false,false);
		self::$elements['dt'] = Array(true,false,false,1,1,false,'* ','',false,false,false);
		self::$elements['th'] =
		self::$elements['td'] = Array(true,false,false,0,0,true,"\t",'',false,false,true);
		self::$elements['dd'] = Array(true,false,false,1,1,true,"        ",'',false,false,false);
		self::$elements['ol'] = Array(true,false,false,2,2,false,'','',false,false,false);
		self::$elements['li'] = Array(true,false,false,1,1,true,'before_li','',true,false,false);
		self::$elements['br'] = Array(true,false,false,0,0,false,"\r\n",'',false,false,false); //use \r\n instead of margin - must not collapse
		self::$elements['hr'] = Array(true,false,false,1,1,false,'          --------------------','',false,false,false);
		self::$elements['sup'] = Array(false,false,false,0,0,false,'^','',false,false,false);
		self::$elements['sub'] = Array(false,false,false,0,0,false,'[',']',false,false,false);
		self::$elements['s'] =
		self::$elements['strike'] =
		self::$elements['del'] = Array(false,false,false,0,0,false,'[DEL: ',' :DEL]',false,false,false);
		self::$elements['ins'] = Array(false,false,false,0,0,false,'[INS: ',' :INS]',false,false,false);
		self::$elements['strong'] =
		self::$elements['b'] =
		self::$elements['mark'] = Array(false,false,false,0,0,false,'*','*',false,false,false);
		self::$elements['em'] =
		self::$elements['i'] = Array(false,false,false,0,0,false,'/','/',false,false,false);
		self::$elements['u'] = Array(false,false,false,0,0,false,'_','_',false,false,false);
		self::$elements['q'] = Array(false,false,false,0,0,false,'"','"',false,false,false);
		self::$elements['a'] = Array(false,false,false,0,0,false,'before_link','',true,false,false);
		self::$elements['area'] = Array(false,false,false,0,0,false,'before_area','',true,false,false);
		self::$elements['base'] = Array(false,false,false,0,0,false,'before_base','',true,false,false);
		self::$elements['input'] = Array(false,false,false,0,0,false,'before_input','',true,false,false);
		self::$elements['bb'] = Array(false,false,false,0,0,false,'[INPUT]','',false,false,false);
		self::$elements['isindex'] = Array(true,false,false,2,2,false,'before_isindex','',true,false,false);
		self::$elements['textarea'] =
		self::$elements['button'] =
		self::$elements['select'] = Array(false,false,true,0,0,false,'[INPUT]','',false,false,false);
		self::$elements['img'] = Array(false,false,false,0,0,false,'before_img','',true,false,false);
	}


//functions for special case elements, where an attribute or some other feature of the element determines how it should behave

	static function before_li($element,$index) {
		//get parent tag name do work out if it is a bullet or numbered list
		$parent_node = $element->parentNode;
		$parent_tag = ( $parent_node == $element->ownerDocument ) ? '' : $element->parentNode->tagName;
		if( !$element->ownerDocument->h2t_isxml ) {
			$parent_tag = strToLower($parent_tag);
		}
		$prefix = '';
		//for each LI ancestor, add indents for nested lists
		while( $parent_node != $element->ownerDocument ) {
			if( ( $element->ownerDocument->h2t_isxml ? $parent_node->tagName : strtolower($parent_node->tagName) ) == 'li' ) {
				$prefix .= '  ';
			}
			$parent_node = $parent_node->parentNode;
		}
		return $prefix.(($parent_tag=='ol')?($index.'.'):'·').' ';
	}

	static function before_img($element,$index) {
		if( $element->hasAttribute('alt') && ( $alt = self::cleanspace($element->getAttribute('alt')) ) ) {
			return "[IMG: $alt]";
		}
		return '';
	}

	static function before_input($element,$index) {
		if( $element->hasAttribute('type') && strtolower(self::cleanspace($element->getAttribute('type'))) != 'hidden' ) {
			return '[INPUT]';
		}
		return '';
	}

	static function before_isindex($element,$index) {
		if( $element->hasAttribute('prompt') && ( $prompt = self::cleanspace($element->getAttribute('prompt')) ) ) {
			return "$prompt [INPUT]";
		}
		return '[INPUT]';
	}

	static function before_link($element,$index) {
		if( $element->hasAttribute('href') && ( $href = self::resolve($element->getAttribute('href'),$element) ) ) {
			if( $element->childNodes->length == 1 && $element->firstChild->nodeType == XML_TEXT_NODE && $element->firstChild->nodeValue == preg_replace( "/^mailto:/iu", '', $href ) ) {
				//link text is exactly the same as the link itself - leave only the textNode
				//textContent exists, but has to do more work, is slower, ignores before/after and picks up void content, so only accept single text nodes
				return '';
			}
			return "[LINK: $href] ";
		}
		return '';
	}

	static function before_area($element,$index) {
		if( $element->hasAttribute('href') && ( $href = self::resolve($element->getAttribute('href'),$element) ) ) {
			return "[LINK: $href] ".($element->hasAttribute('alt')?self::cleanspace($element->getAttribute('alt')):'');
		}
		return '';
	}

	static function before_form($element,$index) {
		if( $element->hasAttribute('action') && ( $action = self::resolve($element->getAttribute('action'),$element) ) ) {
			return "[FORM: $action]";
		}
		return '';
	}

	static function before_quote($element,$index) {
		if( $element->hasAttribute('cite') && ( $cite = self::resolve($element->getAttribute('cite'),$element) ) ) {
			return "{{ [CITE: $cite]";
		}
		return '{{';
	}

	static function before_base($element,$index) {
		if( $element->hasAttribute('href') ) {
			$element->ownerDocument->h2t_base = @parse_url($element->getAttribute('href'));
			$element->ownerDocument->h2t_base['pathdir'] = preg_replace("/[^\/]+$/u",'',$element->ownerDocument->h2t_base['path']);
			$element->ownerDocument->h2t_base['pathfile'] = preg_replace("/^[\w\W]*\//u",'',$element->ownerDocument->h2t_base['path']);
			$element->ownerDocument->h2t_base['basefound'] = true;
			//parse_url does not populate properties if it does not find those parts of the URL - this prevents it using unititialised values
			foreach( Array('scheme','host','port','user','pass','path','query','fragment') as $key ) {
				if( !isset($element->ownerDocument->h2t_base[$key]) ) {
					$element->ownerDocument->h2t_base[$key] = '';
				}
			}
		}
	}

	static function cleanspace($str) {
		return preg_replace("/\s+/u",' ',preg_replace("/^\s+|\s+$/u",'',$str));
	}

	static function resolve($href,$element) {
		$base = $element->ownerDocument->h2t_base;
		//resolve $href according to the $base href
		if( preg_match("/^javascript:/iu",$href) ) { return ''; } //JavaScript URLs are useless
		if( preg_match("/^[^\/\#?]*:/u",$href) ) { return $href; } //assume absolute URL
		if( ( !$href || preg_match("/^\#/u",$href) ) && !$base['basefound'] ) { return ''; } //relative or fragment url with no visible path - useless
		if( !$base['basefound'] ) { return $href; } //relative with visible path but no base - can't help that
		//if it begins with // then just add protocol
		$prefix = $base['scheme'].':';
		if( preg_match("/^\/\//u",$href) ) { return $prefix.$href; }
		//if it begins with / then add protocol://user?:pass?@?host:port?
		@$prefix .= '//'.($base['user']?$base['user']:'').($base['pass']?(':'.$base['pass']):'').(($base['user']||$base['pass'])?'@':'').
		           $base['host'].($base['port']?(':'.$base['port']):'');
		if( preg_match("/^\//u",$href) ) { return $prefix.$href; }
		//if it begins with ./ or [^.?\#] then add protocol://user?:pass?@?host:port?/folder_path/
		$pathprefix = $base['pathdir'];
		if( preg_match("/^(\.\/|[^.?\#])/u",$href) ) { return $prefix.$pathprefix.preg_replace("/^\.\//u",'',$href); }
		//if it begins with ../ then remove one folder for each initial occurence of ../
		if( preg_match("/^\.\.\//u",$href) ) {
			do {
				$href = preg_replace("/^\.\.\//u",'',$href);
				$pathprefix = preg_replace("/[^\/]*\/[^\/]*$/u",'',$pathprefix);
			} while( preg_match("/^\.\.\//u",$href) );
			return $prefix.($pathprefix?$pathprefix:'/').$href; //put back a / if it stepped back past the end of the folder path (too many ../)
		}
		//if it begins with ? then add protocol://user?:pass?@?host:port?/folder_path/filename
		$pathprefix .= $base['pathfile'];
		if( preg_match("/^\?/u",$href) ) { return $prefix.$pathprefix.$href; }
		//if it begins with # then add protocol://user?:pass?@?host:port?/folder_path/filename?querystring
		$pathprefix .= $base['query']?('?'.$base['query']):'';
		if( preg_match("/^\#/u",$href) ) { return $prefix.$pathprefix.$href; }
		return $prefix.$pathprefix.$href.($base['fragment']?('#'.$base['fragment']):'');
	}

	static function formattext($flags,$node_value) {
		//return the text node, with pending margins, and pending spaces
		//reset all pending states
		$output = '';
		if( $flags->h2t_blockstart ) {
			for( $i = 0; !$flags->h2t_ignoremargin and ( $i < $flags->h2t_currentmargin ); $i++ ) {
				$output .= "\r\n";
			}
			$flags->h2t_currentmargin = 0;
			$flags->h2t_blockstart = false;
		} else if( $flags->h2t_pendingspace ) {
			$flags->h2t_pendingspace = false;
			$output .= ' ';
		}
		$flags->h2t_ignoremargin = false;
		$output .= preg_replace("/\x0160/u",' ',$node_value); //nbsp becomes a regular space
		return $output;
	}

	static function render($element,$elementindex) {
		//try to create a textual rendering of the element and its children
		$flags = $element->ownerDocument;
		//store last element formats, to restore them as needed when leaving this element
		$previous_format = $flags->h2t_ispre;
		$previous_margindrop = $flags->h2t_ignoremargin;
		//get formatting information for this tag
		$tag_name = $flags->h2t_isxml ? $element->tagName : strtolower($element->tagName);
		$elem_det = &self::$elements[$tag_name];
		if( !$elem_det ) { $elem_det = &self::$elements['unknown element']; }
		//determine computed formatting
		$flags->h2t_currentmargin = max( $flags->h2t_currentmargin, $elem_det[3] ); //basic margin collapse ;)
		if( $elem_det[0] ) {
			//block start - drop any pending spaces
			$flags->h2t_pendingspace = false;
			$flags->h2t_blockstart = true;
		}
		$flags->h2t_ispre = $elem_det[1] || $previous_format;
		//start working out element rendering
		$element_render = '';

		//deal with ::before
		$temprender = '';
		if( $elem_det[6] ) {
			//it can be dropped using the :first-child rule, but still need to output something, or dropFirstChildMargin can remove all linebreaks (TD/TH)
			$drop = $elementindex == 1 && $elem_det[10];
			if( !$elem_det[8] ) {
				$element_render .= self::formattext($flags,$drop?'':$elem_det[6]);
			} elseif( $temprender = self::$elem_det[6]($element,$elementindex) ) {
				$element_render .= self::formattext($flags,$drop?'':$temprender);
			}
			if( $elem_det[0] && preg_match( "/\s$/u", $elem_det[8] ? $temprender : $elem_det[6] ) ) {
				//there was some output ending in whitespace that will have used up any pending margin
				//it must not be allowed to affect pending whitespace status or it creates weird indents
				$flags->h2t_pendingspace = false;
				$flags->h2t_blockstart = true;
			}
		}

		//deal with contents
		$flags->h2t_ignoremargin = $elem_det[5] || $previous_margindrop;
		$num_child = $element->childNodes->length;
		for( $i = 0, $elemindex = 0; !$elem_det[2] && ( $i < $num_child ); $i++ ) {
			$node = $element->childNodes->item($i);
			if( $node->nodeType == XML_TEXT_NODE || $node->nodeType == XML_CDATA_SECTION_NODE ) {
				if( $flags->h2t_ispre ) {
					//render entire text node, but only use windows linebreaks, as required by 2822
					$element_render .= self::formattext($flags,preg_replace("/\r\n?|\n/u","\r\n",$node->nodeValue));
				} else {
					//enforce: <p>  <span> </span> <span> Single <span>  space </span> </span></p>
					//use [ \t\f\r\n] because \s also matches nbsp when 'u' flag is used
					if( !$flags->h2t_blockstart ) {
						$flags->h2t_pendingspace = $flags->h2t_pendingspace || preg_match("/^[ \t\f\r\n]/u",$node->nodeValue);
					}
					//'u' flag incorrectly deletes utf-8 nodes here if * is used instead of +
					$content = preg_replace("/[ \t\f\r\n]+/u",' ',preg_replace("/^[ \t\f\r\n]+|[ \t\f\r\n]+$/u",'',$node->nodeValue));
					if( $content ) {
						$element_render .= self::formattext($flags,$content);
						$flags->h2t_pendingspace = $flags->h2t_pendingspace || preg_match("/[ \t\f\r\n]$/u",$node->nodeValue);
					}
				}
			} elseif ( $node->nodeType == XML_ELEMENT_NODE ) {
				$elemindex++;
				$element_render .= self::render($node,$elemindex);
			}
		}

		//deal with ::after
		if( $elem_det[7] ) {
			if( !$elem_det[9] ) {
				if( $elem_det[0] && preg_match( "/^\s/u", $elem_det[7] ) ) {
					//don't output pending spaces on blocks if the 'after' content has its own
					$flags->h2t_pendingspace = false;
				}
				$element_render .= self::formattext($flags,$elem_det[7]);
			} elseif( $temprender = self::$elem_det[7]($element,$elementindex) ) {
				if( $elem_det[0] && preg_match( "/^\s/u", $temprender ) ) {
					$flags->h2t_pendingspace = false;
				}
				$element_render .= self::formattext($flags,$temprender);
			}
		}

		//restore preformatting state, and margin drop if it has not been used
		$flags->h2t_ispre = $previous_format;
		$flags->h2t_ignoremargin = $previous_margindrop && $flags->h2t_ignoremargin;
		if( $elem_det[0] ) {
			//block end (next output creates a new block, even if it is inline) - drop any pending spaces
			$flags->h2t_pendingspace = false;
			$flags->h2t_blockstart = true;
		}
		$flags->h2t_currentmargin = max( $flags->h2t_currentmargin, $elem_det[4] );
		return $element_render;
	}

	static function convert( $sourceStr, $isXML = false, $isfile = false ) {
		self::construct();
		if( is_object($sourceStr) ) {
			$DOM = $sourceStr;
		} else {
			$DOM = new DOMDocument();
			//parse the markup
			if( $isXML ) {
				if( $isfile ) {
					$DOM->load($sourceStr);
				} else {
					$DOM->loadXML($sourceStr);
				}
			} else if( $isfile ) {
				$DOM->loadHTMLFile($sourceStr);
			} else {
				//remove any PHP (and XML prologs) if it exists
				$strippedStr = preg_replace( "/<\?[\w\W]*\?>/u", '', $sourceStr );
				if( $strippedStr === null ) {
					//ouch - encoding problem detected
					trigger_error('String passed to html2text has encoding issues (contains characters not permitted in the encoding PHP is using) - attempting recovery by not stripping PHP from the source string',E_USER_WARNING);
					$DOM->loadHTML( $sourceStr );
				} else {
					$DOM->loadHTML( $strippedStr );
				}
				unset($strippedStr);
			}
		}
		unset($sourceStr); //free up memory before layout happens
		//flags - it would be possible to pass flags between recursive function calls, or use references, but this is easier
		//flag: current dropFirstChildMargin value
		$DOM->h2t_ignoremargin = self::$elements['the document'][0];
		//flag: current margin
		$DOM->h2t_currentmargin = 0;
		//flag: a block/flow has been opened, but no text content has been rendered in it yet
		$DOM->h2t_blockstart = true;
		//flag: is there a pending whitespace character to output before next text content
		$DOM->h2t_pendingspace = false;
		//flag: says if any parent node has the isPreformatted state set
		$DOM->h2t_ispre = false;
		//flag: says if tagName is case sensitive
		$DOM->h2t_isxml = $isXML;
		//flag: the document's base href
		$DOM->h2t_base = Array('scheme'=>'','host'=>'','port'=>'','user'=>'','pass'=>'','path'=>'','query'=>'','fragment'=>'','pathdir'=>'','pathfile'=>'','basefound'=>'');
		//wordrapping is quite brutal and will also affect indents and preformatting, but as this is intended for email use, I don't care
		if( $DOM->documentElement ) {
			$sourceStr = self::render($DOM->documentElement,1);
			if( !self::$elements['the document'][1] ) {
				$DOM->h2t_blockstart = true;
				//get any remaining linebreaks
				$sourceStr .= self::formattext($DOM,'');
			}
			//wordwrap is not multibyte-safe ... can't help that, and it works in most cases
			return wordwrap($sourceStr,75,"\r\n");
		} else {
			return '';
		}
	}

}

?>
