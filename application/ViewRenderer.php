<?php

class Timetrack_ViewRenderer {
	public function render(array $parts)
	{
		ob_start();
		foreach($parts as $part)
		{
			require $part;
		}
		return ob_get_clean();
	}
	
	public function __call($method, $params) {
		echo "$method called";
	}
}