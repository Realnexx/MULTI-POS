"""Boundary checks for the production artifact and database bootstrap."""
import importlib.util
from pathlib import Path
import re
import os
import shutil
import subprocess
import tempfile
import unittest

ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('prepare', ROOT / 'tools/prepare-deploy.py')
prepare = importlib.util.module_from_spec(spec)
spec.loader.exec_module(prepare)


class DeploymentTests(unittest.TestCase):
    def test_artifact_includes_runtime_and_blocks_private_tools(self):
        files = {p.relative_to(ROOT).as_posix() for p in prepare.selected_files()}
        for required in ('index.php', '.htaccess', 'assets/.htaccess', 'config/db.php',
                         'config/app.php', 'config/uploads.php', 'assets/img/default.png',
                         'controllers/PosController.php', 'ajax/pos_cart.php',
                         'config/auth.php', 'services/PasswordMailer.php',
                         'lib/phpmailer/src/PHPMailer.php', 'lib/phpmailer/LICENSE'):
            self.assertIn(required, files)
        for file in files:
            self.assertFalse(file.startswith(('auroratabs/', 'scratch/', 'tools/', '.github/')))
            self.assertNotIn('config.local', file)
            self.assertNotIn('mail.local', file)
            self.assertNotIn('init_db', file)
            self.assertFalse(any(file.startswith(folder + '/') for folder in prepare.MEDIA_DIRS))
            self.assertFalse(file.startswith('assets/img/logo_'))
            self.assertNotIn(Path(file).suffix, ('.sql', '.bak', '.md', '.log'))

    def test_bootstrap_has_no_schema_or_seed_operations(self):
        for file in ('config/db.php', 'config/app.php', 'config/uploads.php'):
            self.assertIsNone(re.search(r'\b(CREATE\s+(DATABASE|TABLE)|ALTER\s+TABLE|INSERT\s+INTO|UPDATE\s+users)\b',
                                       (ROOT / file).read_text(), re.I), file)

    def test_initialization_is_cli_only(self):
        source = (ROOT / 'config/init_db.php').read_text()
        self.assertLess(source.index("PHP_SAPI !== 'cli'"), source.index("require_once"))
        self.assertNotIn("require_once __DIR__ . '/config/init_db.php'", (ROOT / 'index.php').read_text())

    def test_config_paths_and_missing_config_fail_closed(self):
        php = os.environ.get('PHP_BINARY', 'php')
        with tempfile.TemporaryDirectory() as folder:
            directory = Path(folder)
            shutil.copyfile(ROOT / 'config/app.php', directory / 'app.php')
            command = [php, '-r', "require 'app.php'; echo APP_BASE_PATH . '|' . ini_get('display_errors');"]
            missing = subprocess.run(command, cwd=folder, capture_output=True, text=True)
            self.assertEqual('Service temporarily unavailable.', missing.stdout)
            for environment, base, display in [('local', '/pos/', '1'), ('production', '/', '0')]:
                config = "<?php return ['environment'=>'%s','base_path'=>'%s','database'=>['host'=>'test','user'=>'test','password'=>'','name'=>'test']];" % (environment, base)
                (directory / 'config.local.php').write_text(config)
                result = subprocess.run(command, cwd=folder, capture_output=True, text=True)
                self.assertEqual(base + '|' + display, result.stdout)
            (directory / 'config.local.php').write_text(config.replace("'/'", "'https://invalid.example/'"))
            invalid = subprocess.run(command, cwd=folder, capture_output=True, text=True)
            self.assertEqual('Service temporarily unavailable.', invalid.stdout)


if __name__ == '__main__':
    unittest.main()
