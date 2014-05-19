<?php

namespace Karma\Formatters;

use Karma\Formatter;

class Rules implements Formatter
{
    private
        $rules,
        $emptyListStrategy;
    
    public function __construct(array $rules)
    {
        $this->emptyListStrategy = self::KEEP_LINE;
        
        $rules = $this->extractEmptyListStrategyRule($rules);
        $this->convertRules($rules);
    }
    
    private function extractEmptyListStrategyRule(array $rules)
    {
        $filteredRules = array();
        $hasAlreadyMatched = false;
        
        foreach($rules as $value => $result)
        {
            if(strtolower(trim($value)) === '<emptylist>')
            {
                if($hasAlreadyMatched === true)
                {
                    throw new \RuntimeException('Duplicate <emptyList> entry in formatters');
                }
                
                $this->setEmptyListStrategy(trim($result));
                $hasAlreadyMatched = true;
                
                continue;
            }
            
            $filteredRules[$value] = $result;
        }
        
        return $filteredRules;
    }
    
    public function setEmptyListStrategy($emptyListStrategy)
    {
        $emptyListStrategy = strtolower($emptyListStrategy);
        
        if(in_array($emptyListStrategy, array(self::KEEP_LINE, self::REMOVE_LINE)))
        {
            $this->emptyListStrategy = $emptyListStrategy;
        }
        
        return $this;
    }
    
    public function getEmptyListStrategy()
    {
        return $this->emptyListStrategy;
    }
    
    private function getSpecialValuesMappingTable()
    {
        return array(
            '<true>' => true,
            '<false>' => false,
            '<null>' => null,
            '<string>' => function($value) {
                return is_string($value);
            }
        );
    }
    
    private function convertRules(array $rules)
    {
        $this->rules = array();
        $mapping = $this->getSpecialValuesMappingTable();
        
        foreach($rules as $value => $result)
        {
            $value = trim($value);
            
            if(is_string($value) && array_key_exists($value, $mapping))
            {
                $result = $this->handleStringFormatting($value, $result);
                $value = $mapping[$value];
            }
            
            $this->rules[] = array($value, $result);
        }    
    }
    
    private function handleStringFormatting($value, $result)
    {
        if($value === '<string>')
        {
            $result = function ($value) use ($result) {
                return str_replace('<string>', $value, $result);
            };
        }
        
        return $result;
    }
    
    public function format($value)
    {
        foreach($this->rules as $rule)
        {
            list($condition, $result) = $rule;
            
            if($this->isRuleMatches($condition, $value))
            {
                return $this->applyFormattingRule($result, $value);
            }
        }
        
        return $value;
    }
    
    private function isRuleMatches($condition, $value)
    {
        $hasMatched = ($condition === $value);
        
        if($condition instanceof \Closure)
        {
            $hasMatched = $condition($value);
        }    
        
        return $hasMatched;
    }
    
    private function applyFormattingRule($ruleResult, $value)
    {
        if($ruleResult instanceof \Closure)
        {
            return $ruleResult($value);
        }
        
        return $ruleResult;
    }
}
