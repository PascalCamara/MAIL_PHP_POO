<?php

class Mail {

    private $_nom_expediteur;
    private $_mail_expediteur;
    private $_mail_replyto;
    private $_mails_destinataires;
    private $_mails_bcc;
    private $_objet;
    private $_texte;
    private $_html;
    private $_fichiers;
    private $_frontiere;
    private $_headers;
    private $_message;

    /**
     * Mail constructor.
     * @param $_nom_expediteur
     * @param $_mail_expediteur
     * @param $_mail_replyto
     */
    public function __construct($_nom_expediteur, $_mail_expediteur, $_mail_replyto = false)
    {

        if (!$_mail_replyto) $_mail_replyto =  $_mail_expediteur;

        if (!self::_validateEmail($_mail_expediteur))
            throw new InvalidArgumentException("Mail expéditeur invalide !");

        if (!self::_validateEmail($_mail_replyto))
            throw new InvalidArgumentException("Mail replyto invalide !");

        $this->_nom_expediteur = $_nom_expediteur;
        $this->_mail_expediteur = $_mail_expediteur;
        $this->_mail_replyto = $_mail_replyto;

        $this->_mails_destinataires = "";
        $this->_mails_bcc = "";
        $this->_objet = "";
        $this->_texte = "";
        $this->_html = "";
        $this->_fichiers = "";
        $this->_frontiere = md5(uniqid(mt_rand()));
        $this->_headers = "";
        $this->_message = "";
    }


    /**
     * @param $email
     * @return mixed
     */
    public static function _validateEmail ($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param $email
     */
    public function ajouter_destinataire ($email) {
        if (!self::_validateEmail($email))
            throw new InvalidArgumentException("Mail destinataire invalide !");

        if ($this->_mails_destinataires == '')
            $this->_mails_destinataires = $email;
        else
            $this->_mails_destinataires .= ';' .$email;
    }

    /**
     * @param $email
     */
    public function ajouter_bbc($email) {
        if (!self::_validateEmail($email))
            throw new InvalidArgumentException("Mail BBC invalide !");

        if ($this->_mails_bcc == '')
            $this->_mails_bcc = $email;
        else
            $this->_mails_bcc .= ';' .$email;
    }

    /**
     * @param $fichier
     */
    public function ajouter_pj($fichier) {

        if (!file_exists($fichier))
            throw new InvalidArgumentException('Piece joint inexistante !');

        if ($this->_fichiers == '')
            $this->_fichiers = $fichier;
        else
            $this->_fichiers .= ";$fichier";
    }

    /**
     * @param $objet
     * @param $texte
     * @param $html
     */
    public function contenu ($objet, $texte, $html) {
        $this->_objet = $objet;
        $this->_texte = $texte;
        $this->_html = $html;
    }

    public function envoyer() {
        $this->_headers = 'From: "'.$this->_nom_expediteur.'" <'.$this->_mail_expediteur.'>'."\n";
        $this->_headers .= 'Return-Path: <'.$this->_mail_replyto.'>'."\n";
        $this->_headers .= 'MIME-Version: 1.0 ' ."\n";

        if ($this->_mails_bcc != '')
            $this->_headers .= "Bcc: " . $this->_mails_bcc . "\n";

        $this->_headers .= 'Content-Type: multipart/mixed; boundary="' . $this->_frontiere . '"';

        if ($this->_texte != '') {
            $this->_message .= '--'.$this->_frontiere."\n";
            $this->_message .= 'Content-Type: text/plain; charset="utf-8"'. "\n";
            $this->_message .= 'Content-Transfer-Encoding: 8bit' . "\n\n";
            $this->_message .= $this->_texte . "\n\n";
        }

        if ($this->_html != '') {
            $this->_message .= '--'.$this->_frontiere . "\n";
            $this->_message .= 'Content-Type: text/html; charset="utf-8"'. "\n";
            $this->_message .= 'Content-Transfer-Encoding: 8bit' . "\n\n";
            $this->_message .= $this->_html . "\n\n";
        }

        if ($this->_fichiers != '') {
            $tab_fichiers = explode(';', $this->_fichiers);
            $nb_fichiers = count($tab_fichiers);

            for ($i=0;$i<$nb_fichiers;$i++) {
                $this->_message .= '--'. $this->_frontiere . "\n";
                $this->_message .= 'Content-Type: '. mime_content_type($tab_fichiers[$i]) .'; name="' . $tab_fichiers[$i] . '"' ."\n";
                $this->_message .= 'Content-Disposition:attachment; filename="'.$tab_fichiers[$i] . '"' . "\n\n";
                $this->_message .= chunk_split(base64_encode(file_get_contents($tab_fichiers[$i]))) . "\n\n";
            }
        }

        if (!mail($this->_mails_destinataires, $this->_objet, $this->_message, $this->_headers))
            throw new Exception("Envoi de mail échoué !");

    }

    public function __destruct()
    {
       var_dump($this);
    }
}
