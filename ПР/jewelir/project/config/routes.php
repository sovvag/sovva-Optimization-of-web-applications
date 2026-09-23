<?php
	use \Core\Route;
	
	return [
		
		new Route('/', 'product', 'index'), // Главная 
		new Route('/products/', 'product', 'index'), // Изделия
		new Route('/products/show/:id/', 'product', 'show'), // Изделия
		new Route('/materials/', 'material', 'index'), // Материалы
		new Route('/sales/', 'sale', 'index'), // Продажи
	];
	
