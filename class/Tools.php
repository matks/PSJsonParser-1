<?php

class Tools
{
    public static function extractNames($filename, $type)
    {
        if (strlen($filename) > 0) {
            $pattern = '/\/full\/(.*?)\/(.*)/';
            preg_match($pattern, $filename, $matches);
            if ($type == 'campaign') {
                return isset($matches[1]) ? $matches[1] : null;
            }
            if ($type == 'file') {
                return isset($matches[2]) ? $matches[2] : null;
            }
        } else {
            return null;
        }
    }

    public static function sanitize($text) {
        $StrArr = str_split($text);
        $NewStr = '';
        foreach ($StrArr as $Char) {
            $CharNo = ord($Char);
            if ($CharNo == 163) { $NewStr .= $Char; continue; }
            if ($CharNo > 31 && $CharNo < 127) {
                $NewStr .= $Char;
            }
        }
        return $NewStr;
    }

    public static function buildTree(array &$suites, $parentId = null) {
        $branch = array();
        foreach ($suites as &$suite) {

            if ($suite->parent_id == $parentId) {
                $children = Tools::buildTree($suites, $suite->id);
                if ($children) {
                    $suite->suites = $children;
                }
                $branch[$suite->id] = $suite;
                unset($suite);
            }
        }
        return $branch;
    }

    public static function removeExtension($filename)
    {
        return substr($filename, 0, strrpos($filename, "."));
    }

    public static function format_duration($duration) {
        if ($duration != 0) {
            $secs = round($duration/1000, 2);

            $return = '';

            $minutes = floor(($secs / 60) % 60);
            if ($minutes > 0) {
                $return .= $minutes.'m';
            }
            $return .= $secs.'s';
            return $return;
        }
    }

    public static function format_datetime($value) {
        return date('Y-m-d H:i:s', strtotime($value));
    }

}