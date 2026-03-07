#!/usr/bin/env python3
import glob

base = '/Applications/XAMPP/xamppfiles/htdocs/DASHBASE/views'

def fix_file(path):
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    original = content

    # Pattern A: $base variable (hospedaje, restaurant, etc.)
    old = 'href="<?= $base ?>/public/css/dashboard.css">'
    new = 'href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . \'/public/css/dashboard.css\') ?>">'
    content = content.replace(old, new)

    old = 'href="<?= $base ?>/public/css/components.css">'
    new = 'href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . \'/public/css/components.css\') ?>">'
    content = content.replace(old, new)

    # Pattern B: relative ../../ path
    old = 'href="../../public/css/dashboard.css">'
    new = 'href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . \'/../../public/css/dashboard.css\') ?>">'
    content = content.replace(old, new)

    old = 'href="../../public/css/components.css">'
    new = 'href="../../public/css/components.css?v=<?= filemtime(__DIR__ . \'/../../public/css/components.css\') ?>">'
    content = content.replace(old, new)

    if content != original:
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
        return True
    return False

changed = 0
for php in glob.glob(base + '/**/*.php', recursive=True):
    if fix_file(php):
        changed += 1
        print('  ok', php.replace(base + '/', ''))

print(f'\nTotal: {changed} archivos actualizados')
