<?php
class Parameter {

    // request parameter names
    public static $SSOUSERNAME = 'ssousername';
    public static $USERNAME = 'username';
    public static $HASH = 'hash';
    public static $HASHTYPE = 'hashtype';
    public static $HASHENCODING = 'hashencoding';
    public static $TOKEN = 'token';
    public static $ACTIONREDIRECT = 'actionredirect';
    public static $URL = 'url';
	public static $LMS = 'lms';
    public static $LOCALE = 'locale';
    public static $LAYOUT = 'layout';
    public static $ALIASID = 'aliasid';
    public static $ITEMID = 'itemid';
    public static $ALIASNAME = 'aliasname';
    public static $ITEMNAME = 'itemname';
    public static $BUREAULABEL = 'bureaulabel';
    public static $VIEWTYPE = 'viewtype';
    public static $VIEWITEMPATH = 'viewitempath';
    public static $DISPLAYMESSAGE = 'displaymessage';
    public static $THEME = 'theme';

    // request parameter values
    public static $VALUE_RESETURL = 'default';
    public static $VALUE_DISPLAYMESSAGE = 'true';

    /** Checks if a parameter is empty
     *
     * @param parameter any string
     * @return boolean true if null or empty
     */
    public static function isEmpty($parameter)
    {
       $isEmpty = TRUE;
       if (isset($parameter) && strlen(trim($parameter))) {
           $isEmpty = FALSE;
       }
       return $isEmpty;
    }

    /** Checks if a parameter is not empty
     *
     * @param parameter any string
     * @return boolean true if not empty
     */
    public static function isNotEmpty($parameter)
    {
       return !self::isEmpty($parameter);
    }

    /** Trims a parameter
     *
     * @param parameter any string
     * @return String the trimmed parameter otherwise empty string
     */
    public static function trim($parameter) {
        return !isset($parameter)?"":trim($parameter);
    }
}
?>