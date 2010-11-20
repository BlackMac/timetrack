<?php
include 'ViewRenderer.php';

class Timetrack_View {
	/**
	 * @var string
	 */
	protected $directory;

	/**
	 * @var string
	 */
	protected $header = 'header';

	/**
	 * @var string
	 */
	protected $footer = 'footer';

	/**
	 * @var string
	 */
	protected $viewScript;
	
	/**
	 * @var Timetrack_ViewRenderer
	 */
	public $view;

	/**
	 * Constructs the View object.
	 *
	 * @param string $directory
	 */
	public function __construct($directory = 'views')
	{
		$this->directory = $directory;
		$this->view = new Timetrack_ViewRenderer();
		$this->prepare();
	}
	
	protected function prepare()
	{
		
	}
	
	public function setViewScript($viewScript)
	{
		$this->viewScript = $viewScript;
	}

	public function getViewScript()
	{
		return $this->viewScript;
	}

	/**
	 * Set the header template.
	 *
	 * @param string $file The (relative) file name
	 * @return null
	 */
	public function setHeader($file)
	{
		$this->header = $file;
	}

	/**
	 * Set the footer template.
	 *
	 * @param string $file The (relative) file name
	 * @return null
	 */
	public function setFooter($file)
	{
		$this->footer = $file;
	}

	/**
	 * Calculates the filename for a view script.
	 *
	 * @return string
	 */
	protected function getFilename()
	{
		return $this->directory . '/' . $this->viewScript . '.phtml';
	}

	/**
	 * Calculates the header filename for a view script.
	 *
	 * @return string
	 */
	protected function getHeaderFilename()
	{
		return $this->directory . '/' . $this->header . '.phtml';
	}

	/**
	 * Calculates the foot filename for a view script.
	 *
	 * @return string
	 */
	protected function getFooterFilename()
	{
		return $this->directory . '/' . $this->footer . '.phtml';
	}

	/**
	 * Render the view by including the view script.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @return string
	 */
	public function render()
	{
		if ($this->viewScript === null) {
			throw new Exception('No view script set');
		}

		$header = $this->getHeaderFilename();
		$body = $this->getFilename();
		$footer = $this->getFooterFilename();

		if (!file_exists($header)) {
			throw new Exception('View header in file "' . $header . '" not found');
		}

		if (!file_exists($body)) {
			throw new Exception('View "' . $this->viewScript . '" not found in file "' . $body . '"');
		}

		if (!file_exists($footer)) {
			throw new Exception('View footer in file "' . $footer . '" not found');
		}

		$parts = array($header, $body, $footer);
		$body = $this->view->render($parts);

		return $body;
	}

	/**
	 * Detects whether the user agent is a mobile device
	 */
	public function detectMobileDevices() {
		$container = $_SERVER['HTTP_USER_AGENT'];

		// Add whatever user agents you want here to the array if you want to make this show on a Blackberry
		// or something. No guarantees it'll look pretty, though!
		$useragents = array("iPhone", "iPod", "aspen", "dream", "incognito", "webmate", "BlackBerry9500", "BlackBerry9530");
		$regex = '/' . join('|', $useragents) . '/i';

		return preg_match($regex, $container) === 1;
	}

}