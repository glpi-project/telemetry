<?php
namespace App\Middleware;

class CsrfView extends Middleware {

   public function __invoke($request, $response, $next) {
      // CSRF token names and values
      $nameKey  = $this->container->csrf->getTokenNameKey();
      $valueKey = $this->container->csrf->getTokenValueKey();
      $name     = $request->getAttribute($nameKey);
      $value    = $request->getAttribute($valueKey);

      // append global var to view wich render two input hidden for csrf check
      $this->container->view->getEnvironment()->addGlobal('csrf', [
         'field' => '
            <input type="hidden" name="'.$nameKey.'" value="'.$name.'">
            <input type="hidden" name="'.$valueKey.'" value="'.$value.'">
         ',
      ]);

      return $next($request, $response);
   }
}
