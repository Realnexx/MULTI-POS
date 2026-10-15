<?php

/**
 * Helper Class for Formula Evaluation
 * Replaces variables and evaluates math using eval()
 */
class FormulaEngine {
    private $expression;
    private $scores = []; // Array of judge scores
    private $context = []; // Other variables

    public function __construct($formula_expression) {
        $this->expression = $formula_expression;
    }

    public function evaluate($scores, $context) {
        $this->scores = $scores;
        $this->context = $context; 

        $expr = $this->expression;

        // 1. Resolve functions from the inside out (iterative approach to handle nesting)
        // This regex matches functions that do not have nested parentheses in their inner part
        while (preg_match('/(SUM|AVG|MAX|MIN|COUNT|ROUND|ABS)\s*\(\s*([^()]+)\s*\)/i', $expr, $m)) {
            $full_match = $m[0];
            $func = strtoupper($m[1]);
            $inner = trim($m[2]);
            $replacement = 0;

            // Handle aggregate functions
            if (in_array($func, ['SUM', 'AVG', 'MAX', 'MIN', 'COUNT'])) {
                if (strtolower($inner) === 'score') {
                    if ($func === 'SUM') $replacement = array_sum($this->scores);
                    elseif ($func === 'AVG') $replacement = count($this->scores) == 0 ? 0 : array_sum($this->scores) / count($this->scores);
                    elseif ($func === 'MAX') $replacement = count($this->scores) == 0 ? 0 : max($this->scores);
                    elseif ($func === 'MIN') $replacement = count($this->scores) == 0 ? 0 : min($this->scores);
                    elseif ($func === 'COUNT') $replacement = count($this->scores);
                } else {
                    // Complex aggregate like SUM(score * weight)
                    $results = [];
                    foreach ($this->scores as $s) {
                        $prepared = $this->prepareExpression($inner, $s);
                        $results[] = $this->executeEval($prepared);
                    }
                    if ($func === 'SUM') $replacement = array_sum($results);
                    elseif ($func === 'AVG') $replacement = count($results) == 0 ? 0 : array_sum($results) / count($results);
                    elseif ($func === 'MAX') $replacement = count($results) == 0 ? 0 : (empty($results) ? 0 : max($results));
                    elseif ($func === 'MIN') $replacement = count($results) == 0 ? 0 : (empty($results) ? 0 : min($results));
                    elseif ($func === 'COUNT') $replacement = count($results);
                }
            } elseif ($func === 'ROUND') {
                $parts = explode(',', $inner);
                $val = $this->executeEval($this->prepareExpression($parts[0]));
                $dec = isset($parts[1]) ? intval($this->executeEval($this->prepareExpression($parts[1]))) : 0;
                $replacement = round($val, $dec);
            } elseif ($func === 'ABS') {
                $val = $this->executeEval($this->prepareExpression($inner));
                $replacement = abs($val);
            }

            // Replace this specific instance of the function call with its result
            // Using preg_quote and str_replace to be safe
            $expr = str_replace($full_match, $replacement, $expr);
        }

        // 2. Prepare and Evaluate the final top-level expression
        $final_expr = $this->prepareExpression($expr);
        return $this->executeEval($final_expr);
    }

    /**
     * Replaces variables with values and handles math shorthand like ^
     * @param string $expr The math expression string
     * @param float|null $individual_score If provided, 'score' variable uses this value. Otherwise uses sum of all scores.
     */
    private function prepareExpression($expr, $individual_score = null) {
        $s_val = ($individual_score !== null) ? $individual_score : array_sum($this->scores);
        
        // Replace bare variables
        $expr = preg_replace('/\bi_score\b/i', $s_val, $expr);
        $expr = preg_replace('/\bscore\b/i', $s_val, $expr);
        
        $j_count = $this->context['judge_count'] ?? count($this->scores);
        $expr = preg_replace('/\bjudge_count\b/i', $j_count, $expr);

        $c_weight = $this->context['category_weight'] ?? 0;
        $expr = preg_replace('/\bcategory_weight\b/i', $c_weight, $expr);
        $expr = preg_replace('/\bweight\b/i', $c_weight, $expr);
        
        $rank = $this->context['rank'] ?? 0;
        $expr = preg_replace('/\brank\b/i', $rank, $expr);
        $deduction = $this->context['deduction'] ?? 0;
        $expr = preg_replace('/\bdeduction\b/i', $deduction, $expr);

        // Pageant Tabulation variables
        $vars = [
            'category_result' => $this->context['criteria_score'] ?? 0,
            'criteria_score' => $this->context['criteria_score'] ?? 0,
            'criteria_weighted' => $this->context['criteria_weighted'] ?? 0,
            'category_score' => $this->context['category_score'] ?? 0,
            'category_weighted' => $this->context['category_weighted'] ?? 0,
            'total_category_score' => $this->context['total_category_score'] ?? 0,
            'total_criteria_score' => $this->context['total_criteria_score'] ?? 0,
            'average_score' => $this->context['average_score'] ?? 0,
            'total_score' => $this->context['total_score'] ?? 0,
        ];
        foreach ($vars as $var_name => $var_value) {
            $expr = preg_replace('/\b' . $var_name . '\b/i', $var_value, $expr);
        }

        // PEMDAS: Convert ^ to **
        $expr = str_replace('^', '**', $expr);

        return $expr;
    }

    /**
     * Safely executes eval() on a numeric/math string
     */
    private function executeEval($expr) {
        // Only allow numbers, operators, parens, dot, spaces, underscores, and hyphen
        if (preg_match('/[^0-9+*\/().\s_\-]/', $expr)) {
            return 0; // Fail safe
        }

        try {
            // Using eval() to follow standard PEMDAS rules of PHP
            $ival = @eval("return ($expr);");
            return ($ival === false) ? 0 : $ival;
        } catch (Throwable $t) {
            return 0;
        }
    }
}
?>
