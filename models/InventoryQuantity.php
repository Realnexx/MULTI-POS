<?php
/** Four-decimal fixed point inventory math. No database or float arithmetic. */
class InventoryQuantity
{
    const SCALE = 10000;

    public static function parse($value, $allowZero = false)
    {
        $value = trim((string) $value);
        if (!preg_match('/^(0|[1-9][0-9]{0,8})(?:\.([0-9]{1,4}))?$/D', $value, $m)) {
            throw new InvalidArgumentException('Use a positive quantity with up to four decimal places.');
        }
        $result = ((int) $m[1]) * self::SCALE + (int) str_pad($m[2] ?? '', 4, '0');
        if (!$allowZero && $result === 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }
        return $result;
    }

    public static function format($value)
    {
        return intdiv($value, self::SCALE) . '.' . str_pad((string) ($value % self::SCALE), 4, '0', STR_PAD_LEFT);
    }

    public static function unit($unit)
    {
        $unit = strtolower(trim($unit));
        $aliases = ['pcs'=>'pc', 'piece'=>'pc', 'pieces'=>'pc', 'grams'=>'g', 'gram'=>'g',
            'kilogram'=>'kg', 'kilograms'=>'kg', 'liter'=>'l', 'litre'=>'l', 'liters'=>'l', 'milliliter'=>'ml'];
        return $aliases[$unit] ?? $unit;
    }

    public static function convert($quantity, $from, $to)
    {
        $from = self::unit($from);
        $to = self::unit($to);
        $units = ['pc'=>['count',1], 'g'=>['weight',1], 'kg'=>['weight',1000],
            'ml'=>['volume',1], 'l'=>['volume',1000]];
        if ($from === '' || $to === '') throw new InvalidArgumentException('A stock unit is required.');
        if ($from === $to) $result = $quantity;
        elseif (isset($units[$from], $units[$to]) && $units[$from][0] === $units[$to][0]) {
            $numerator = $quantity * $units[$from][1];
            if ($numerator % $units[$to][1] !== 0) throw new InvalidArgumentException('Conversion exceeds four decimal places.');
            $result = intdiv($numerator, $units[$to][1]);
        } else throw new InvalidArgumentException('Incompatible ingredient and stock units.');
        if ((!isset($units[$to]) || $units[$to][0] === 'count') && $result % self::SCALE !== 0) {
            throw new InvalidArgumentException('Counted ingredients require whole units.');
        }
        return $result;
    }
}
