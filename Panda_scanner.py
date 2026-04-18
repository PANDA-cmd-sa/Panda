#!/usr/bin/env python3
"""
PANDA Web Vulnerability Scanner
by Nqobile Nkosi (PANDA-cmd-sa)

Checks a target website for common security issues.
For educational and ethical use only — only scan sites you own or have permission to test.
"""

import sys
import socket
import ssl
import urllib.request
import urllib.error
import urllib.parse
import http.client
import json
from datetime import datetime

# ── colours ──────────────────────────────────────────────────────────────────
R = "\033[91m"   # red    → vulnerable / bad
Y = "\033[93m"   # yellow → warning / info
G = "\033[92m"   # green  → ok / safe
B = "\033[94m"   # blue   → section header
W = "\033[97m"   # white  → normal text
D = "\033[2m"    # dim
X = "\033[0m"    # reset

def banner():
    print(f"""
{B}
  ██████╗  █████╗ ███╗   ██╗██████╗  █████╗
  ██╔══██╗██╔══██╗████╗  ██║██╔══██╗██╔══██╗
  ██████╔╝███████║██╔██╗ ██║██║  ██║███████║
  ██╔═══╝ ██╔══██║██║╚██╗██║██║  ██║██╔══██║
  ██║     ██║  ██║██║ ╚████║██████╔╝██║  ██║
  ╚═╝     ╚═╝  ╚═╝╚═╝  ╚═══╝╚═════╝ ╚═╝  ╚═╝
{X}{D}  Web Vulnerability Scanner — by Nqobile Nkosi{X}
{D}  github.com/PANDA-cmd-sa  |  Ethical use only{X}
""")

def label(status, text):
    if status == "VULN":
        return f"  {R}[VULN]{X}  {text}"
    elif status == "WARN":
        return f"  {Y}[WARN]{X}  {text}"
    elif status == "OK":
        return f"  {G}[ OK ]{X}  {text}"
    else:
        return f"  {D}[INFO]{X}  {text}"

def section(title):
    print(f"\n{B}── {title} {'─' * (50 - len(title))}{X}")

def get_headers(url):
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "PANDA-Scanner/1.0"})
        with urllib.request.urlopen(req, timeout=8) as res:
            return dict(res.headers), res.status, res.read(4096).decode("utf-8", errors="ignore")
    except urllib.error.HTTPError as e:
        return dict(e.headers), e.code, ""
    except Exception as e:
        return {}, None, str(e)

def normalize_url(target):
    if not target.startswith("http"):
        target = "https://" + target
    return target.rstrip("/")

# ── checks ───────────────────────────────────────────────────────────────────

def check_security_headers(headers):
    section("Security Headers")
    checks = {
        "Strict-Transport-Security": (
            "HSTS missing — browser won't enforce HTTPS",
            "HSTS present"
        ),
        "X-Frame-Options": (
            "Clickjacking protection missing (X-Frame-Options)",
            "Clickjacking protection present"
        ),
        "X-Content-Type-Options": (
            "MIME sniffing protection missing",
            "MIME sniffing protection present"
        ),
        "Content-Security-Policy": (
            "No Content Security Policy — XSS risk increased",
            "Content Security Policy present"
        ),
        "Referrer-Policy": (
            "Referrer-Policy missing — may leak sensitive URLs",
            "Referrer-Policy present"
        ),
        "Permissions-Policy": (
            "Permissions-Policy missing",
            "Permissions-Policy present"
        ),
    }

    score = 0
    for header, (bad, good) in checks.items():
        # case-insensitive header lookup
        found = any(k.lower() == header.lower() for k in headers)
        if found:
            print(label("OK", good))
            score += 1
        else:
            print(label("VULN", bad))
    return score, len(checks)

def check_server_info(headers):
    section("Server Information Disclosure")
    leaky = ["Server", "X-Powered-By", "X-AspNet-Version", "X-Generator"]
    found_any = False
    for h in leaky:
        val = next((v for k, v in headers.items() if k.lower() == h.lower()), None)
        if val:
            print(label("WARN", f"{h}: {val}  ← reveals server tech stack"))
            found_any = True
    if not found_any:
        print(label("OK", "No obvious server version headers found"))

def check_https(url, host):
    section("HTTPS / SSL")
    try:
        ctx = ssl.create_default_context()
        conn = ctx.wrap_socket(socket.socket(), server_hostname=host)
        conn.settimeout(6)
        conn.connect((host, 443))
        cert = conn.getpeercert()
        conn.close()

        expire_str = cert.get("notAfter", "")
        if expire_str:
            expire = datetime.strptime(expire_str, "%b %d %H:%M:%S %Y %Z")
            days_left = (expire - datetime.utcnow()).days
            if days_left < 30:
                print(label("WARN", f"SSL cert expires in {days_left} days"))
            else:
                print(label("OK", f"SSL cert valid — expires in {days_left} days"))
        print(label("OK", "HTTPS is supported"))
    except ssl.SSLCertVerificationError:
        print(label("VULN", "SSL certificate verification failed (invalid/self-signed cert)"))
    except ConnectionRefusedError:
        print(label("WARN", "Port 443 not open — HTTPS may not be available"))
    except Exception as e:
        print(label("WARN", f"SSL check skipped: {e}"))

    # check if http redirects to https
    if url.startswith("https"):
        http_url = url.replace("https://", "http://", 1)
        try:
            req = urllib.request.Request(http_url, headers={"User-Agent": "PANDA-Scanner/1.0"})
            opener = urllib.request.build_opener(urllib.request.HTTPRedirectHandler())
            with opener.open(req, timeout=5) as res:
                final = res.url
                if final.startswith("https"):
                    print(label("OK", "HTTP redirects to HTTPS"))
                else:
                    print(label("WARN", "HTTP does not redirect to HTTPS"))
        except Exception:
            print(label("INFO", "Could not verify HTTP→HTTPS redirect"))

