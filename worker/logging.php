<?php
use Monolog\Logger;
use Monolog\Formatter\NormalizerFormatter;

/**
 * Serializes a log message to CLI from monolog
 * https://gist.github.com/joegreen0991/7370013
 *
 * @author Joe Green
 * @author Daniel Trnka
 */
class MonologCliFormatter extends NormalizerFormatter
{

	const TAB = "    ";

	public $colors = array(
		LOGGER::DEBUG => '0;36', // Cyan
		LOGGER::INFO => '0;32', // Green
		LOGGER::NOTICE => '1;33', // Yellow
		LOGGER::WARNING => '0;35', // Purple
		LOGGER::ERROR => '0;31', // Red
		LOGGER::CRITICAL => array('0;30','43'), // Black/Yellow
		LOGGER::ALERT => array('1;37','45'), // White/Purple
		LOGGER::EMERGENCY => array('1;37','41'), // White/Red
	 );

	/**
	 * {@inheritdoc}
	 */
	public function format(array $record)
	{
		$record = parent::format($record);

		$lines = array(
			$record['datetime'] . ' - ' . Logger::getLevelName($record['level']) . ' - ' . $record['channel'] . ' - ' . $record['message']

			//'Message : ' . $record['message'],
			//'Level : ' . Logger::getLevelName($record['level'])
		);

		if (isset($record['context']['exception'])) {
			$lines[] = 'File : ' . $record['context']['exception']['file'];
			$lines[] = 'Trace : ';

			foreach($record['context']['exception']['trace'] as $line){
				$lines[] = self::TAB . $line;
			}

			unset($record['context']['exception']);
		}

		foreach ($record['context'] as $key => $val) {
			$lines[] = $key . ' : ' . (is_scalar($val) ? $val : $this->toJson($val));
		}

		// Color the output

		// Get the max row length
		$max = max(array_map('strlen', $lines));

		// Pad each of the rows to this length
		/*foreach($lines as $i => $line){
			$lines[$i] = self::TAB . str_pad($line, $max + 5);
		}*/

		$string = implode(PHP_EOL, $lines);

		$colors = $this->colors[$record['level']];

		if(is_array($colors)){
			// Create a padding string of empty spaces the same length as the max row
			$pad = PHP_EOL . str_repeat(self::TAB . str_repeat(" ", $max + 5) . PHP_EOL, 2);

			// Create the coloured string
			return "\033[{$colors[0]}m\033[{$colors[1]}m" . $pad . $string . $pad . "\033[0m\n";
		}else{
			return "\033[{$colors}m" . $string . "\033[0m\n";
		}

	}
}

