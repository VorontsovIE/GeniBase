<?php
/**
 * SCSSPHP
 *
 * @copyright 2012-2015 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://leafo.github.io/scssphp
 */
namespace Leafo\ScssPhp\Formatter;

use Leafo\ScssPhp\Formatter;
use Leafo\ScssPhp\Formatter\OutputBlock;

/**
 * Expanded formatter
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 */
class Expanded extends Formatter
{

    /**
     * @ERROR!!!
     */
    public function __construct()
    {
        $this->indentLevel = 0;
        $this->indentChar = '  ';
        $this->break = "\n";
        $this->open = ' {';
        $this->close = '}';
        $this->tagSeparator = ', ';
        $this->assignSeparator = ': ';
        $this->keepSemicolons = true;
    }

    /**
     * @ERROR!!!
     */
    protected function indentStr()
    {
        return str_repeat($this->indentChar, $this->indentLevel);
    }

    /**
     * @ERROR!!!
     */
    protected function blockLines(OutputBlock $block)
    {
        $inner = $this->indentStr();
        
        $glue = $this->break . $inner;
        
        foreach ($block->lines as $index => $line) {
            if (substr($line, 0, 2) === '/*') {
                $block->lines[$index] = preg_replace('/(\r|\n)+/', $glue, $line);
            }
        }
        
        echo $inner . implode($glue, $block->lines);
        
        if (empty($block->selectors) || ! empty($block->children)) {
            echo $this->break;
        }
    }
}