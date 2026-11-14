#!/usr/bin/env python3
"""
BREW & BEAN - Code Obfuscation & Preparation Utility
Packages and obfuscates source files for the student distribution image
so students inspecting the container cannot see raw PHP, Node, or Python source.
"""

import os
import shutil
import zlib
import base64
import py_compile

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC_WEB = os.path.join(BASE_DIR, "web", "src")
SRC_BOT = os.path.join(BASE_DIR, "staff-bot", "bot.js")
SRC_VERIFIER = os.path.join(BASE_DIR, "verifier")

DIST_DIR = os.path.join(BASE_DIR, "to students", "dist")
DIST_WEB = os.path.join(DIST_DIR, "web")
DIST_BOT = os.path.join(DIST_DIR, "staff-bot")
DIST_VERIFIER = os.path.join(DIST_DIR, "verifier")

def obfuscate_php_content(content: str) -> str:
    # Compress with zlib and encode in base64
    compressed = zlib.compress(content.encode("utf-8"), level=9)[2:-4] # raw deflate
    b64 = base64.b64encode(compressed).decode("ascii")
    # Return valid PHP wrapper that executes with zero overhead
    return (
        "<?php /* BREW & BEAN ACADEMIC ASSESSMENT ENGINE - ENCRYPTED RUNTIME */ "
        f"eval('?>'.gzinflate(base64_decode('{b64}'))); ?>"
    )

def process_web():
    print("[*] Obfuscating PHP web application files...")
    if os.path.exists(DIST_WEB):
        shutil.rmtree(DIST_WEB)
    os.makedirs(DIST_WEB, exist_ok=True)

    for root, dirs, files in os.walk(SRC_WEB):
        rel_path = os.path.relpath(root, SRC_WEB)
        dest_dir = os.path.join(DIST_WEB, rel_path)
        os.makedirs(dest_dir, exist_ok=True)

        for f in files:
            src_file = os.path.join(root, f)
            dest_file = os.path.join(dest_dir, f)

            if f.endswith(".php"):
                with open(src_file, "r", encoding="utf-8") as fp:
                    content = fp.read()
                obfuscated = obfuscate_php_content(content)
                with open(dest_file, "w", encoding="utf-8") as fp:
                    fp.write(obfuscated)
            else:
                shutil.copy2(src_file, dest_file)
    print("    [+] Web files successfully obfuscated.")

def process_bot():
    print("[*] Obfuscating staff simulation bot...")
    if os.path.exists(DIST_BOT):
        shutil.rmtree(DIST_BOT)
    os.makedirs(DIST_BOT, exist_ok=True)

    with open(SRC_BOT, "r", encoding="utf-8") as fp:
        bot_code = fp.read()

    b64_bot = base64.b64encode(bot_code.encode("utf-8")).decode("ascii")
    obfuscated_bot = f"eval(Buffer.from('{b64_bot}', 'base64').toString('utf8'));\n"

    dest_bot = os.path.join(DIST_BOT, "bot.js")
    with open(dest_bot, "w", encoding="utf-8") as fp:
        fp.write(obfuscated_bot)

    # Copy package.json
    shutil.copy2(os.path.join(BASE_DIR, "staff-bot", "package.json"), os.path.join(DIST_BOT, "package.json"))
    print("    [+] Staff bot successfully obfuscated.")

def process_verifier():
    print("[*] Obfuscating verifier and compiling to bytecode...")
    if os.path.exists(DIST_VERIFIER):
        shutil.rmtree(DIST_VERIFIER)
    os.makedirs(DIST_VERIFIER, exist_ok=True)

    with open(os.path.join(SRC_VERIFIER, "app.py"), "r", encoding="utf-8") as fp:
        app_code = fp.read()

    # Mask plaintext flag literals in the Python source before compilation
    flag_map = {
        "FLAG{BREW_USER_ENUMERATION_01}": base64.b64encode(b"FLAG{BREW_USER_ENUMERATION_01}").decode("ascii"),
        "FLAG{BREW_IDOR_02}": base64.b64encode(b"FLAG{BREW_IDOR_02}").decode("ascii"),
        "FLAG{BREW_VERTICAL_ACCESS_03}": base64.b64encode(b"FLAG{BREW_VERTICAL_ACCESS_03}").decode("ascii"),
        "FLAG{BREW_COMMAND_INJECTION_04}": base64.b64encode(b"FLAG{BREW_COMMAND_INJECTION_04}").decode("ascii"),
        "FLAG{BREW_STORED_XSS_05}": base64.b64encode(b"FLAG{BREW_STORED_XSS_05}").decode("ascii"),
        "FLAG{BREW_ROOT_ESCALATION_06}": base64.b64encode(b"FLAG{BREW_ROOT_ESCALATION_06}").decode("ascii")
    }

    helper = "\nimport base64\ndef _dec(s): return base64.b64decode(s).decode('utf-8')\n"
    app_code = helper + app_code
    for plain, enc in flag_map.items():
        app_code = app_code.replace(f'"{plain}"', f'_dec("{enc}")')
        app_code = app_code.replace(f"'{plain}'", f'_dec("{enc}")')

    # Write masked app.py for container-side bytecode compilation
    dest_app = os.path.join(DIST_VERIFIER, "app.py")
    with open(dest_app, "w", encoding="utf-8") as fp:
        fp.write(app_code)

    # Copy templates and requirements
    shutil.copytree(os.path.join(SRC_VERIFIER, "templates"), os.path.join(DIST_VERIFIER, "templates"))
    shutil.copy2(os.path.join(SRC_VERIFIER, "requirements.txt"), os.path.join(DIST_VERIFIER, "requirements.txt"))
    print("    [+] Verifier masked source and assets prepared for container compilation.")

if __name__ == "__main__":
    process_web()
    process_bot()
    process_verifier()
    print("[+] All components obfuscated successfully into 'to students/dist/'.")
