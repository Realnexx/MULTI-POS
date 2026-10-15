"""Database-free precision/conversion checks, also run by deployment validation."""
import os
from pathlib import Path
import subprocess
import unittest

ROOT = Path(__file__).resolve().parents[2]


class InventoryQuantityTests(unittest.TestCase):
    def test_precision_conversion_and_rejections(self):
        php = os.environ.get('PHP_BINARY', 'php')
        code = r'''
        require 'models/InventoryQuantity.php';
        if (InventoryQuantity::parse('0.0001') !== 1) exit(1);
        if (InventoryQuantity::format(12345) !== '1.2345') exit(2);
        if (InventoryQuantity::convert(150, 'kg', 'g') !== 150000) exit(3);
        if (InventoryQuantity::convert(2500, 'l', 'ml') !== 2500000) exit(4);
        foreach ([['0','pc','pc'],['-1','g','g'],['1e3','g','g'],['0.00001','g','g'],['0.5','pc','pc'],['1','g','ml']] as $row) {
            $rejected = false;
            try { InventoryQuantity::convert(InventoryQuantity::parse($row[0]),$row[1],$row[2]); }
            catch (InvalidArgumentException $error) { $rejected = true; }
            if (!$rejected) exit(5);
        }
        '''
        result = subprocess.run([php, '-r', code], cwd=ROOT, capture_output=True, text=True)
        self.assertEqual(0, result.returncode, result.stdout + result.stderr)
