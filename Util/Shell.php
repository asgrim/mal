<?php

/**
 * A base shell class
 *
 * @author James Titcumb <hello@jamestitcumb.com>
 * @license https://github.com/Asgrim/MAL/raw/master/LICENSE The BSD License
 * @copyright Copyright (c) 2011, James Titcumb
 */
class MAL_Util_Shell
{
	private $_last_output;
	private $_last_errno;

	public function getLastError()
	{
		return $this->_last_errno;
	}

	protected function Exec($cmd, $noisy = false)
	{
		if($noisy) echo "<strong>Command: " . $cmd . "</strong><br /><br />";

		$this->_last_errno = 0;
		$this->_last_output = array();

		exec($cmd . " 2>&1", $this->_last_output, $this->_last_errno);

		if($noisy)
		{
			echo "Output:<pre>";
			var_dump($this->_last_output);
			echo "Exit code: " . $this->_last_errno;
			echo "</pre>";
			echo "<hr />";
		}
	}
}
