<?php
error_reporting(E_ALL);
spl_autoload_register(function ($className) {
      $class = '../class/' . $className . '.php';
      if (file_exists($class)) {
            require_once $class;
      }
});

