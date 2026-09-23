<?php
	namespace Core;
	
	class Controller
	{
		public $title = '';
		protected $layout = 'default';
		
		protected function render($view, $data = []) {
			return new Page($this->layout, $this->title, $view, $data);
		}
	}
