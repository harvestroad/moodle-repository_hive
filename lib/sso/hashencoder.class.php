<?php
class HashEncoder {

    /**
     * Calculates the MD5 or SHA digest and returns the value as a 32 character 
     * hex string.
     *
     * @param string token the token to be hashed along the secret string
     * @param string secretstring the secret string to be hashed along with the token
     * @param string type type of the hash (MD5 or SHA)
     * @param string encoding bytes encoding
     * @return MD5 digest as a hex string
     */
    public static function hash($token, $secretstring, $type, $encoding) {
        $hash = '';
        $buf = $token.$secretstring;
        if (strcasecmp($type,'MD5') == 0) {
            $hash = md5($buf);
        } else if (strcasecmp($type,'SHA') == 0)  {
          $hash = sha1($buf);
        }
        return $hash;
    }
}
?>