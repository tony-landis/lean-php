<?php

/**
 * Array to Text Table Generation Class
 *
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class ArrayToTextTable
{
    /** 
     * @var <array> The array for processing
     */
    private static $rows;

    /** 
     * @var <int> The column width settings
     */
    private static $cs = array();

    /**
     * @var <int> The Row lines settings
     */
    private static $rs = array();

    /**
     * @var <int> Max Column Height (returns)
     */
    private static $mH = 2;

    /**
     * @var <int> Max Row Width (chars)
     */
    private static $mW = 30;

    private static $head  = false;
    private static $pcen  = "+";
    private static $prow  = "-";
    private static $pcol  = "|";

    /** Prepare array into textual format
     *
     * @param <array> $rows The input array
     * @param <bool> $head Show heading
     * @param <int> $maxWidth Max Column Height (returns)
     * @param <int> $maxHeight Max Row Width (chars)
     */
    public static function setData($rows, $head=true, $maxWidth=35, $maxHeight=2)
    {
        self::$rows =& $rows;
        self::$mW=$maxWidth;
        self::$mH=$maxHeight;
        self::$cs=array();
        self::$rs=array();

        // calculate row height and col width for each col/row
        foreach(self::$rows as $rowKey => &$row)
            foreach($row as $colKey => &$colVal)
                self::setMax($rowKey, $colKey, $colVal);

        // set the heading from the array keys
        if($head) self::setHeading();
    }

    /**
     * Prints the data to a text table
     *
     * @param <bool> $return Set to 'true' to return text rather than printing
     * @return mixed
     */
    public static function printText($return=false)
    {
        if($return) ob_start();

        self::printLine();
        self::printHeading();
        foreach(self::$rows as $rowKey => &$row)
            self::printRow($rowKey, $row);
        self::printLine(false);

        if($return) return ob_get_clean();
    }

    private function setHeading()
    {
        $data = array();
        $keys = array_keys(self::$rows[0]);
        foreach($keys as $colKey => $value)
        {
            self::setMax(false, $value, $value);
            $data[$value] = strtoupper($value);
        }

        if(!is_array($data)) return false;
        self::$head = $data;
    }

    private static function printLine($nl=true)
    {
        print self::$pcen;
        foreach(self::$cs as $key => $val)
            print self::$prow .
                str_pad('', $val, self::$prow, STR_PAD_RIGHT) .
                self::$prow .
                self::$pcen;
        if($nl) print "\n";
    }

    private static function printHeading()
    {
        if(!is_array(self::$head)) return false;

        print self::$pcol;
        foreach(self::$cs as $key => $val)
            print ' '.
                str_pad(@self::$head[$key], $val, ' ', STR_PAD_BOTH) .
                ' ' .
                self::$pcol;

        print "\n";
        self::printLine();
    }

    private static function printRow($rowKey, &$row)
    {
        // loop through each line
        for($line=1; $line <= self::$rs[$rowKey]; $line++)
        {
            print self::$pcol;
            foreach($row as $colKey => &$colVal)
            {
                print " ";
                print str_pad(substr($colVal, (self::$mW * ($line-1)), self::$mW), self::$cs[$colKey], ' ', STR_PAD_RIGHT);
                print " " . self::$pcol;
                $colKey ++;
            }
            print  "\n";
        }
    }

    private static function setMax($rowKey, $colKey, &$colVal)
    {
        $w = strlen($colVal);
        $h = 1;
        if($w > self::$mW)
        {
            $h = ceil($w % self::$mW);
            if($h > self::$mH) $h=self::$mH;
            $w = self::$mW;
        }

        if(!isset(self::$cs[$colKey]) || self::$cs[$colKey] < $w)
            self::$cs[$colKey] = $w;

        if($rowKey !== false && (!isset(self::$rs[$rowKey]) || self::$rs[$rowKey] < $h))
            self::$rs[$rowKey] = $h;
    }
}
?>