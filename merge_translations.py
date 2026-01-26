import os
import re

LANGUAGES = ['en', 'lt', 'de', 'ru']
BASE_DIR = 'lang'

def parse_php_array(content):
    data = {}
    lines = content.split('\n')
    current_group = None
    pattern = re.compile(r'["\']([\w\.\-]+)["\']\s*=>\s*["\'](.*?)["\'],?')
    for line in lines:
        line = line.strip()
        if not line or line.startswith('//') or line.startswith('*') or line.startswith('/*'):
            continue
        match = pattern.search(line)
        if match:
            k, v = match.groups()
            v = v.replace("\'", "'")
            if current_group:
                data[f"{current_group}.{k}"] = v
            else:
                data[k] = v
            continue
        match_group = re.search(r'["\']([\w\.\-]+)["\']\s*=>\s*\[', line)
        if match_group:
            current_group = match_group.group(1)
            continue
        if line == '],' or line == ']':
            current_group = None
    return data

def write_php_messages(filepath, data):
    sorted_keys = sorted(data.keys())
    content = "<?php\n\nreturn [\n"
    for k in sorted_keys:
        val = data[k]
        val = val.replace("'", "\'\'")
        content += "    '{}' => '{}',\n".format(k, val)
    content += "];\n"
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

all_merged_keys = set()
for lang in LANGUAGES:
    messages_path = os.path.join(BASE_DIR, lang, 'messages.php')
    root_file_name = "{}.php".format(lang)
    root_file_path = os.path.join(BASE_DIR, lang, root_file_name)
    messages_data = {}
    if os.path.exists(messages_path):
        with open(messages_path, 'r', encoding='utf-8') as f:
            messages_data = parse_php_array(f.read())
    if os.path.exists(root_file_path):
        with open(root_file_path, 'r', encoding='utf-8') as f:
            root_data = parse_php_array(f.read())
            for k, v in root_data.items():
                if k not in messages_data:
                    messages_data[k] = v
                    all_merged_keys.add(k)
        os.remove(root_file_path)
    if lang == 'en' and 'home_page' not in messages_data:
        messages_data['home_page'] = 'Home page'
    elif 'home_page' not in messages_data:
        messages_data['home_page'] = messages_data.get('nav_home', 'Home page')
    write_php_messages(messages_path, messages_data)
    print("Processed {}".format(lang))

print("Scanning for replacements ({} keys)...".format(len(all_merged_keys)))
extensions = ['.php', '.blade.php']
files_to_scan = []
for root, dirs, files in os.walk('.'):
    if any(d in root for d in ['node_modules', 'vendor', '.git', 'storage', '.agent']):
        continue
    for file in files:
        if any(file.endswith(ext) for ext in extensions):
            files_to_scan.append(os.path.join(root, file))

code_pattern = re.compile(r'(__|@lang|trans)\s*\(\s*(["\'])(.*?)\2')
count = 0
for filepath in files_to_scan:
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
    except:
        continue
    original = content
    def repl(match):
        func = match.group(1)
        quote = match.group(2)
        text = match.group(3)
        if text in all_merged_keys:
            return "{}({}messages.{}{}".format(func, quote, text, quote)
        if text == 'Home page':
            return "{}({}messages.home_page{}".format(func, quote, quote)
        return match.group(0)
    new_content = code_pattern.sub(repl, content)
    if new_content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        count += 1
print("Replaced in {} files.".format(count))
