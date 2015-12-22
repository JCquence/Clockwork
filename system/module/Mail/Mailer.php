<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Mail
     * @author Jelle van der Coelen
     * @package Clockwork/Module/Mailer
     */
    class Mailer
    {
        /**
         * PHPMailer object.
         * @var object
         */
        private $mailer;
        
        /**
         * Holds message.
         * @var string
         */
        private $message;
        
        /**
         * Subject of message.
         * @var string
         */
        private $subject;
        
        /**
         * Send to address.
         * @var string
         */
        private $to;
        
        /**
         * Constructor.
         *
         * @param string $to      Send to this address.
         * @param string $subject Subject of message.
         * @param string $message Message to send.
         *
         * @return void
         */
        public function __construct($to, $subject = '', $message = '')
        {            
            $this->to      = $to;
            $this->subject = $subject;
            $this->message = $message;

            $this->init();
        }
        
        /**
         * Initialize.
         *
         * @return void
         */
        private function init()
        {
            include_once MODULE_DIR.'Mail/phpmailer/class.phpmailer.php';
            
            $this->mailer = new PHPMailer();
        }
        
        /**
         * Send message. Omit parameters to get defaults form Config.
         *
         * @param string $from     From email address.
         * @param string $fromname From name.
         * @param string $bcc      Send message BCC to this address.
         *
         * @return boolean
         */
        public function send($from = null, $fromname = null, $bcc = null)
        {
            $this->mailer->From     = ($from ? $from : Config::getSetting('mail_from'));
            $this->mailer->FromName = ($fromname ? $fromname : Config::getSetting('mail_from_name'));
            $this->mailer->Subject  = $this->subject;

            $this->mailer->IsHTML(true);                
            
            $this->mailer->Body = $this->message;    

            $this->mailer->AddAddress($this->to);

            if($bcc)
                $this->mailer->AddBCC($bcc);

            return $this->mailer->Send();
        }
    }
?>