def check_cookies(headers):
    section("Cookie Flags")
    raw = next((v for k, v in headers.items() if k.lower() == "set-cookie"), None)
    if not raw:
        print(label("INFO", "No Set-Cookie header in this response (may appear on login pages)"))
        return
    cookies = raw.split(",")
    for cookie in cookies:
        c = cookie.strip()
        name = c.split("=")[0].strip()
        flags = c.lower()
        issues = []
        if "httponly" not in flags:
            issues.append("missing HttpOnly")
        if "secure" not in flags:
            issues.append("missing Secure flag")
        if "samesite" not in flags:
            issues.append("missing SameSite")
        if issues:
            print(label("WARN", f"Cookie '{name}': {', '.join(issues)}"))
        else:
            print(label("OK", f"Cookie '{name}': all flags set correctly"))

def check_common_paths(base_url):
    section("Exposed Sensitive Paths")
    paths = [
        "/.env",
        "/.git/config",
        "/admin",
        "/admin/login",
        "/phpinfo.php",
        "/wp-login.php",
        "/config.php",
        "/backup.zip",
        "/robots.txt",
        "/.htaccess",
        "/server-status",
        "/api/v1/users",
    ]

    for path in paths:
        url = base_url + path
        try:
            req = urllib.request.Request(url, headers={"User-Agent": "PANDA-Scanner/1.0"})
            with urllib.request.urlopen(req, timeout=5) as res:
                code = res.status
        except urllib.error.HTTPError as e:
            code = e.code
        except Exception:
            code = None

        if code == 200:
            if path in ["/robots.txt"]:
                print(label("INFO", f"{path} → {code} (normal)"))
            else:
                print(label("VULN", f"{path} → {code} ACCESSIBLE ← should be restricted"))
        elif code in [301, 302, 307, 308]:
            print(label("WARN", f"{path} → {code} redirect"))
        elif code == 403:
            print(label("OK", f"{path} → 403 Forbidden (blocked, good)"))
        elif code == 401:
            print(label("OK", f"{path} → 401 Auth required (protected)"))
        elif code is None:
            print(label("INFO", f"{path} → no response"))
        # 404s are silent — expected

def check_cors(headers, url):
    section("CORS Policy")
    acao = next((v for k, v in headers.items() if k.lower() == "access-control-allow-origin"), None)
    if acao == "*":
        print(label("WARN", "Access-Control-Allow-Origin: * — any site can read responses"))
    elif acao:
        print(label("OK", f"CORS restricted to: {acao}"))
    else:
        print(label("OK", "No open CORS policy detected"))

def check_methods(url):
    section("Allowed HTTP Methods")
    dangerous = ["PUT", "DELETE", "TRACE", "CONNECT"]
    found = []
    try:
        parsed = urllib.parse.urlparse(url)
        conn = http.client.HTTPSConnection(parsed.netloc, timeout=6) if url.startswith("https") else http.client.HTTPConnection(parsed.netloc, timeout=6)
        conn.request("OPTIONS", parsed.path or "/", headers={"User-Agent": "PANDA-Scanner/1.0"})
        res = conn.getresponse()
        allow = res.getheader("Allow", "")
        conn.close()
        for m in dangerous:
            if m in allow:
                found.append(m)
        if found:
            print(label("WARN", f"Potentially dangerous methods allowed: {', '.join(found)}"))
        else:
            print(label("OK", f"No dangerous methods exposed (Allow: {allow or 'not disclosed'})"))
    except Exception:
        print(label("INFO", "OPTIONS request not supported or blocked"))

# ── main ─────────────────────────────────────────────────────────────────────

def scan(target):
    url = normalize_url(target)
    parsed = urllib.parse.urlparse(url)
    host = parsed.netloc or parsed.path

    print(f"\n{W}Target : {B}{url}{X}")
    print(f"{W}Host   : {host}{X}")
    print(f"{D}Started: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}{X}")

    headers, status, body = get_headers(url)

    if status is None:
        print(f"\n{R}Could not reach {url}{X}")
        print(f"{D}{body}{X}")
        sys.exit(1)

    print(f"{D}Response: HTTP {status}{X}")

    check_https(url, host)
    score, total = check_security_headers(headers)
    check_server_info(headers)
    check_cookies(headers)
    check_cors(headers, url)
    check_methods(url)
    check_common_paths(url)

    # ── summary ──
    section("Summary")
    pct = int((score / total) * 100)
    bar_filled = int(pct / 5)
    bar = G + "█" * bar_filled + D + "░" * (20 - bar_filled) + X
    print(f"\n  Header score: {bar}  {W}{score}/{total}{X} ({pct}%)\n")

    if pct >= 80:
        print(f"  {G}Good security posture — a few things to tighten up.{X}")
    elif pct >= 50:
        print(f"  {Y}Moderate — several headers missing. Worth fixing before going live.{X}")
    else:
        print(f"  {R}Weak — missing most security headers. Significant room to improve.{X}")

    print(f"\n{D}Scan complete. Remember: only scan systems you own or have written permission to test.{X}\n")

if __name__ == "__main__":
    banner()
    if len(sys.argv) < 2:
        print(f"{Y}Usage: python3 panda_scanner.py <target>{X}")
        print(f"{D}Example: python3 panda_scanner.py https://example.com{X}\n")
        sys.exit(0)
    scan(sys.argv[1])
