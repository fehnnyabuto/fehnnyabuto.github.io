<?php
class PHP_Email_Form {
    public $to = '';
    public $from_name = '';
    public $from_email = '';
    public $subject = '';
    public $smtp = array();
    public $ajax = false;
    private $messages = array();
    
    // Add message to email body
    public function add_message($content, $label = '', $priority = 0) {
        $this->messages[] = array(
            'content' => $content,
            'label' => $label,
            'priority' => $priority
        );
    }
    
    // Send the email
    public function send() {
        // Headers
        $headers = "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
        $headers .= "Reply-To: " . $this->from_email . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        // Message body
        $message = "<html><body>";
        $message .= "<h2>" . $this->subject . "</h2>";
        
        foreach ($this->messages as $msg) {
            if (!empty($msg['label'])) {
                $message .= "<strong>" . $msg['label'] . ":</strong> ";
            }
            $message .= nl2br(htmlspecialchars($msg['content'])) . "<br><br>";
        }
        $message .= "</body></html>";
        
        // If SMTP is configured
        if (!empty($this->smtp) && isset($this->smtp['host'])) {
            return $this->send_smtp($message);
        } else {
            // Use PHP mail function
            $result = mail($this->to, $this->subject, $message, $headers);
            return $this->ajax ? ($result ? 'OK' : 'Error') : $result;
        }
    }
    
    // SMTP email sending method
    private function send_smtp($message) {
        $smtp_host = $this->smtp['host'];
        $smtp_username = $this->smtp['username'];
        $smtp_password = $this->smtp['password'];
        $smtp_port = $this->smtp['port'];
        
        // Using PHPMailer for SMTP
        require_once 'PHPMailer/PHPMailer.php';
        require_once 'PHPMailer/SMTP.php';
        require_once 'PHPMailer/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        
        try {
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_username;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $smtp_port;
            
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($this->to);
            $mail->addReplyTo($this->from_email, $this->from_name);
            
            $mail->isHTML(true);
            $mail->Subject = $this->subject;
            $mail->Body = $message;
            
            $result = $mail->send();
            return $this->ajax ? ($result ? 'OK' : 'Error') : $result;
        } catch (Exception $e) {
            return $this->ajax ? 'Error: ' . $mail->ErrorInfo : false;
        }
    }
}