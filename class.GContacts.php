<?php

class GContacts
{
    // assigning all the variables
    protected static $details = array(), $code, $token, $emailList, $emailArray = array();

    // using constructor to get the Details
    public function __construct($client_id, $client_secret, $redirect, $max_results)
    {
        self::$details['client_id']     = $client_id;
        self::$details['client_secret'] = $client_secret;
        self::$details['redirect']      = $redirect;
        self::$details['max_results']   = $max_results;
    }

    // made a static function to get the contents using CURL
    protected static function getContents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    // made a function to post the details and get the content using CURL
    protected static function postContents($url, $fields)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    // displaying the authentication URL
    public static function getURL()
    {
        $url = 'https://accounts.google.com/o/oauth2/auth?client_id=' . self::$details['client_id'] . '&redirect_uri=' . self::$details['redirect'] . '&scope=https://www.google.com/m8/feeds/&response_type=code';
        return $url;
    }

    // displaying the imported email list
    public static function getList($code)
    {
        self::$code = htmlentities($code, ENT_QUOTES, 'UTF-8');

        $post = array(
            'code'          => self::$code,
            'client_id'     => self::$details['client_id'],
            'client_secret' => self::$details['client_secret'],
            'redirect_uri'  => self::$details['redirect'],
            'grant_type'    => 'authorization_code',
        );

        // getting the JSON file and decoding it
        if ($access = json_decode(self::postContents('https://accounts.google.com/o/oauth2/token', $post), true)) {
            self::$token = $access['access_token'];
            $url         = 'https://www.google.com/m8/feeds/contacts/default/full?max-results=' . self::$details['max_results'] . '&oauth_token=' . self::$token;

            if ($content = self::getContents($url)) {
                // parsing XML objects
                $xml = new SimpleXMLElement($content);
                // registering the path
                $xml->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');
                self::$emailList = $xml->xpath('//gd:email');

                foreach (self::$emailList as $email) {
                    self::$emailArray[] = $email->attributes()->address;
                }

                return self::$emailArray; // returning the email list array.
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    // counts the number or email imported
    public static function countEmails()
    {
        $number = count(self::$emailArray);
        return $number;
    }

    // checking whether the specified email is there in the imported list or emails
    public static function emailExists($emailToMatch)
    {
        foreach (self::$emailArray as $email) {
            if ($emailToMatch == $email) {
                return true;
            }
        }

        return false;
    }

}
