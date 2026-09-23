<?php
	namespace Core;
	
	class Router
	{
		public function getTrack($routes, $uri)
		{
			foreach ($routes as $route) {
				$pattern = $this->createPattern($route->path); // see method description
				
				/*
					Check if the URI matches the regular expression
					If the URI matches the regex, $params will contain the parameters
				*/
				if (preg_match($pattern, $uri, $params)) {
					$params = $this->clearParams($params);  // clean parameters from elements with numeric keys
					return new Track($route->controller, $route->action, $params);
				}
			}
			
			return new Track('error', 'notFound');
		}
		
		/*
			Method converts the path from the route into a regex pattern,
			substituting named capture groups for the route parameters.
		
			For example, from the address '/test/:var1/:var2/' the method
			will create the regex '#^/test/(?<var1>[^/]+)/(?<var2>[^/]+)/?$#'
		*/
		private function createPattern($path)
		{
			return '#^' . preg_replace('#/:([^/]+)#', '/(?<$1>[^/]+)', $path) . '/?$#';
		}
		
		private function clearParams($params)
		{
			$result = [];
			
			foreach ($params as $key => $param) {
				if (!is_int($key)) {
					$result[$key] = $param;
				}
			}
			
			return $result;
		}
	}
	