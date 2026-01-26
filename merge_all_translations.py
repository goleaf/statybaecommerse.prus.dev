import os
import re

LANGUAGES = ['en', 'lt', 'de', 'ru']
BASE_DIR = 'lang'

def parse_php_to_dict(filepath):
    data = {}
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
    except:
        return {}

    stack = []
    lines = content.split('\n')
    # Match group: 'key' => [
    group_pattern = re.compile(r'["\']([\w\.\-]+)["\']\s*=>\s*\[')
    # Match leaf: 'key' => 'value'
    leaf_pattern = re.compile(r'["\']([\w\.\-]+)["\']\s*=>\s*["\'](.*?)["\'],?')
    
    for line in lines:
        line = line.strip()
        if not line or line.startswith('//') or line.startswith('*') or line.startswith('<?php') or line.startswith('return'):
            continue
            
        gm = group_pattern.search(line)
        if gm:
            stack.append(gm.group(1))
            continue
            
        lm = leaf_pattern.search(line)
        if lm:
            k, v = lm.groups()
            full_key = "_".join(stack + [k])
            data[full_key] = v.replace("\'", "'")
            continue
            
        if line.startswith(']') or line.startswith('),'):
            if stack:
                stack.pop()
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

replacement_map = {}

for lang in LANGUAGES:
    lang_dir = os.path.join(BASE_DIR, lang)
    if not os.path.exists(lang_dir):
        continue
    
    messages_path = os.path.join(lang_dir, 'messages.php')
    master_data = {}
    if os.path.exists(messages_path):
        master_data = parse_php_to_dict(messages_path)
    
    files_to_merge = []
    for root, dirs, files in os.walk(lang_dir):
        for file in files:
            if file.endswith('.php') and file != 'messages.php':
                files_to_merge.append(os.path.join(root, file))
    
    for fpath in files_to_merge:
        rel_path = os.path.relpath(fpath, lang_dir)
        # Convert path to prefix: sub/file.php -> sub_file
        prefix = rel_path.replace('.php', '').replace(os.sep, '_').replace('/', '_')
        # Also logical prefix for Laravel: sub.file
        logical_prefix = rel_path.replace('.php', '').replace(os.sep, '.').replace('/', '.')
        
        file_data = parse_php_to_dict(fpath)
        for k, v in file_data.items():
            new_key = "{}".format(prefix, k)
            master_data[new_key] = v
            
            # Map both flat and dotted old keys to the new messages key
            replacement_map["{}.{}".format(logical_prefix, k)] = "messages.{}".format(new_key)
            replacement_map["{}.{}".format(logical_prefix, k.replace('_', '.'))] = "messages.{}".format(new_key)
            
        os.remove(fpath)
        
    write_php_messages(messages_path, master_data)
    print("Processed {}".format(lang))

# Cleanup empty dirs
for lang in LANGUAGES:
    lang_dir = os.path.join(BASE_DIR, lang)
    if os.path.exists(lang_dir):
        for root, dirs, files in os.walk(lang_dir, topdown=False):
            for d in dirs:
                path = os.path.join(root, d)
                if not os.listdir(path):
                    os.rmdir(path)

print("Replacement map size: {}".format(len(replacement_map)))

extensions = ['.php', '.blade.php']
files_to_scan = []
for root, dirs, files in os.walk('.'):
    if any(d in root for d in ['node_modules', 'vendor', '.git', 'storage', '.agent']):
        continue
    for file in files:
        if any(file.endswith(ext) for ext in extensions):
            files_to_scan.append(os.path.join(root, file))

code_pattern = re.compile(r'(__|@lang|trans|trans_choice)\s*\(\s*(["\'])(.*?)\2')

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
        if text in replacement_map:
            return "{}({}{}".format(func, quote, replacement_map[text], quote)
        return match.group(0)
    
    new_content = code_pattern.sub(repl, content)
    if new_content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        count += 1
print("Updated {} files.".format(count))