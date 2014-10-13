<?php
/**
 * This file contains base library & shim functions for Yii framework.
 *
 * @author Ger Hobbelt <ger@hobbelt.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */





/**
 * Internal helper function which produces the list of static member variables of the given 
 * class instance. 
 *
 * @param  (any) $object  class instance
 *
 * @return array          list of static member variable names of the given class instance. May be empty.
 */
function get_class_static_vars($object) 
{ 
    $cname = get_class($object);
    $cvars = get_class_vars($cname);
    $ovars = get_object_vars($object);
    return array_diff_key($cvars, $ovars); 
} 






define('VAR_DUMP_EX_MAX_SUBITEMS_DEFAULT', 0x7FFFFFFF);
define('VAR_DUMP_EX_MAX_LEVELS', 10);



/**
 * Extended version of `var_dump()`.
 * 
 * Derived from code by phella.net:
 *
 *   http://nl3.php.net/manual/en/function.var-dump.php
 *
 * @return string Plaintext of HTML formatted string describing the content (type and value) of the @value parameter.
*/
function var_dump_ex($value, $level = 0, $sort_before_dump = 0, $show_whitespace = true, $max_subitems = VAR_DUMP_EX_MAX_SUBITEMS_DEFAULT, $show_as_HTML = true)
{
  if ($show_as_HTML)
  {
    if ($show_whitespace)
    {
      $trans = array(
        ' '   => '&there4;',
        "\t"  => '&rArr;',
        "\n"  => '&para;',
        "\r"  => '&lArr;',
        "\0"  => '&oplus;'
      );

      $fmt = function ($str, $tag = null) use ($trans)
      {
        if (empty($tag))
        {
          return strtr(htmlspecialchars($str, ENT_COMPAT, 'UTF-8'), $trans);
        }
        else
        {
          return '<' . $tag . '>' . strtr(htmlspecialchars($str, ENT_COMPAT, 'UTF-8'), $trans) . '</' . $tag . '>';
        }
      };
    }
    else
    {
      $fmt = function ($str, $tag = null)
      {
        if (empty($tag))
        {
          return htmlspecialchars($str, ENT_COMPAT, 'UTF-8');
        }
        else
        {
          return '<' . $tag . '>' . htmlspecialchars($str, ENT_COMPAT, 'UTF-8') . '</' . $tag . '>';
        }
      };
    }
  }
  else
  {
    if ($show_whitespace)
    {
      $trans = array(
        ' '   => ' ',
        "\t"  => '[TAB]',
        "\n"  => '[LF]',
        "\r"  => '[CR]',
        "\0"  => '[NUL]'
      );

      $fmt = function ($str, $tag) use ($trans)
      {
        return strtr($str, $trans);
      };
    }
    else
    {
      $fmt = function ($str, $tag)
      {
        return $str;
      };
    }
  }

  if ($level == -1)
  {
    return $fmt($value, null);
  }

  $rv = '';
  if ($level == 0 && $show_as_HTML)
  {
    $rv .= '<pre>';
  }
  $type = gettype($value);
  $rv .= $type;

  switch ($type)
  {
  case 'string':
    $rv .= '(' . strlen($value) . ') ';
    $rv .= $fmt($value, 'b');
    break;

  case 'boolean':
    $rv .= ' ' . ($value ? 'true' : 'false');
    break;

  case 'object':
    $props = get_object_vars($value);
    if ($sort_before_dump > $level)
    {
      ksort($props);
    }
    $classname = get_class($value);
    $rv .= '(' . count($props) . ') ' . $fmt($classname, 'u');
    if ($level > VAR_DUMP_EX_MAX_LEVELS)
    {
      $rv .= "\n" . str_repeat("\t", $level + 1) . ($show_as_HTML ? '<i>(... deeper structure ...)</i>' : '(... deeper structure ...)');
      break;
    }
    foreach($props as $key => $val)
    {
      $rv .= "\n" . str_repeat("\t", $level + 1) . $fmt($key, null) . ' => ';
      $rv .= var_dump_ex($value->{$key}, $level + 1, $sort_before_dump, $show_whitespace, $max_subitems, $show_as_HTML);
    }

    $staticprops = get_class_static_vars($value);
    if ($sort_before_dump > $level)
    {
      ksort($staticprops);
    }
    $rv .= '+STATIC(' . count($staticprops) . ') ' . $fmt($classname, 'u');
    try
    { 
      $srv = '';
      foreach($staticprops as $key => $val)
      {
        $srv .= "\n" . str_repeat("\t", $level + 1) . 'static ' . $fmt($key, null) . ' => ';
        $srv .= @var_dump_ex($value->{$key}, $level + 1, $sort_before_dump, $show_whitespace, $max_subitems, $show_as_HTML);
      }
    }
    catch (Exception $ex) 
    {
      // very probably (bleeping) Smarty kicking up a ruckus.
      foreach($staticprops as $key => $val)
      {
        $srv .= "\n" . str_repeat("\t", $level + 1) . 'static ' . $fmt($key, null) . ' ...';
      }
    }
    $rv .= $srv;
    break;

  case 'array':
    if ($sort_before_dump > $level)
    {
      $value = array_merge($value); // fastest way to clone the input array
      ksort($value);
    }
    $rv .= '(' . count($value) . ')';
    if ($level > VAR_DUMP_EX_MAX_LEVELS)
    {
      $rv .= "\n" . str_repeat("\t", $level + 1) . ($show_as_HTML ? '<i>(... deeper structure ...)</i>' : '(... deeper structure ...)');
      break;
    }
    $count = 0;
    foreach($value as $key => $val)
    {
      $rv .= "\n" . str_repeat("\t", $level + 1) . $fmt($key, null) . ' => ';
      $rv .= var_dump_ex($val, $level + 1, $sort_before_dump, $show_whitespace, $max_subitems, $show_as_HTML);
      $count++;
      if ($count >= $max_subitems)
      {
        $rv .= "\n" . str_repeat("\t", $level + 1) . ($show_as_HTML ? '<i>(' . (count($value) - $count) . ' more entries ...)</i>' : '(' . (count($value) - $count) . ' more entries ...)');
        break;
      }
    }
    break;

  default:
    $rv .= ' ' . $fmt($value, 'b');
    break;
  }
  if ($level == 0 && $show_as_HTML)
  {
    $rv .= '</pre>';
  }
  return $rv;
}





/**
 * Return string describing the format and content of the passed $value argument using `var_dump_ex()`: 
 * `var_dump_ex_txt()` is a shorthand function which invokes `var_dump_ex()` to produce a 
 * plaintext formatted description of the $value argument.
 *
 * @param  (any) $value  Input value which will be described
 *
 * @return string        Plaintext formatted string describing the contents of $value.
 */
function var_dump_ex_txt($value)
{
  return var_dump_ex($value, 0, 0, false, VAR_DUMP_EX_MAX_SUBITEMS_DEFAULT, false);
}


