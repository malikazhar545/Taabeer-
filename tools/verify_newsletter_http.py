"""Authenticated local admin-route check; temporary session is removed afterwards."""
from html.parser import HTMLParser
import json
import re
import subprocess
from urllib.request import Request, urlopen
from urllib.parse import urlparse


def php(code):
    bootstrap = "<?php require '/var/www/html/wp-load.php';if(!str_contains(home_url(),'localhost')){exit(1);}"
    return subprocess.run(['docker', 'compose', 'exec', '-T', 'wordpress', 'php'],
                          input=bootstrap + code, text=True, capture_output=True, check=True).stdout


class VisibleText(HTMLParser):
    def __init__(self):
        super().__init__()
        self.hidden = 0
        self.parts = []

    def handle_starttag(self, tag, attrs):
        if tag in ('script', 'style'):
            self.hidden += 1

    def handle_endtag(self, tag):
        if tag in ('script', 'style'):
            self.hidden = max(0, self.hidden - 1)

    def handle_data(self, data):
        if not self.hidden:
            self.parts.append(data)


session = json.loads(php("$expiry=time()+180;$token=WP_Session_Tokens::get_instance(1)->create($expiry);"
    "echo wp_json_encode(['token'=>$token,'cookies'=>[AUTH_COOKIE=>wp_generate_auth_cookie(1,$expiry,'auth',$token),"
    "LOGGED_IN_COOKIE=>wp_generate_auth_cookie(1,$expiry,'logged_in',$token)]]);"))
try:
    request = Request('http://localhost:8080/wp-admin/admin.php?page=taabeer-newsletter',
                      headers={'Cookie': '; '.join(k + '=' + v for k, v in session['cookies'].items())})
    with urlopen(request, timeout=45) as response:
        html = response.read().decode('utf-8')
        assert response.status == 200 and urlparse(response.url).path == '/wp-admin/admin.php'
        assert '<h1>Newsletter subscribers</h1>' in html and '<th>Email</th>' in html
    text = VisibleText()
    text.feed(html)
    assert not re.search(r'(?:Deprecated|Fatal error|Warning):', ''.join(text.parts))
    print('PASS authenticated newsletter admin URL returns the subscriber table without visible PHP warnings')
finally:
    php('WP_Session_Tokens::get_instance(1)->destroy(' + json.dumps(session['token']) + ');')
    print('Temporary local QA session removed')
