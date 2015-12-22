<?php
    // --- include PHP Mailer
    include_once MODULE_DIR.'Mail/phpmailer/class.phpmailer.php';
    
    /**
     * Template Mailer
     * @author Jelle van der Coelen
     * @package Clockwork/Module/Mailer
     */
    class TemplateMailer extends PHPMailer
    {
        /**
         * Template to load.
         * @var string
         */
        private $template = 'default';
        
        /**
         * Holds vars.
         * @var array
         */
        private $vars = array();
        
        /**
         * Fetch template HTML and parse vars.
         *
         * @param array $vars Vars to pass to template.
         *
         * @return string
         */
        private function fetchTemplate($vars = array())
        {
            $html = rfile_get_contents(appdir('template/mail/'.$this->template.'.html'));
            
            foreach($this->vars as $key => $var)
                $html = str_replace('{{ '.$key.' }}', $var, $html);
            
            $css = array();
            preg_match_all('/\{\%css\:\ (.*?)\ \%\}/', $html, $css);
            
            if(!empty($css[1][0]))
            {
                $style = rfile_get_contents(root('www/asset/css/'.$css[1][0]));
                $html = str_replace($css[0][0], '<style>'.$style.'</style>', $html);
            }
            
            return $html;
        }
        
        /**
         * Parse template and send mail.
         *
         * @param string $to Send to this address.
         *
         * @return boolean
         */
        public function sendMail($to, $subject, $from = null, $fromname = null)
        {
            $this->From     = ($from ? $from : Config::getSetting('mail_from'));
            $this->FromName = ($fromname ? $fromname : Config::getSetting('mail_from_name'));
            $this->Subject  = $subject;

            $this->IsHTML(true);
            $this->Body = $this->fetchTemplate();

            $this->AddAddress($to);
            return $this->Send();
        }
        
        /**
         * Set template to load.
         *
         * @param string $template Template to load.
         *
         * @return void
         */
        public function setTemplate($template){ $this->template = $template; }
        
        /**
         * Set vars to pass to template.
         *
         * @param array $vars Vars to pass.
         *
         * @return void
         */
        public function setVars($vars){ $this->vars = $vars; }
    }
?>