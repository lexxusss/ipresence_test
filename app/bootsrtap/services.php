<?php

/**
 * @param mixed ...$vars
 * @return string
 */
function generateHashId(...$vars) {
    return md5(join('', $vars));
}

/**
 * @param $array
 * @param $paramName
 * @return array
 */
function mergeArrayItemsByParam($array, $paramName)
{
    $params = [];
    foreach ($array as $item) {
        foreach ($item[$paramName] as $key => $param) {
            $params[$key] = $param;
        }
    }

    return $params;
}

function isPolishIntent(\App\Model\QIntent $intent, $userData, $outputContexts) {
    return
        $intent->isPolish() ||
        (!empty($userData[\App\Model\QParam::INTENT_LANGUAGE]) && $userData[\App\Model\QParam::INTENT_LANGUAGE] == \App\Helpers\Locale::PL) ||
        hasPolishOutputContext($outputContexts);
}

/**
 * @param $outputContexts
 * @return bool
 */
function hasPolishOutputContext($outputContexts) {
    foreach ($outputContexts as $context) {
        if (array_key_exists('name', $context)) {
            if ($parts = explode('/', $context['name'])) {
                if ($name = last($parts)) {
                    if (substr($name, 0, strlen(\App\Model\QIntent::POLISH_INTENT_PREFIX_DF)) === \App\Model\QIntent::POLISH_INTENT_PREFIX_DF) {
                        return true;
                    }
                }
            }
        }
    }

    return false;
}

/**
 * Walk through $array and insert each $kk => $vv in its sub arrays
 *
 * @param $array
 * @param $keysValues
 * @param null $subKey
 * @return mixed
 */
function insertKeysValuesInEachArraySubArray($array, $keysValues, $subKey = null) {
    foreach ($array as &$v) {
        if (is_array($v)) {
            foreach ($keysValues as $kk => $vv) {
                if (!$subKey) {
                    $v[$kk] = $vv;
                } else {
                    $v[$subKey][$kk] = $vv;
                }
            }
        }
    }

    return $array;
}

/**
 * @param $array
 * @param $keysValues
 * @param null $subKey
 * @return mixed
 */
function setKeysValuesInEachArraySubArray($array, $keysValues, $subKey = null) {
    foreach ($array as &$v) {
        if (!$subKey) {
            $v = $keysValues;
        } else {
            $v[$subKey] = $keysValues;
        }
    }

    return $array;
}

function array_has_key_with_value($array, $key, $value) {
    foreach ($array as $subArr) {
        if ($value === ($subArr[$key] ?? null)) {
            return true;
        }
    }

    return false;
}

/**
 * Check whether $value is presented in at least one of $array's $keys
 *
 * @param $value
 * @param $array
 * @param $keys
 * @return bool
 */
function isValuePresentedInKeys($value, $array, $keys) {
    foreach ($keys as $key) {
        if (str_contains($array[$key], $value)) {
            return true;
        }
    }

    return false;
}

/**
 * @param array $array
 * @return int|null|string
 */
function getFirstNullValuesKey(array $array) {
    foreach ($array as $key => $item) {
        if (is_null($item)) {
            return $key;
        }
    }

    return null;
}

/**
 * @param array $array
 * @param bool $breakIfNullFound
 * @return int|null|string
 */
function getLastNotNullValuesKey(array $array, $breakIfNullFound = true) {
    $lastNotNullValuesKey = null;

    foreach ($array as $key => $item) {
        if (!is_null($item)) {
            $lastNotNullValuesKey = $key;
        } elseif ($breakIfNullFound) {
            break;
        }
    }

    return $lastNotNullValuesKey;
}

/**
 * @param \App\Http\Controllers\Controller $controller
 * @param $function
 * @return string
 * @throws ReflectionException
 */
function getApiSourceNameAlias(\App\Http\Controllers\Controller $controller, string $function): string {
    $classShortName = str_replace('Controller', '', (new \ReflectionClass($controller))->getShortName());

    return snake_case($classShortName . '_' .  $function);
}

/**
 * @param $need
 * @param $word
 * @return bool
 */
function soundsSimilar($need, $word) {
    return
        $need == $word ||
        \App\Helpers\Encoding::encodePolishToUtf8_Lower_Case($need) == \App\Helpers\Encoding::encodePolishToUtf8_Lower_Case($word) ||
        soundex($need) == soundex($word) ||
        levenshtein($need, $word) <= 3;
}

