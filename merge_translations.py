import os
import re

LANGUAGES = ['en', 'lt', 'de', 'ru']
BASE_DIR = 'lang'

def parse_php_array(content):
    """
    Parses a simple PHP array return ['k' => 'v', ...];
    Returns a dict.
    Handles single level and simple nested if formatted nicely.
    But better to use simple regex for 'key' => 'val'.
    """
    data = {}
    # Regex for 'key' => 'value' or "key" => "value"
    # value can be string or array [ ... ]
    # This is a naive parser.
    
    lines = content.split('\n')
    
    current_group = None
    
    for line in lines:
        line = line.strip()
        if line.startswith('//') or line.startswith('*') or line.startswith('/*'):
            continue
            
        # Match 'key' => 'value',
        match = re.search(r"['"]([\w\.\-]+)['"]\s*=>\s*['"](.*?)['"],?", line)
        if match:
            k, v = match.groups()
            v = v.replace("\'", "'") # Unescape single quotes
            if current_group:
                data[f"{current_group}.{k}"] = v
            else:
                data[k] = v
            continue
            
        # Match 'group' => [
        match_group = re.search(r"['"]([\w\.\-]+)['"]\s*=>\s*[", line)
        if match_group:
            current_group = match_group.group(1)
            continue
            
        # Match ], closing group
        if line == '],' or line == ']':
            current_group = None
            
    return data

def write_php_messages(filepath, data):
    sorted_keys = sorted(data.keys())
    content = "<?php\n\nreturn [\n"
    for k in sorted_keys:
        val = data[k]
        val = val.replace("'", "\'\'") # Escape single quotes
        content += f