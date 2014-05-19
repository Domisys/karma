<?php

use Karma\Formatters\Rules;
use Karma\Formatter;

class RulesTests extends PHPUnit_Framework_TestCase
{
    private
        $formatter;
    
    protected function setUp()
    {
        $rules = array(
            ' <true>' => 'string true',
            '<false> ' => 'string false',
            '<null>' => 0,
            'foobar' => 'barfoo',
            'footrue' => true,
            ' <string> ' => '"<string>"',
            '<emptyList>' => '<removeLine>'
        );
        
        $this->formatter = new Rules($rules);
    }
    
    /**
     * @dataProvider providerTestFormat
     */
    public function testFormat($input, $expected)
    {
        $result = $this->formatter->format($input);
        $this->assertSame($expected, $result);
    }
    
    public function providerTestFormat()
    {
        return array(
            'boolean true' => array(true, 'string true'),
            'string true' => array('true', '"true"'),
            'other string true' => array('<true>', '"<true>"'),
            'footrue' => array('footrue', true),
                        
            'boolean false' => array(false, 'string false'),
            'string false' => array('false', '"false"'),
            'other string false' => array('<false>', '"<false>"'),
                        
            'null' => array(null, 0),
            'string null' => array('null', '"null"'),
            'other string null' => array('<null>', '"<null>"'),
                        
            'zero' => array(0, 0),
            'string zero' => array('0', '"0"'),
            'other string zero' => array('<0>', '"<0>"'),
                        
            'foo' => array('foo', '"foo"'),
            'foobar' => array('foobar', 'barfoo'),
            'barfoobarfoo' => array('barfoobarfoo', '"barfoobarfoo"'),
        );    
    }
    
    public function testNominalEmptyListStrategy()
    {
        $this->assertSame(Formatter::REMOVE_LINE, $this->formatter->getEmptyListStrategy());
    }
    
    /**
     * @dataProvider providerTestEmptyListStrategy
     */
    public function testEmptyListStrategy($value, $expected, $ruleCondition = '<emptyList>')
    {
        $rules = array($ruleCondition => $value);
        $formatter = new Rules($rules);
        
        $this->assertSame($expected, $formatter->getEmptyListStrategy());
    }
    
    public function providerTestEmptyListStrategy()
    {
        return array(
            // OK
            array('<keepLine>', Formatter::KEEP_LINE),
            array('<removeLine>', Formatter::REMOVE_LINE),
            array('<KEEPLINE>', Formatter::KEEP_LINE),
            array('<REMOVELINE>', Formatter::REMOVE_LINE),
            array('<keepline>', Formatter::KEEP_LINE),
            array('<removeline>', Formatter::REMOVE_LINE),
            
            array('<keepLine>', Formatter::KEEP_LINE, '<EMPTYLIST>'),
            array('<removeLine>', Formatter::REMOVE_LINE, '<EMPTYLIST>'),
            array('<keepLine>', Formatter::KEEP_LINE, '<emptylist>'),
            array('<removeLine>', Formatter::REMOVE_LINE, '<emptylist>'),
            
            // NOK
            array('<AkeepLineB>', Formatter::KEEP_LINE),
            array('<AremoveLineB>', Formatter::KEEP_LINE),
            array('<removeLi>', Formatter::KEEP_LINE),
            array('<r>', Formatter::KEEP_LINE),
            array('keepLine', Formatter::KEEP_LINE),
            array('removeLine', Formatter::KEEP_LINE),
        );
    }
    
    /**
     * @expectedException \RuntimeException
     */
    public function testEmptyListStrategyError()
    {
        $rules = array(
            '<emptyList>' => '<keepLine>',
            '<emptyLIST>' => '<keepLine>',
        );
        
        $formatter = new Rules($rules);
    }
}