/*-- QParam seeds - helpers --*/
/**
 * @param $word
 * @return string
 */
function underscoreToSentence($word) {
    return ucfirst(strtolower(str_replace('_', ' ', $word)));
}

/**
 * @param $tt
 * @return string
 */
function buildTitleForParam($tt) {
    return 'What is your ' . lcfirst($tt) . ' ?';
}

/**
 * @param $text
 * @return bool
 */
function isNotOriginal($text) {
    return strpos($text, 'original') === false;
}
/*-- /QParam seeds - helpers --*/


/**
 * @param $string
 * @return bool
 */
function isJson($string) {
    json_decode($string);

    return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * @param $arr
 * @return \Illuminate\Support\Collection
 */
function collect_recursive($arr) {
    if (is_array($arr)) {
        foreach ($arr as &$props) {
            $props = collect_recursive($props);
        }

        $arr = collect($arr);
    }

    return $arr;
}

/**
 * @param $data
 * @return string
 */
function make_password_hash($data) {
    $pass = is_string($data)
        ? $data
        : ($data['password'] ?? generate_random_string());

    return \Hash::make($pass);
}

/**
 * @return string
 */
function generate_random_string() {
    return str_random(8);
}

/**
Chmods files and folders with different permissions.

This is an all-PHP alternative to using: \n
<tt>exec("find ".$path." -type f -exec chmod 644 {} \;");</tt> \n
<tt>exec("find ".$path." -type d -exec chmod 755 {} \;");</tt>

@param $path An either relative or absolute path to a file or directory
which should be processed.
@param $filePerm The permissions any found files should get.
@param $dirPerm The permissions any found folder should get.
@return Returns TRUE if the path if found and FALSE if not.
@warning The permission levels has to be entered in octal format, which
normally means adding a zero ("0") in front of the permission level. \n
More info at: http://php.net/chmod.
 */

function recursiveChmod($path, $filePerm=0644, $dirPerm=0755) {
    // Check if the path exists
    if (!file_exists($path)) {
        return(false);
    }

    // See whether this is a file
    if (is_file($path)) {
        // Chmod the file with our given filepermissions
        chmod($path, $filePerm);

        // If this is a directory...
    } elseif (is_dir($path)) {
        // Then get an array of the contents
        $foldersAndFiles = scandir($path);

        // Remove "." and ".." from the list
        $entries = array_slice($foldersAndFiles, 2);

        // Parse every result...
        foreach ($entries as $entry) {
            // And call this function again recursively, with the same permissions
            recursiveChmod($path."/".$entry, $filePerm, $dirPerm);
        }

        // When we are done with the contents of the directory, we chmod the directory itself
        chmod($path, $dirPerm);
    }

    // Everything seemed to work out well, return true
    return(true);
}

/**
 * @param $className
 * @return bool|string
 */
function getBaseNameClass($className) {
    return substr(strrchr($className, "\\"), 1);
}

/**
 * A str_replace_array for PHP
 *
 * As described in http://php.net/str_replace this wouldnot make sense
 * However there are chances that we need it, so often !
 * See https://wiki.php.net/rfc/cyclic-replace
 *
 * @author Jitendra Adhikari | adhocore <jiten.adhikary@gmail.com>
 *
 * @param string $search  The search string
 * @param array  $replace The array to replace $search in cyclic order
 * @param string $subject The subject on which to search and replace
 *
 * @return string
 */
function str_replace_array($search, array $replace, $subject)
{
    if (0 === $tokenc = substr_count($subject, $search)) {
        return $subject;
    }
    $string  = '';
    if (count($replace) >= $tokenc) {
        $replace = array_slice($replace, 0, $tokenc);
        $tokenc += 1;
    } else {
        $tokenc = count($replace) + 1;
    }
    foreach(explode($search, $subject, $tokenc) as $part) {
        $string .= $part.array_shift($replace);
    }
    return $string;
}

/**
 * @param $string
 * @param bool $capitalizeFirstCharacter
 * @return mixed|string
 */
function snakeCaseToCamelCase($string, $capitalizeFirstCharacter = false)
{
    $str = str_replace(['_', '-'], ' ', ucwords($string, '_-'));

    if (!$capitalizeFirstCharacter) {
        $str = lcfirst($str);
    }

    return $str;
}

/**
 * @param $string
 * @return string
 */
function snakeCaseToSpaceCase($string)
{
    $str = str_replace(['_', '-'], ' ', $string);

    return $str;
}
