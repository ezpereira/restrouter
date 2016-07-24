<?php

namespace ezrouter;

interface Routes
{
	public function get();
	public function post();
	public function error();
}

class Router
{
	protected $r;

	private function getMethod()
	{
		return (isset($_SERVER['REQUEST_METHOD'])) ? 
					strtolower($_SERVER['REQUEST_METHOD']) : 'get';
	}

	private function getURI()
	{
		return (isset($_SERVER['REQUEST_URI'])) ? 
					parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/';
	}

	private function getPathInfo()
	{
		return (isset($_SERVER['PATH_INFO'])) ? 
					$_SERVER['PATH_INFO'] : null;
	}

	private function getOrigPathInfo()
	{
		return (isset($_SERVER['ORIG_PATH_INFO'])) ? 
					$_SERVER['ORIG_PATH_INFO'] : null;
	}

	private function getPath()
	{
		$path      = $this->getURI();
        $path_info = $this->getPathInfo();
        $path_orig = $this->getOrigPathInfo();

        if (!empty($path_info)) {
            $path = $path_info;
        } elseif (!empty($path_orig) && $path_orig !== '/index.php') {
            $path = $path_orig;
        }

        return $path;
	}

	private function getTokens()
	{
		return array(
            	':s' => '([a-zA-Z]+)',
                ':string' => '([a-zA-Z]+)',
                ':n' => '([0-9]+)',
                ':number' => '([0-9]+)',
                ':a'  => '([a-zA-Z0-9-_]+)',
                ':alpha'  => '([a-zA-Z0-9-_]+)'
            );
	}

	private function matchRoutes($path, $routes)
	{
		$tokens   = $this->getTokens();
		$callback = null;
        $params   = array(); 

        foreach ($routes as $pattern => $handler_name) {
            $pattern = strtr($pattern, $tokens);
            preg_match('#^/?' . $pattern . '/?$#', $path, $matches);
            if (!empty($matches)) {
                $callback = $handler_name;
                $params   = $matches;
                break;
            }
        }
        unset($params[0]);
        if(!$callback) {
        	$this->r->error()['404']();
        }

        if(is_callable($callback) && $params) {
        	call_user_func_array($callback, $params);
	    } elseif (isset($callback[0]) && is_callable($callback[0])) {
	      	call_user_func_array($callback[0], $params);
	    }
	}

	public function run(Routes $routes)
	{
		$this->r = $routes;
		$method  = $this->getMethod();
		$path    = $this->getPath();

		$routes  = $this->r->{$method}();
		$route   = array_key_exists($path, $routes) ? $routes[$path] : null;

		if ($route) {
            return $route;
        } elseif ($routes) {
            $this->matchRoutes($path, $routes);
        }
	}
}