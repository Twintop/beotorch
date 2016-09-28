<?php

abstract class BasicEnum {
    private static $constCacheArray = NULL;

private function __construct(){
      /*
        Preventing instance :)
      */
     }

    private static function getConstants() {
        if (self::$constCacheArray == NULL) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function isValidName($name, $strict = false) {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value) {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict = true);
    }
    
    public static function getName($gValue, $strict = false) {
        $constants = self::getConstants();
        
        foreach($constants as $key => $value)
        {
            if ($strict)
            {
                if ($value == $gValue)
                {
                    return $key;
                }
            }
            elseif (strtolower($value) == strtolower($gValue))
            {
                return $key;
            }
        }
        
        return false;
        /*$values = array_values(self::getConstants());
        $key = array_search($value, $values); 
        
        return $values[$key];
         */
    }
    
    public static function getValue($name, $strict = false) {
        $constants = self::getConstants();
        
        foreach($constants as $key => $value)
        {
            if ($strict)
            {
                if ($key == $name)
                {
                    return $value;
                }
            }
            elseif (strtolower($key) == strtolower($name))
            {
                return $value;
            }
        }
        
        return false;
        /*$keys = Array();
        $key = -1;
        
        if ($strict) {
            $keys = $constants;
            $key = array_search($name, $constants);            
        }
        else {
            $keys = array_map('strtolower', array_keys($constants));
            $key = array_search(strtolower($name), $keys);
        }
        
        //print "\n" . $name . " - " . $key . " = " . $keys[$key] . "\n";
        
        if ($key >= 0)
        {
            return $constants[$keys];
        }
        else
        {
            return -1;
        }*/
    }
}

?>
