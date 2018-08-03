<?php
/**
 * Textr.php
 * @author      Marc-André Appel <marc-andre@appel.fun>
 * @copyright   2018 Marc-André Appel
 * @license     https://opensource.org/licenses/LGPL-3.0 LGPL 3.0
 * @link        https://github.com/marcandreappel/Textr
 * @created     03/08/2018
 */

declare(strict_types=1);

namespace MarcAndreAppel\Textr;

use voku\helper\URLify;
use voku\helper\UTF8;

trait Textr
{
	/**
	 * @brief Formatting a text into a "lowercase-and-dashed" format
	 *
	 * @param string      $text
	 * @param int         $max_length
	 * @param string      $locale
	 * @param string|null $excluded_words
	 *
	 * @return string
	 */
	public function urlify(string $text, int $max_length = 128, string $locale = 'en_US', string $excluded_words = null): string
	{
		/**
		 * @brief   Default preset for words to delete
		 */
		if ( ! is_null($excluded_words))
		{
			$excluded_words = "a, an, as, at, before, but, by, for, from, is, in, into, like, of, off, on, onto, per, since, than, the, this, that, to, up, via, with";
		}
		$text = strtolower(str_replace(array("\r", "\n", "\t"), ' ', $this->asciify($text, $locale)));

		if (is_string($excluded_words))
		{
			if (strlen($excluded_words))
			{
				$remove_list = explode(',', $excluded_words);
				$remove_list = array_map('trim', $remove_list);
				$remove_list = array_filter($remove_list, 'strlen');
			}
			else
			{
				$remove_list = array();
			}
		}
		else
		{
			$remove_list = URLify::$remove_list;
		}
		if (count($remove_list))
		{
			$text = preg_replace('/\b(' . join('|', $remove_list) . ')\b/i', '', $text);
		}
		/**
		 * @brief Removing unneeded characters
		 */
		$text = preg_replace('/[^-\w\s]/', '', $text);
		/**
		 * @brief Converting underscores to spaces
		 */
		$text = str_replace('_', ' ', $text);
		/**
		 * @brief Triming leading and trailing spaces
		 */
		$text = preg_replace('/^\s+|\s+$/', '', $text);
		/**
		 * @brief Converting spaces to hyphens
		 */
		$text = preg_replace('/[-\s]+/', '-', $text);
		/**
		 * @brief Lowercasing the text
		 */
		$text = strtolower($text);
		/**
		 * @brief Trim to $max_length characters
		 */
		$text = trim(substr($text, 0, $max_length), '-');

		return $text;
	}

	/**
	 * @brief Converts the string into an ASCII-only string
	 *
	 * @param string $text
	 * @param string $locale
	 * @param string $charset
	 *
	 * @return string
	 */
	public function asciify(string $text, string $locale = 'en_US', string $charset = 'UTF-8'): string
	{
		$language = substr($locale, 0, strcspn($locale, '_'));
		$text = URLify::downcode($text, $language);

		if (preg_match('/[^\\t\\r\\n\\x20-\\x7e]/', $text))
		{
			if (function_exists('iconv'))
			{
				$_text = @iconv($charset, 'US-ASCII//IGNORE//TRANSLIT', $text);
				if (is_string($_text))
				{
					$text = $_text;
				}
			}
			$text = preg_replace('/[^\\t\\r\\n\\x20-\\x7e]/', '', $text);
		}
		return $text;
	}

	/**
	 * @brief Shortens and sanitizes a string but only cuts at word boundaries.
	 *
	 * @param        $string
	 * @param int    $length
	 * @param string $tail
	 * @param string $charset
	 *
	 * @return string
	 */
	public function shortify(string $string, int $length = 255, string $tail = '…')
	{
		if (intval($length) == 0) 
		{
			$length = 255;
		}
		$string = strip_tags($string);

		if (UTF8::strlen($string) > $length)
		{
			/**
			 * @brief Replacing whitespace characters
			 */
			$string = preg_replace('/\s+?(\S+)?$/', '', UTF8::substr($string, 0, $length + 1));
			/**
			 * @brief Needed if the shortened string consists of a single word
			 */
			$string = UTF8::substr($string, 0, $length) . $tail;
		}

		return $string;
	}

	/**
	 * @brief Scans passed text and automatically hyperlinks any URL inside it
	 *
	 * @param string $input
	 * @param bool   $target
	 *
	 * @return string
	 */
	public function linkify(string $input, bool $target = false): string
	{
		$target = ($target) ? ' target="_blank" ' : '';
		$output = preg_replace(
			'/(http:\/\/|https:\/\/|(www\.))(([^\s<]{4,80})[^\s<]*)/',
			'<a href="$1$2$3" ' . $target . ' rel="nofollow">$1$2$4</a>',
			$input);
		
		return $output;
	}
}
