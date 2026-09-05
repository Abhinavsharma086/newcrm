import urllib.request
import urllib.parse
import http.cookiejar
import re

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# Get CSRF token
resp = opener.open('http://127.0.0.1:8000/login')
html = resp.read().decode()
token = re.search(r'name="_token" value="(.*?)"', html).group(1)

# Login
data = urllib.parse.urlencode({'_token': token, 'email': 'admin@admin.com', 'password': 'password'}).encode()
opener.open('http://127.0.0.1:8000/login', data)

# Fetch create page
try:
    resp = opener.open('http://127.0.0.1:8000/admin/quotations/create')
    print("SUCCESS")
    print(resp.read().decode()[:1000])
except urllib.error.HTTPError as e:
    print(f"HTTP Error {e.code}")
    error_html = e.read().decode()
    
    # Try to extract the title/exception from Ignition error page
    import json
    try:
        # Sometimes Laravel returns a JSON response on error if requested, but here it's HTML.
        # We can extract the <title> or the first <h1>
        title_match = re.search(r'<title>(.*?)</title>', error_html)
        if title_match:
            print("TITLE:", title_match.group(1))
        
        # Save error to file for analysis
        with open('error_page.html', 'w', encoding='utf-8') as f:
            f.write(error_html)
        print("Saved full error page to error_page.html")
    except:
        print("Could not parse error.")
