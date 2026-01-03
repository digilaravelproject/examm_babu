<?php

use Illuminate\Support\Facades\Config;

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
        switch ($qType) {
            case "MTF":
                $leftOptions = [];
                $rightPairs = [];
                $matches = [];
                $pairs = [];

                foreach ($options as $key => $option) {
                    // Safety check for PHP 8.2
                    $val = is_array($option) ? ($option['option'] ?? '') : ($option->option ?? '');
                    $pairVal = is_array($option) ? ($option['pair'] ?? '') : ($option->pair ?? '');

                    array_push($leftOptions, $val);
                    array_push($rightPairs, $pairVal);
                }

                foreach ($leftOptions as $key => $option) {
                    $salt = config('qwiktest.matching_option_salt', 'def_salt');
                    array_push($matches, ['id' => md5($salt . $key), 'value' => $option]);
                }

                foreach ($rightPairs as $key => $pair) {
                    $salt = config('qwiktest.matching_pair_salt', 'def_salt');
                    array_push($pairs, ['id' => md5($salt . $key), 'value' => $pair, 'code' => '']);
                }

                shuffle($pairs);
                foreach ($pairs as $key => &$pair) {
                    $pair['code'] = covertToCharacter($key);
                }

                $newOptions['matches'] = $matches;
                $newOptions['pairs'] = $pairs;
                return $newOptions;

            case "FIB":
                return count(getBlankItems($question));

            case "ORD":
                foreach ($options as $key => $option) {
                    $salt = config('qwiktest.ordering_option_salt', 'def_salt');
                    $val = is_array($option) ? ($option['option'] ?? '') : ($option->option ?? '');
                    array_push($newOptions, ['id' => md5($salt . $key), 'value' => $val, 'code' => '']);
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
                foreach ($options as $option) {
                    $val = is_array($option) ? ($option['option'] ?? '') : ($option->option ?? '');
                    array_push($newOptions, $val);
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

// --- VALIDATION FUNCTIONS ---

if (!function_exists('validateMSA')) {
    function validateMSA($correctAnswer, $userResponse)
    {
        return (int) $userResponse == (int) $correctAnswer;
    }
}

if (!function_exists('validateMMA')) {
    function validateMMA($correctAnswer, $userResponse)
    {
        if (!is_array($userResponse) || !is_array($correctAnswer)) return false;

        $x = array_values($userResponse);
        $y = array_values($correctAnswer);
        sort($x);
        sort($y);
        return $x == $y;
    }
}

if (!function_exists('validateFIB')) {
    function validateFIB($correctAnswer, $userResponse)
    {
        if (!is_array($userResponse) || !is_array($correctAnswer)) return false;

        $x = array_values($userResponse);
        $y = array_values($correctAnswer);
        $x = array_map('strtolower', $x);
        $y = array_map('strtolower', $y);
        return $x == $y;
    }
}

if (!function_exists('validateMTF')) {
    function validateMTF($options, $userResponse, $answerFlag = false)
    {
        $correctMatch = [];
        $responseMatch = [];
        $salt = config('qwiktest.matching_pair_salt', 'def_salt');

        foreach ($options as $key => $option) {
            array_push($correctMatch, md5($salt . $key));
        }

        if (!$userResponse) return false;

        foreach ($userResponse as $item) {
            if (is_array($item)) {
                array_push($responseMatch, $item['id'] ?? '');
            } else {
                // Fix: json_encode to json_decode to read properties properly
                $object = json_decode(json_encode($item), true);
                array_push($responseMatch, $object['id'] ?? '');
            }
        }

        if ($answerFlag) {
            return $correctMatch;
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
            array_push($possibleAnswers, $val);
        }
        return in_array($userResponse, $possibleAnswers);
    }
}

if (!function_exists('validateORD')) {
    function validateORD($options, $userResponse, $answerFlag = false)
    {
        $correctOrder = [];
        $responseOrder = [];
        $salt = config('qwiktest.ordering_option_salt', 'def_salt');

        foreach ($options as $key => $option) {
            array_push($correctOrder, md5($salt . $key));
        }

        if (!$userResponse) return false;

        foreach ($userResponse as $item) {
            if (is_array($item)) {
                array_push($responseOrder, $item['id'] ?? '');
            } else {
                $object = json_decode(is_string($item) ? $item : json_encode($item), true);
                array_push($responseOrder, $object['id'] ?? '');
            }
        }

        if ($answerFlag) {
            return $correctOrder;
        }

        return array_values($correctOrder) == array_values($responseOrder);
    }
}

// --- UTILITY FUNCTIONS ---

if (!function_exists('getBlankItems')) {
    function getBlankItems($str)
    {
        $startDelimiter = "##";
        $endDelimiter = "##";
        $contents = array();
        $startDelimiterLength = strlen($startDelimiter);
        $endDelimiterLength = strlen($endDelimiter);
        $startFrom = 0;

        while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {
            $contentStart += $startDelimiterLength;
            $contentEnd = strpos($str, $endDelimiter, $contentStart);
            if (false === $contentEnd) {
                break;
            }
            $contents[] = substr($str, $contentStart, $contentEnd - $contentStart);
            $startFrom = $contentEnd + $endDelimiterLength;
        }
        return $contents;
    }
}

if (!function_exists('replaceBlanksWithPlaceholder')) {
    function replaceBlanksWithPlaceholder($text)
    {
        return preg_replace_callback(
            "/(##)(.*?)(##)/",
            function ($m) {
                static $id = 0;
                $id++;
                return "[" . $id . "] " . "______";
            },
            $text
        );
    }
}

if (!function_exists('covertToCharacter')) {
    function covertToCharacter($value)
    {
        $characters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        return $characters[$value] ?? 'Z';
    }
}
