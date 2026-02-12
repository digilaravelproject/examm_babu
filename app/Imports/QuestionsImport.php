<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class QuestionsImport extends DefaultValueBinder implements ToArray, WithHeadingRow, WithCustomValueBinder
{
    public function array(array $array)
    {
        return $array;
    }

    /**
     * UNIVERSAL VALUE BINDER
     * Yeh logic ensure karta hai ki data waisa hi aaye jaisa Excel me dikh raha hai.
     */
    public function bindValue(Cell $cell, $value)
    {
        // 1. Agar cell khali hai, to null return karo
        if ($value === null || $value === '') {
            return parent::bindValue($cell, $value);
        }

        // 2. PERCENTAGE HANDLER (Future Proof)
        // Hum check karenge ki cell par '%' ka format laga hai ya nahi.
        $formatCode = $cell->getStyle()->getNumberFormat()->getFormatCode();

        // Agar format code me % hai, to hum value ko mathematically fix karenge
        if ($formatCode && strpos($formatCode, '%') !== false && is_numeric($value)) {
            $percentageVal = $value * 100;

            // Decimal handling (10.0 -> 10, 10.5 -> 10.5)
            if (floor($percentageVal) == $percentageVal) {
                $percentageVal = (int)$percentageVal;
            } else {
                $percentageVal = round($percentageVal, 2);
            }

            // Value set karo aur return karo (e.g., "10%")
            $cell->setValueExplicit($percentageVal . '%', DataType::TYPE_STRING);
            return true;
        }

        // 3. DATE HANDLER
        // Agar future me Date column aata hai to ye number nahi banega
        if (Date::isDateTime($cell)) {
             try {
                 $dateValue = Date::excelToDateTimeObject($value)->format('Y-m-d');
                 $cell->setValueExplicit($dateValue, DataType::TYPE_STRING);
                 return true;
             } catch (\Exception $e) {
                 // Agar date parse fail ho jaye to raw value hi rakho
             }
        }

        // 4. STANDARD FORMATTER
        // Baaki sabke liye (Currency, Text, Scientific Notation)
        $formattedValue = $cell->getFormattedValue();

        // Safety: Agar formatted khali hai par raw value hai
        if (($formattedValue === '' || $formattedValue === null) && $value !== null) {
            $formattedValue = $value;
        }

        // Force String (Taaki "01" number bankar "1" na ho jaye)
        $cell->setValueExplicit((string)$formattedValue, DataType::TYPE_STRING);

        return true;
    }
}
