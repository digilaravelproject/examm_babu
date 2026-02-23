<?php

/**
 * Exam Babu - Integrated Question Helpers
 * Hardcoded salts included + Full validation and parsing logic.
 */

// --- 1. FORMATTING FUNCTIONS ---

if (!function_exists('formatQuestionProperty')) {
    /**
     * Format Question for the Exam
     */
    function formatQuestionProperty($question, $qType)
    {
        if ($qType == "FIB") {
            return replaceBlanksWithPlaceholder($question);
        }
        return $question;
    }
}

if (!function_exists('formatOptionsProperty')) {
    /**
     * Format Options for the Exam
     */
    function formatOptionsProperty($options, $qType, $question)
    {
        $newOptions = [];
        $options = is_string($options) ? json_decode($options, true) : $options;

        // Hardcoded Salts from your config
        $m_salt = "gRPXyMML3JZ4gbX3"; // matching_option_salt
        $p_salt = "AVZZn02uV1ppQhcE"; // matching_pair_salt
        $o_salt = "EejYoTj4as3RQtUm"; // ordering_option_salt

        switch ($qType) {
            case "MTF":
                $leftOptions = [];
                $rightPairs = [];
                $matches = [];
                $pairs = [];

                if (is_array($options)) {
                    foreach ($options as $key => $option) {
                        $val = is_array($option) ? ($option['option'] ?? '') : (is_object($option) ? ($option->option ?? '') : (is_string($option) ? $option : ''));
                        $pairVal = is_array($option) ? ($option['pair'] ?? '') : (is_object($option) ? ($option->pair ?? '') : '');

                        // Fallback: Agar pair khali hai toh comma se split karne ki koshish karein (Issue #3 Fix)
                        if (empty($pairVal) && preg_match('/^(.*?),(.*)$/s', $val, $matches_split)) {
                            $val = trim($matches_split[1]);
                            $pairVal = trim(strip_tags($matches_split[2]));
                        }

                        array_push($leftOptions, trim($val));
                        array_push($rightPairs, trim($pairVal));
                    }
                }

                foreach ($leftOptions as $key => $option) {
                    array_push($matches, ['id' => md5($m_salt . $key), 'value' => $option]);
                }

                foreach ($rightPairs as $key => $pair) {
                    array_push($pairs, ['id' => md5($p_salt . $key), 'value' => $pair, 'code' => '']);
                }

                shuffle($pairs);
                foreach ($pairs as $key => &$pair) {
                    $pair['code'] = covertToCharacter($key);
                }

                return ['matches' => $matches, 'pairs' => $pairs];

            case "FIB":
                return count(getBlankItems($question));

            case "ORD":
                if (is_array($options)) {
                    foreach ($options as $key => $option) {
                        $val = is_array($option) ? ($option['option'] ?? '') : (is_object($option) ? ($option->option ?? '') : (is_string($option) ? $option : ''));
                        array_push($newOptions, ['id' => md5($o_salt . $key), 'value' => trim($val), 'code' => '']);
                    }
                }
                shuffle($newOptions);
                foreach ($newOptions as $key => &$option) {
                    $option['code'] = covertToCharacter($key);
                }
                return $newOptions;

            case "SAQ":
            case "LAQ":
                return [];

            default:
                if (is_array($options)) {
                    foreach ($options as $option) {
                        $val = is_array($option) ? ($option['option'] ?? '') : (is_object($option) ? ($option->option ?? '') : (is_string($option) ? $option : ''));
                        array_push($newOptions, trim($val));
                    }
                }
                return $newOptions;
        }
    }
}

if (!function_exists('formatAnswerProperty')) {
    function formatAnswerProperty($qType)
    {
        return match ($qType) {
            "MMA", "MTF", "FIB", "ORD" => [],
            default => ''
        };
    }
}

// --- 2. VALIDATION FUNCTIONS ---

if (!function_exists('validateMSA')) {
    function validateMSA($correctAnswer, $userResponse) {
        return (int) $userResponse == (int) $correctAnswer;
    }
}

if (!function_exists('validateMMA')) {
    function validateMMA($correctAnswer, $userResponse) {
        if (!is_array($userResponse) || !is_array($correctAnswer)) return false;
        $x = array_values($userResponse);
        $y = array_values($correctAnswer);
        sort($x); sort($y);
        return $x == $y;
    }
}

if (!function_exists('validateFIB')) {
    function validateFIB($correctAnswer, $userResponse) {
        if (!is_array($userResponse) || !is_array($correctAnswer)) return false;
        $x = array_map('trim', array_map('strtolower', array_values($userResponse)));
        $y = array_map('trim', array_map('strtolower', array_values($correctAnswer)));
        return $x == $y;
    }
}

if (!function_exists('validateMTF')) {
    function validateMTF($options, $userResponse, $answerFlag = false)
    {
        $correctMatch = [];
        $responseMatch = [];
        $p_salt = "AVZZn02uV1ppQhcE"; // matching_pair_salt

        foreach ($options as $key => $option) {
            array_push($correctMatch, md5($p_salt . $key));
        }

        if ($answerFlag) return $correctMatch;
        if (!$userResponse) return false;

        foreach ($userResponse as $item) {
            if (is_array($item)) {
                array_push($responseMatch, $item['id'] ?? '');
            } else {
                $object = json_decode(json_encode($item), true);
                array_push($responseMatch, $object['id'] ?? '');
            }
        }
        return array_values($correctMatch) == array_values($responseMatch);
    }
}

if (!function_exists('validateSAQ')) {
    function validateSAQ($options, $userResponse)
    {
        $possibleAnswers = [];
        foreach ($options as $option) {
            $val = is_array($option) ? ($option['option'] ?? '') : ($option->option ?? '');
            array_push($possibleAnswers, strtolower(trim($val)));
        }
        return in_array(strtolower(trim($userResponse)), $possibleAnswers);
    }
}

if (!function_exists('validateORD')) {
    function validateORD($options, $userResponse, $answerFlag = false)
    {
        $correctOrder = [];
        $responseOrder = [];
        $o_salt = "EejYoTj4as3RQtUm"; // ordering_option_salt

        foreach ($options as $key => $option) {
            array_push($correctOrder, md5($o_salt . $key));
        }

        if ($answerFlag) return $correctOrder;
        if (!$userResponse) return false;

        foreach ($userResponse as $item) {
            if (is_array($item)) {
                array_push($responseOrder, $item['id'] ?? '');
            } else {
                $object = json_decode(is_string($item) ? $item : json_encode($item), true);
                array_push($responseOrder, $object['id'] ?? '');
            }
        }
        return array_values($correctOrder) == array_values($responseOrder);
    }
}

// --- 3. UTILITY & FIB LOGIC ---

if (!function_exists('getBlankItems')) {
    function getBlankItems($str)
    {
        // Issue #1 Fix: Regex is better than manual while loop
        preg_match_all('/##(.*?)##/', $str, $matches);
        return $matches[1] ?? [];
    }
}

if (!function_exists('replaceBlanksWithPlaceholder')) {
    function replaceBlanksWithPlaceholder($text)
    {
        return preg_replace_callback("/##(.*?)##/", function ($m) {
            static $id = 0; $id++;
            // UI spacing and blank logic
            return " <span class='fib-blank-ui'>(" . $id . ") ________</span> ";
        }, $text);
    }
}

if (!function_exists('covertToCharacter')) {
    function covertToCharacter($value)
    {
        $characters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        return $characters[$value] ?? 'Z';
    }
}
