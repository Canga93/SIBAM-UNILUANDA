<?php
// Configuração de email real usando PHPMailer
// Requer instalação via Composer: composer require phpmailer/phpmailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        try {
            // Configurações do servidor
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.uniluanda.edu.ao';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'sibam@uniluanda.edu.ao';
            $this->mail->Password = 'sua_senha_de_email';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;
            $this->mail->CharSet = 'UTF-8';
            
            // Remetente
            $this->mail->setFrom('sibam@uniluanda.edu.ao', 'SIBAM UNILUANDA');
            
        } catch (Exception $e) {
            error_log("Erro ao configurar email: " . $e->getMessage());
        }
    }
    
    public function sendResetEmail($to_email, $to_name, $token) {
        try {
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
            
            // Destinatário
            $this->mail->addAddress($to_email, $to_name);
            
            // Conteúdo
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Redefinição de Senha - SIBAM UNILUANDA';
            
            $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                    .content { background: #f8f9fa; padding: 30px; }
                    .button { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
                    .footer { background: #343a40; color: white; padding: 20px; text-align: center; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>SIBAM UNILUANDA</h1>
                        <p>Sistema Integrado de Busca e Acesso a Monografias</p>
                    </div>
                    
                    <div class='content'>
                        <h2>Redefinição de Senha</h2>
                        <p>Olá <strong>$to_name</strong>,</p>
                        <p>Você solicitou a redefinição de sua senha no SIBAM UNILUANDA.</p>
                        <p>Clique no botão abaixo para redefinir sua senha:</p>
                        
                        <p style='text-align: center; margin: 30px 0;'>
                            <a href='$reset_link' class='button'>Redefinir Senha</a>
                        </p>
                        
                        <p>Ou copie e cole este link no seu navegador:</p>
                        <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 5px;'>
                            $reset_link
                        </p>
                        
                        <p><strong>Este link expira em 1 hora.</strong></p>
                        
                        <p>Se você não solicitou esta redefinição, ignore este email. Sua senha permanecerá inalterada.</p>
                    </div>
                    
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " SIBAM UNILUANDA. Todos os direitos reservados.</p>
                        <p>Universidade UNILUANDA - Luanda, Angola</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $this->mail->Body = $message;
            
            // Texto alternativo para clientes de email que não suportam HTML
            $this->mail->AltBody = "Redefinição de Senha - SIBAM UNILUANDA\n\n" .
                                  "Olá $to_name,\n\n" .
                                  "Você solicitou a redefinição de sua senha. " .
                                  "Use o link abaixo para redefinir sua senha:\n\n" .
                                  "$reset_link\n\n" .
                                  "Este link expira em 1 hora.\n\n" .
                                  "Se você não solicitou esta redefinição, ignore este email.";
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $this->mail->ErrorInfo);
            return false;
        }
    }
}

// Uso:
// $email_sender = new EmailSender();
// $email_sender->sendResetEmail('usuario@email.com', 'Nome Usuário', $token);
?>