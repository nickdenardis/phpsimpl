<?php
/**
 * Base Mail Class
 * 
 * Used to create and send emails with attachments.
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class Mail{
	/**
	 * @var	array
	 */
	private $sendto = array();
	/**
	 * @var	array
	 */
	private $acc = array();
	/**
	 * @var	array
	 */
	private $abcc = array();
	/**
	 * @var	array List of message attachments
	 */
	private $aattach = array();
	/**
	 * @var	array List of message headers
	 */
	private $xheaders = array();
	/**
	 * @var	array Message priorities referential
	 */
	private $priorities = array('1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)');
	/**
	 * @var	string Character set of message
	 */
	private $charset = "us-ascii";
	/**
	 * @var	string Character encoding
	 */
	private $ctencoding = "7bit";
	/**
	 * @var	int If reciept is desired
	 */
	private $receipt = 0;
	/**
	 * @var	string Content type of mail
	 */
	private $content_type = 'text/plain';
	/**
	 * @var	string Boundary of types in mail
	 */
	private $boundary;
	/**
	 * @var	string Actual email body
	 */
	private $body;
	
	/**
	 * Mail contructor
	 * 
	 * @return null
	 */
	public function __construct(){
		$this->autoCheck(true);
		$this->boundary = '--' . md5(uniqid("myboundary"));
	}
	
	/**
	 * Activate or deactivate the email addresses validator
	 * 
	 * @param $bool Boolean set to true to turn on the auto validation
	 * @return null
	 */
	public function autoCheck($bool){
		$this->checkAddress = ($bool)?true:false;
	}
	
	/**
	 * Define the subject line of the email
	 * 
	 * @param $subject String of the title of the email
	 * @return null
	 */
	public function Subject($subject){
		$this->xheaders['Subject'] = strip_tags(strtr( $subject, "\r\n" , "  " ));
	}
	
	/**
	 * Set the sender of the mail
	 * 
	 * @param $from String should be an email address
	 * @return null
	 */
	public function From($from){
		if( !is_string($from) ) {
			echo "Class Mail: error, From is not a string";
			exit;
		}
		$this->xheaders['From'] = $from;
	}
	
	/**
	 * Set the reply-to header 
	 * 
	 * @param $email String should be an email address
	 * @return bool
	 */
	public function ReplyTo( $address ){
		if( !is_string($address) ) 
			return false;
		
		$this->xheaders["Reply-To"] = $address;
		
		return true;	
	}
	
	/**
	 * Add a receipt to the mail ie.  a confirmation is returned to the "From" address (or "ReplyTo" if defined) when the receiver opens the message.
	 * 
	 * @warning this functionality is *not* a standard, thus only some mail clients are compliants.
	 * @return null
	 */
	public function Receipt(){
		$this->receipt = 1;
	}
	
	/**
	 * Set the mail recipient
	 * 
	 * @param $to String email address, accept both a single address or an array of addresses
	 * @return null
	 */
	public function To($to){
		if(is_array($to))
			$this->sendto= $to;
		else 
			$this->sendto[] = $to;
	
		if($this->checkAddress == true)
			return $this->CheckAdresses($this->sendto);
			
		return true;
	}
	
	/**
	 * Set the Carbon Copy headers
	 * 
	 * @param $cc String email address, accept both a single address or an array of addresses
	 * @return bool
	 */
	public function Cc($cc){
		if(is_array($cc))
			$this->acc= $cc;
		else 
			$this->acc[]= $cc;
			
		if($this->checkAddress == true)
			$this->CheckAdresses($this->acc);
	}
	
	/**
	 * Set the Bcc headers (blank carbon copy)
	 * 
	 * @param $bcc Email address(es), accept both array and string
	 * @return null
	 */
	public function Bcc($bcc){
		if(is_array($bcc)){
			$this->abcc = $bcc;
		}else{
			$this->abcc[]= $bcc;
		}
	
		if($this->checkAddress == true)
			$this->CheckAdresses($this->abcc);
	}
	
	/**
	 * Set the body of the email
	 * 
	 * @todo Add the ability to send text/html email
	 * @param $body String containing be body of the email
	 * @param $content_type Strong containing the content type (iso-8859-1)
	 * @param $charset String containing the charset (us-ascii)
	 * @return null
	 */
	public function Body($body, $content_type='', $charset='', $encoding=''){
		$this->body = $body;
		
		if ($content_type != '')
			$this->content_type = strtolower(trim($content_type));
		
		if($charset != '') {
			$this->charset = strtolower($charset);
			if( $this->charset != 'us-ascii')
				$this->ctencoding = '8bit';
			
			if ($encoding != '')
				$this->ctencoding = $encoding;
		}
	}
	
	/**
	 * Set the Organization of the email
	 * 
	 * @param $org String containing the Organization
	 * @return null
	 */
	public function Organization($org){
		if(trim($org != ''))
			$this->xheaders['Organization'] = $org;
	}
	
	/**
	 * Set the Priority of the email
	 * 
	 * @param $priority Integer taken between 1 (highest) and 5 (lowest)
	 * @return bool
	 */
	public function Priority($priority){
		if(!intval($priority))
			return false;
			
		if(!isset($this->priorities[$priority-1]))
			return false;
	
		$this->xheaders["X-Priority"] = $this->priorities[$priority-1];
		
		return true;
	}
	
	/**
	 * Attach a file to the mail
	 * 
	 * @param $filename String path of the file to attach
	 * @param $filetype String MIME-type of the file. default to 'application/x-unknown-content-type'
	 * @param $disposition String instruct the Mailclient to display the file if possible ("inline") or always as a link ("attachment") possible values are "inline", "attachment"
	 * @return null
	 */
	public function Attach($filename, $filetype = '', $disposition = 'inline'){
		if($filetype == '')
			$filetype = "application/x-unknown-content-type";
		
		$this->aattach[] = $filename;
		$this->actype[] = $filetype;
		$this->adispo[] = $disposition;
	}
	
	/**
	 * Build the email message
	 * 
	 * @return null
	 */
	protected function BuildMail(){
		$this->headers = '';
		
		if(count($this->acc) > 0)
			$this->xheaders['CC'] = implode( ', ', $this->acc );
		
		if(count($this->abcc) > 0) 
			$this->xheaders['BCC'] = implode( ', ', $this->abcc );
		
	
		if($this->receipt) {
			if( isset($this->xheaders["Reply-To"] ) )
				$this->xheaders["Disposition-Notification-To"] = $this->xheaders["Reply-To"];
			else 
				$this->xheaders["Disposition-Notification-To"] = $this->xheaders['From'];
		}
		
		if($this->charset != '') {
			$this->xheaders["Mime-Version"] = "1.0";
			$this->xheaders["Content-Type"] = "$this->content_type; charset=$this->charset";
			$this->xheaders["Content-Transfer-Encoding"] = $this->ctencoding;
		}
	
		$this->xheaders["X-Mailer"] = "Php/Simpl";
		
		// Attach the files if there is any
		if(count($this->aattach) > 0) {
			$this->_build_attachement();
		}else{
			$this->fullBody = $this->body;
		}
	
		reset($this->xheaders);
		while(list($hdr,$value) = each($this->xheaders)) {
			if($hdr != 'Subject')
				$this->headers .= "$hdr: $value\n";
		}
	}
	
	/**
	 * Format and send the email
	 * 
	 * @return bool
	 */
	public function Send(){
		$this->BuildMail();
		
		$this->strTo = implode( ", ", $this->sendto );
		
		// envoie du mail
		$res = @mail( $this->strTo, $this->xheaders['Subject'], $this->fullBody, $this->headers );
		
		return $res;
	}
	
	/**
	 * Get the contents plus headers of the email
	 * 
	 * @return string
	 */
	public function Get(){
		$this->BuildMail();
		$mail = 'To: ' . implode( ", ", $this->sendto ) . "\n";
		$mail .= $this->headers . "\n";
		$mail .= $this->fullBody;
		return $mail;
	}
	
	/**
	 * Check an email address validity
	 * 
	 * @param $address String email address to check
	 * @return bool
	 */
	public function ValidEmail($address){
		// Get the email address out
		if (preg_match('!<(.*?)>!', $address, $regs))
			$address = $regs[1];
			
		// Check to see if it is in valid format
		return preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $email);
	}
	
	/**
	 * Check validity of email addresses
	 * 
	 * @param $aad Array of email addresses 
	 * @return bool
	 */
	public function CheckAdresses($aad){
		for($i=0;$i< count($aad); $i++){
			if(!$this->ValidEmail($aad[$i])){
				return false;
			}
		}
		
		return true;
	}
	
	
	/**
	 * Check and encode attach file(s)
	 * 
	 * @return null
	 */
	private function _build_attachement(){
		$this->xheaders["Content-Type"] = "multipart/mixed;\n boundary=\"$this->boundary\"";
	
		$this->fullBody = "This is a multi-part message in MIME format.\n--$this->boundary\n";
		$this->fullBody .= "Content-Type: " . $this->content_type . "; charset=$this->charset\nContent-Transfer-Encoding: $this->ctencoding\n\n" . $this->body ."\n";
		
		$sep = chr(13) . chr(10);
		
		$ata = array();
		$k = 0;
		
		// for each attached file, do...
		for($i=0; $i<count($this->aattach); $i++){
			
			$filename = $this->aattach[$i];
			$basename = basename($filename);
			$ctype = $this->actype[$i];	// content-type
			$disposition = $this->adispo[$i];
			
			if(!file_exists($filename)){
				echo "Class Mail, method attach : file $filename can't be found";
				exit;
			}
			$subhdr= "--$this->boundary\nContent-type: $ctype;\n name=\"$basename\"\nContent-Transfer-Encoding: base64\nContent-Disposition: $disposition;\n  filename=\"$basename\"\n";
			$ata[$k++] = $subhdr;
			// non encoded line length
			$linesz= filesize( $filename)+1;
			$fp= fopen( $filename, 'r' );
			$ata[$k++] = chunk_split(base64_encode(fread( $fp, $linesz)));
			fclose($fp);
		}
		$this->fullBody .= implode($sep, $ata);
	}
}
?>