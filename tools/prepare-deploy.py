"""Build a fresh, allowlisted runtime artifact. Never execute application PHP."""
from pathlib import Path
import shutil
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[1]
OUTPUT = ROOT / '.deploy'
PHP_DIRS = ('ajax', 'controllers', 'middleware', 'models', 'views', 'services', 'lib/phpmailer/src')
MEDIA_DIRS = ('assets/uploads', 'assets/img/products', 'assets/img/users', 'assets/img/branches')
ASSET_TYPES = {'.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg', '.ico',
               '.woff', '.woff2', '.ttf', '.eot', '.otf', '.mp4', '.webm', '.ogg'}


def selected_files():
    selected = [ROOT / 'assets/vendor/aos/LICENSE', ROOT / 'assets/vendor/aos/SOURCE.json', ROOT / 'lib/phpmailer/LICENSE', ROOT / 'lib/phpmailer/SOURCE.json', ROOT / 'index.php', ROOT / '.htaccess', ROOT / 'assets/.htaccess']
    selected += [ROOT / 'config' / name for name in ('db.php', 'app.php', 'uploads.php', 'auth.php', 'permissions.php')]
    for folder in PHP_DIRS:
        selected += list((ROOT / folder).rglob('*.php'))
    for file in (ROOT / 'assets').rglob('*'):
        relative = file.relative_to(ROOT).as_posix()
        if not file.is_file() or file.suffix.lower() not in ASSET_TYPES:
            continue
        if any(relative.startswith(folder + '/') for folder in MEDIA_DIRS):
            continue
        if relative.startswith('assets/img/logo_'):
            continue
        if any(part.startswith('.') or part == 'node_modules' for part in file.relative_to(ROOT).parts):
            continue
        selected.append(file)
    return sorted(set(selected))


def build(php='php'):
    # Validate before replacing an earlier artifact. Paths come from fixed project roots.
    files = selected_files()
    for file in files:
        if file.is_symlink() or ROOT not in file.resolve().parents:
            raise RuntimeError('Refusing an external or symbolic artifact path')
        if file.suffix == '.php':
            result = subprocess.run([php, '-l', str(file)], capture_output=True, text=True)
            if result.returncode:
                raise RuntimeError(result.stdout + result.stderr)
    if OUTPUT.exists():
        if OUTPUT.is_symlink() or OUTPUT.resolve() != ROOT / '.deploy':
            raise RuntimeError('Refusing to replace an unexpected deployment directory')
        shutil.rmtree(OUTPUT)
    for file in files:
        destination = OUTPUT / file.relative_to(ROOT)
        destination.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(file, destination)
    print(f'Validated and staged {len(files)} runtime files in .deploy/')


if __name__ == '__main__':
    build(sys.argv[1] if len(sys.argv) > 1 else 'php')
