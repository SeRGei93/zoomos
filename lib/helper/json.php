<?php

namespace ZMS\Helper;


class Json
{
    private static $charset = "utf-8";

    /**
     * @param string $stringJson
     * @return array|null
     */
    public static function decode(&$stringJson)
    {
        $result = \json_decode($stringJson, $assoc = true);
        if ($result === null && \json_last_error() !== JSON_ERROR_NONE) {

            return [];
        }

        if (strtolower(LANG_CHARSET) != self::$charset) {
            self::charSet($result);
        }

        return $result;
    }

	private static function getLastErrorMsg() 
	{
		$errorCode = json_last_error();

		$errors = [
			JSON_ERROR_NONE => 'No errors',
			JSON_ERROR_DEPTH =>'Maximum stack depth reached',
			JSON_ERROR_STATE_MISMATCH => 'Incorrect bits or mode mismatch',
			JSON_ERROR_CTRL_CHAR => 'Invalid control character',
			JSON_ERROR_SYNTAX => 'Syntax error, invalid JSON',
			JSON_ERROR_UTF8 => 'Incorrect UTF-8 characters, possibly incorrectly encoded'
		];

		$errorMsg = isset($errors[$errorCode])
			? $errors[$errorCode]
			: 'Unknown error';

		return $errorMsg . ' [' . $errorCode . '] - ' . \json_last_error_msg() . '.';
	}

    /** @param $result */
    private static function charSet(&$result)
    {
        if (is_array($result)) {
            foreach ($result as &$value) {
                self::charSet($value);
            }
        } else {
            $result = iconv(self::$charset, LANG_CHARSET, $result);
        }
    }
}
