<?php
namespace Fuel\Core;

use Fuel\Core\Autoloader;
use Go\ParserReflection\LocatorInterface;

/**
 * fuel/coreのいくつかのclassがファイル名と
 * class名が一致しないため、COREPATH . bootstrap.phpのマッピングを
 * 再利用するため、Autoloaderを継承して使う。
 */
class Locator extends Autoloader implements LocatorInterface
{
  public function locateClass($className)
  {
    $className = ltrim($className, '\\');

    if (isset(static::$classes[strtolower($className)])) {
      // class名でPATHをAutoloaderに登録済み

      return str_replace('/', DS, static::$classes[strtolower($className)]);
    } elseif ($full_class = static::find_core_class($className)) {
      // coreとしてnamspeaceをAutoloaderに登録済み

      return static::prep_path(static::$classes[strtolower($full_class)]);
    } else {
      $pos = strripos($class, '\\');
      $full_namespace = substr($className, 0, $pos);

      if ($full_namespace) {
        // 登録済みのnaemspaceを探す
        // namespace名がPSR-0に従わない場合などに事前に登録しておく

        foreach (static::$namespaces as $namespace => $path) {
          $namespace = ltrim($namespace, '\\');
          if (stripos($full_namespace, $namespace) === 0) {
            $path .= static::class_to_path(
              substr($className, strlen($namespace) + 1),
              array_key_exists($namespace, static::$psr_namespaces)
            );

            if (is_file($path)) {
              return $path;
            }
          }
        }
      }

      // class名をnamespaceに沿ってAPPPATH以下を探す
      return APPPATH . 'classes' . DS . static::class_to_path($className);
    }
  }
}
